<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFlag;
use Illuminate\Http\Request;

class UserFlagController extends Controller
{
    public function index(int $id)
    {
        $customer = User::findOrFail($id);
        $flags = UserFlag::where('user_id', $id)->orderByDesc('id')->get();
        return response()->view('admin.user-flags', compact('customer', 'flags'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, int $id)
    {
        User::findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $reason = trim(strip_tags($data['reason']));
        if ($reason === '') return back()->withErrors(['reason' => 'Enter a flag reason.']);
        $flag = new UserFlag();
        $flag->user_id = $id;
        $flag->created_by = $request->user()->id;
        $flag->reason = $reason;
        $flag->save();
        return back()->with('success', 'User flag added.');
    }

    public function resolve(Request $request, int $id, int $flagId)
    {
        $flag = UserFlag::where('user_id', $id)->findOrFail($flagId);
        if (!$flag->resolved_at) {
            $flag->resolved_at = now();
            $flag->resolved_by = $request->user()->id;
            $flag->save();
        }
        return back()->with('success', 'User flag resolved.');
    }
}
