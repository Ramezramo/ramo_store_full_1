<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VendorApplicationConfig;
use App\Http\Controllers\Controller;
use App\Models\VendorApplication;
use App\Models\VendorApplicationReview;
use App\Models\UserFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorApplicationAdminController extends Controller
{
    public function index()
    {
        $applications = VendorApplication::where('status', '!=', 'draft')->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('submitted_at')->paginate(20);
        $requireIdentity = VendorApplicationConfig::requireIdentity();
        $reasons = DB::table('vendor_rejection_reasons')->orderBy('label')->get();
        $reviews = VendorApplicationReview::whereIn('application_id', $applications->pluck('id'))->orderByDesc('id')->get()->groupBy('application_id');
        $flags = UserFlag::whereIn('user_id', $applications->pluck('user_id'))->whereNull('resolved_at')->orderByDesc('id')->get()->groupBy('user_id');
        return response()->view('admin.vendor-applications', compact('applications', 'requireIdentity', 'reasons', 'reviews', 'flags'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function addReason(Request $request)
    {
        $data = $request->validate(['label' => 'required|string|max:200']);
        $label = trim(strip_tags($data['label']));
        if ($label === '') return back()->withErrors(['label' => 'Enter a reason.']);
        DB::table('vendor_rejection_reasons')->insert(['label' => $label, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Rejection reason added.');
    }

    public function toggleReason(int $id)
    {
        DB::transaction(function () use ($id) {
            $reason = DB::table('vendor_rejection_reasons')->where('id', $id)->lockForUpdate()->first();
            abort_if(!$reason, 404);
            DB::table('vendor_rejection_reasons')->where('id', $id)->update(['active' => !$reason->active, 'updated_at' => now()]);
        });
        return back()->with('success', 'Rejection reason updated.');
    }

    public function settings(Request $request)
    {
        $request->validate(['require_identity' => 'required|boolean']);
        DB::table('app_configs')->updateOrInsert(
            ['config_key' => 'vendor_application_settings', 'lang' => null],
            ['config_group' => 'vendor', 'value' => json_encode(['require_identity' => $request->boolean('require_identity')]),
                'label' => 'Vendor identity verification', 'description' => 'Require ID for new vendor applications.',
                'is_public' => false, 'sort_order' => 0, 'updated_at' => now()]
        );
        return back()->with('success', 'Vendor application settings saved.');
    }

    public function identity(int $id)
    {
        $application = VendorApplication::findOrFail($id);
        abort_if($application->status === 'draft', 404);
        abort_unless($application->identity_document && in_array($application->identity_mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);
        $bytes = base64_decode($application->identity_document, true);
        abort_if($bytes === false, 404);
        return response($bytes)->header('Content-Type', $application->identity_mime)
            ->header('Content-Disposition', 'inline; filename="identity-document"')
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function review(Request $request, int $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,reject,hold',
            'reason_id' => 'required_if:decision,reject|nullable|integer',
            'add_flag' => 'nullable|boolean',
        ]);
        $reviewed = DB::transaction(function () use ($id, $data, $request) {
            $application = VendorApplication::whereKey($id)->lockForUpdate()->firstOrFail();
            if (!in_array($application->status, ['pending', 'on_hold'], true)) return false;
            if ($data['decision'] === 'approve' && $application->identity_document && !$request->boolean('identity_verified')) {
                throw \Illuminate\Validation\ValidationException::withMessages(['identity_verified' => 'Review the ID and confirm the identity check before approving.']);
            }
            $reason = null;
            if ($data['decision'] === 'reject') {
                $reason = DB::table('vendor_rejection_reasons')->where('id', $data['reason_id'])->where('active', true)->lockForUpdate()->first();
                if (!$reason) throw \Illuminate\Validation\ValidationException::withMessages(['reason_id' => 'Choose an active rejection reason.']);
            }
            $application->status = match ($data['decision']) { 'approve' => 'approved', 'reject' => 'rejected', 'hold' => 'on_hold' };
            $application->rejection_reason = $reason?->label;
            $application->reviewed_at = now();
            $application->reviewed_by = $request->user()->id;
            $application->save();
            $review = new VendorApplicationReview();
            $review->application_id = $application->id;
            $review->reviewed_by = $request->user()->id;
            $review->submission_number = $application->submission_count;
            $review->decision = $application->status;
            $review->reason = $reason?->label;
            $review->save();
            if ($reason && $request->boolean('add_flag')) {
                $flag = new UserFlag();
                $flag->user_id = $application->user_id;
                $flag->created_by = $request->user()->id;
                $flag->application_id = $application->id;
                $flag->reason = $reason->label;
                $flag->save();
            }
            DB::table('vendor_application_notifications')->insert([
                'user_id' => $application->user_id, 'application_id' => $application->id,
                'status' => $application->status, 'created_at' => now(), 'updated_at' => now(),
            ]);
            return true;
        }, 3);

        return back()->with($reviewed ? 'success' : 'error', $reviewed ? 'Application reviewed.' : 'This application is no longer awaiting review.');
    }
}
