<?php

namespace App\Http\Controllers\Web;

use App\Helpers\VendorApplicationConfig;
use App\Http\Controllers\Controller;
use App\Models\VendorApplication;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendorApplicationController extends Controller
{
    private const MAX_SUBMISSIONS = 2;

    private function accountDetails($user): array
    {
        $placeholderName = $user->registration_method === 'phone_otp'
            && str_starts_with((string) $user->name, 'User ') && empty($user->last_name);
        $parts = $placeholderName ? [] : preg_split('/\\s+/', trim((string) $user->name), 2, PREG_SPLIT_NO_EMPTY);
        $email = trim((string) $user->email);

        $first = $placeholderName ? '' : trim((string) ($user->first_name ?: ($parts[0] ?? '')));
        $last = $placeholderName ? '' : trim((string) ($user->last_name ?: ($parts[1] ?? '')));
        $phone = trim((string) $user->phone);

        return [
            'first_name' => mb_strlen($first) <= 100 ? $first : '',
            'last_name' => mb_strlen($last) <= 100 ? $last : '',
            'email' => filter_var($email, FILTER_VALIDATE_EMAIL) && !str_ends_with(strtolower($email), '@ramostore.local') ? $email : '',
            'phone' => preg_match('/^\\+?[0-9]{10,15}$/', $phone) ? $phone : '',
        ];
    }

    public function show(Request $request)
    {
        $application = VendorApplication::where('user_id', $request->user()->id)->first();
        $user = $request->user();
        $existing = $this->accountDetails($user);
        $personal = $application?->personal_data ?? [];
        if ($request->session()->has('vendor_application_input')) {
            $personal = array_replace($personal, Crypt::decrypt($request->session()->get('vendor_application_input')));
        }

        return response()->view('web.vendor.application', [
            'application' => $application,
            'existing' => $existing,
            'personal' => $personal,
            'sellerStatus' => $application?->vendor_id ? VendorUser::whereKey($application->vendor_id)->value('status') : null,
            'requireIdentity' => VendorApplicationConfig::requireIdentity(),
            'categories' => DB::table('categories2')->where(fn ($query) => $query->whereNull('parent')->orWhere('parent', 0))
                ->orderBy('menu_order')->orderBy('name')->get(['id', 'name']),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function savePersonal(Request $request)
    {
        $user = $request->user();
        $application = VendorApplication::where('user_id', $user->id)->first();
        if ($application && !in_array($application->status, ['draft', 'rejected'], true)) {
            return redirect()->route('vendor.register');
        }
        if ($application && $application->status === 'rejected' && $application->submission_count >= self::MAX_SUBMISSIONS) {
            return redirect()->route('vendor.register')->with('error', 'You have reached the limit of two applications.');
        }

        $submitted = $request->input('action') === 'submit';
        $accountDetails = $this->accountDetails($user);
        $personal = $application?->personal_data ?? [];
        foreach (['first_name', 'last_name', 'email', 'phone'] as $field) {
            $existing = $accountDetails[$field];
            $personal[$field] = $existing !== '' ? $existing : trim((string) $request->input($field, $personal[$field] ?? ''));
        }
        $personal['address'] = trim((string) $request->input('address', $personal['address'] ?? ''));
        $request->merge($personal);
        $required = $submitted ? 'required' : 'nullable';
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:save,submit',
            'first_name' => [$required, 'string', 'max:100'],
            'last_name' => [$required, 'string', 'max:100'],
            'email' => [$required, 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id),
                function ($attribute, $value, $fail) {
                    if (str_ends_with(strtolower((string) $value), '@ramostore.local')) $fail('Please provide a valid email address.');
                }],
            'phone' => [$required, 'string', 'max:20', 'regex:/^\\+?[0-9]{10,15}$/'],
            'address' => [$required, 'string', 'max:500'],
            'identity_document' => [
                $submitted && VendorApplicationConfig::requireIdentity() && !$application?->identity_document ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,webp', 'max:4096',
            ],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->with('vendor_application_input', Crypt::encrypt($personal));
        }
        foreach ($personal as $key => $value) $personal[$key] = strip_tags($value);

        $saved = DB::transaction(function () use ($request, $user, $personal, $submitted) {
            // Serialize concurrent submissions for the same customer.
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
            $application = VendorApplication::where('user_id', $user->id)->lockForUpdate()->first();
            if ($application && (!in_array($application->status, ['draft', 'rejected'], true)
                || ($application->status === 'rejected' && $application->submission_count >= self::MAX_SUBMISSIONS))) return false;
            if (!$application) {
                $application = new VendorApplication();
                $application->user_id = $user->id;
            }
            $application->personal_data = $personal;
            if ($request->hasFile('identity_document')) {
                $application->identity_document = base64_encode(file_get_contents($request->file('identity_document')->getRealPath()));
                $application->identity_mime = $request->file('identity_document')->getMimeType();
            }
            if ($submitted) {
                $application->submission_count++;
                $application->status = 'pending';
                $application->submitted_at = now();
                $application->reviewed_at = null;
                $application->reviewed_by = null;
                $application->rejection_reason = null;
            }
            $application->save();
            return true;
        }, 3);

        if (!$saved) return redirect()->route('vendor.register')->with('error', 'This application cannot currently be submitted.');
        return redirect()->route($submitted ? 'vendor.register' : 'account.hub')
            ->with('success', $submitted ? 'Your application has been submitted for review.' : 'Your progress has been saved.');
    }

    public function saveMarketplace(Request $request)
    {
        $application = VendorApplication::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($application->status === 'approved' && !$application->vendor_id, 403);
        $create = $request->input('action') === 'create';
        $required = $create ? 'required' : 'nullable';
        $data = $request->validate([
            'action' => 'required|in:save,create',
            'shop_name' => [$required, 'string', 'max:255'],
            'category_id' => [$required, 'integer', Rule::exists('categories2', 'id')->where(fn ($query) => $query->where(fn ($q) => $q->where('parent', 0)->orWhereNull('parent')))],
            'shop_address' => [$required, 'string', 'max:500'],
            'shop_phone' => [$required, 'string', 'max:20', 'regex:/^\\+?[0-9]{10,15}$/'],
            'shop_logo' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'shop_banner' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
        ]);
        $draft = [
            'shop_name' => strip_tags((string) ($data['shop_name'] ?? '')),
            'category_id' => $data['category_id'] ?? null,
            'shop_address' => strip_tags((string) ($data['shop_address'] ?? '')),
            'shop_phone' => $data['shop_phone'] ?? '',
        ];
        $images = [];
        foreach (['shop_logo', 'shop_banner'] as $field) {
            if ($request->hasFile($field)) {
                $images[$field] = [
                    'data' => base64_encode(file_get_contents($request->file($field)->getRealPath())),
                    'mime' => $request->file($field)->getMimeType(),
                ];
            }
        }
        $storedPaths = [];
        try {
        $vendorId = DB::transaction(function () use ($application, $draft, $create, $images, &$storedPaths) {
            $application = VendorApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            if ($application->status !== 'approved' || $application->vendor_id) return $application->vendor_id;
            $previousDraft = $application->marketplace_draft ?? [];
            $draft['images'] = array_replace($previousDraft['images'] ?? [], $images);
            $application->marketplace_draft = $draft;
            if (!$create) {
                $application->save();
                return null;
            }
            $personal = $application->personal_data;
            if (DB::table('vendor_users')->whereRaw('LOWER(email) = ?', [strtolower($personal['email'])])->exists()
                || DB::table('vendor_users')->where('phone', $draft['shop_phone'])->exists()) {
                throw ValidationException::withMessages(['shop_phone' => 'A seller already uses this email or phone. Contact support to securely link your existing seller account.']);
            }
            $vendor = new VendorUser();
            $vendor->first_name = $personal['first_name'];
            $vendor->last_name = $personal['last_name'];
            $vendor->email = strtolower($personal['email']);
            $vendor->phone = $draft['shop_phone'];
            $vendor->password = Hash::make(Str::random(64));
            $vendor->shop_name = $draft['shop_name'];
            $vendor->store_category_id = $draft['category_id'];
            $vendor->shop_address = $draft['shop_address'];
            $vendor->status = 'approved';
            $vendor->auth_token = '0';
            $vendor->holder_name = '';
            $vendor->account_no = null;
            $vendor->bank_name = 'not set';
            $vendor->branch = 'not set';
            $vendor->product_count = 0;
            $vendor->orders_count = 0;
            $vendor->sales_commission_percentage = 0;
            foreach ($draft['images'] as $field => $image) {
                $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$image['mime']] ?? null;
                if (!$extension) throw ValidationException::withMessages([$field => 'Invalid image format.']);
                $path = 'stores/'.($field === 'shop_logo' ? 'logo' : 'banner').'/'.Str::uuid().'.'.$extension;
                if (!Storage::disk('public')->put($path, base64_decode($image['data'], true))) {
                    throw new \RuntimeException('Unable to save store image.');
                }
                $storedPaths[] = $path;
                $vendor->{$field} = $path;
            }
            $vendor->save();
            $application->vendor_id = $vendor->id;
            $application->marketplace_draft = array_diff_key($draft, ['images' => true]);
            $application->save();
            return $vendor->id;
        });
        } catch (\Throwable $error) {
            foreach ($storedPaths as $path) Storage::disk('public')->delete($path);
            throw $error;
        }

        return $vendorId ? redirect()->route('vendor.dashboard')->with('success', 'Your marketplace is ready.')
            : redirect()->route('account.hub')->with('success', 'Your marketplace progress has been saved.');
    }

    public function switchToSeller(Request $request)
    {
        $application = VendorApplication::where('user_id', $request->user()->id)
            ->where('status', 'approved')->whereNotNull('vendor_id')->firstOrFail();
        $vendor = VendorUser::findOrFail($application->vendor_id);
        abort_unless($vendor->status === 'approved', 403);
        \Illuminate\Support\Facades\Auth::guard('vendor_web')->logout();
        $request->session()->forget(\App\Services\VendorWorkspace::SESSION_KEY);
        return redirect()->route('vendor.dashboard');
    }

    public function linkExisting(Request $request)
    {
        $data = $request->validate(['seller_email' => 'required|email|max:255', 'seller_password' => 'required|string|max:255']);
        $vendor = VendorUser::whereRaw('LOWER(email) = ?', [strtolower(trim($data['seller_email']))])->first();
        if (!$vendor || $vendor->status !== 'approved' || !Hash::check($data['seller_password'], $vendor->password)) {
            throw ValidationException::withMessages(['seller_email' => 'The seller credentials are invalid or the seller account is not approved.']);
        }

        DB::transaction(function () use ($request, $vendor, $data) {
            $lockedVendor = VendorUser::whereKey($vendor->id)->lockForUpdate()->firstOrFail();
            if ($lockedVendor->status !== 'approved' || !Hash::check($data['seller_password'], $lockedVendor->password)) {
                throw ValidationException::withMessages(['seller_email' => 'The seller credentials are invalid or the seller account is not approved.']);
            }
            $application = VendorApplication::where('user_id', $request->user()->id)->lockForUpdate()->first();
            if (($application && ($application->vendor_id || $application->status !== 'draft'))
                || VendorApplication::where('vendor_id', $vendor->id)->exists()) {
                throw ValidationException::withMessages(['seller_email' => 'This customer or seller account is already linked or has an application under review.']);
            }
            if (!$application) {
                $application = new VendorApplication();
                $application->user_id = $request->user()->id;
            }
            $application->vendor_id = $vendor->id;
            $application->status = 'approved';
            $application->personal_data = null;
            $application->identity_document = null;
            $application->identity_mime = null;
            $application->save();
        }, 3);
        \Illuminate\Support\Facades\Auth::guard('vendor_web')->logout();
        return redirect()->route('vendor.register')->with('success', 'Your existing seller account is linked.');
    }
}
