<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHandlerRam;
use App\Helpers\AuthConfig;
use App\Support\EgyptianPhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    // Helper method for success response
    private function successResponse($data, $message = '', $code = 200)
    {
        return ResponseHandlerRam::success(
            data: $data,
            message: $message,
            statusCode: $code
        );
    }

    // Helper method for failure response
    private function failureResponse($message, $code = 400, $forceViewMessageDetails = false)
    {
        return ResponseHandlerRam::error(
            forceViewMessageDetails: $forceViewMessageDetails,
            message: $message,
            statusCode: $code
        );
    }

    /** Legacy API phone authentication is intentionally unavailable. */
    public function sendOtp(Request $request)
    {
        return $this->retiredPhoneEndpoint();
    }

    public function registerWithPhone(Request $request)
    {
        return $this->retiredPhoneEndpoint();
    }

    private function retiredPhoneEndpoint()
    {
        return response()->json([
            'success' => false,
            'code' => 'legacy_phone_auth_retired',
            'message' => 'This phone sign-in endpoint has been retired. Use the website phone verification flow.',
        ], 410);
    }

    public function register(Request $request)
    {
        if (! AuthConfig::val('email_login', true)) {
            return $this->failureResponse('Email registration is disabled. Use an enabled sign-in method.', 403);
        }
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        if ($request->filled('phone')) {
            $phone = is_string($request->phone) ? EgyptianPhoneNumber::normalize($request->phone) : null;
            if (! $phone) return $this->failureResponse('Please enter a valid Egyptian mobile number.', 422);
            $request->merge(['phone' => $phone]);
        }
        if (is_string($request->input('email')) && User::query()->whereRaw('LOWER(email) = ?', [$request->email])->exists()) {
            return $this->failureResponse('That email address is already in use.', 422);
        }
        $validator = Validator::make($request->all(), [
            'user_login' => 'string|max:255',
            'avatar' => 'nullable|string|url',
            'url' => 'nullable|string|url',
            'user_nicename' => 'nullable|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'description' => 'nullable|string|max:500',
            'password' => [
                'required',
                'confirmed',
                'string',
                'max:50',
                Rules\Password::min(8),
            ],
            'password_confirmation' => 'required|string',
            // 'billing' => 'nullable|array',
            // 'billing.first_name' => 'nullable|string|max:255',
            // 'billing.last_name' => 'nullable|string|max:255',
            // 'billing.company' => 'nullable|string|max:255',
            // 'billing.address_1' => 'nullable|string|max:255',
            // 'billing.address_2' => 'nullable|string|max:255',
            // 'billing.city' => 'nullable|string|max:255',
            // 'billing.state' => 'nullable|string|max:100',
            // 'billing.postcode' => 'nullable|string|max:20',
            // 'billing.country' => 'nullable|string|max:100',

            // 'billing.email' => 'nullable|email|max:255',
            // 'billing.phone' => 'nullable|string|max:20',

            'shipping' => 'nullable|array',
            'shipping.first_name' => 'nullable|string|max:255',
            'shipping.last_name' => 'nullable|string|max:255',
            'shipping.address_1' => 'nullable|string|max:255',
            'shipping.address_2' => 'nullable|string|max:255',
            'shipping.city' => 'nullable|string|max:255',
            'shipping.state' => 'nullable|string|max:100',
            'shipping.postcode' => 'nullable|string|max:20',
            'shipping.country' => 'nullable|string|max:100',
            'shipping.latitude' => 'nullable|numeric|between:-90,90',
            'shipping.longitude' => 'nullable|numeric|between:-180,180',
        ], [], [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'shipping.latitude' => 'Shipping Latitude',
            'shipping.longitude' => 'Shipping Longitude',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors(), 400, true);
        }

        try {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->avatar = $request->avatar;
            $user->user_login = $request->user_login ?? Str::slug($request->name);
            $user->url = $request->url;
            $user->user_nicename = $request->user_nicename ?? Str::slug($request->name);
            $user->display_name = $request->display_name ?? $request->name;
            $user->phone = $request->phone ?? '';
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->role = json_encode(['customer']); // Default role
            // $user->billing = $request->billing ? json_encode($request->billing) : null;
            $user->shipping = $request->shipping ? json_encode($request->shipping) : '[]';
            $user->capabilities = json_encode(['customer' => true]);
            $user->description = $request->description ?? '';
            $user->lastname = $request->last_name;
            $user->firstname = $request->first_name;
            $user->registered = now();
            $user->nicename = trim($request->first_name.' '.$request->last_name);
            $user->registration_method = 'reguler_email_password';
            $user->is_phone_verified = false;
            $user->save();

            return $this->successResponse(
                data: [
                    'cookie' => $user->createToken('auth_token')->plainTextToken,
                    'user_data' =>["phone"  => $user->phone] ,
                    'user_id' => $user->id,
                ],
                message: 'Registration successful',
                code: 201
            );
        } catch (\Exception $e) {
            return $this->failureResponse('Registration failed: '.$e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        if (! AuthConfig::val('email_login', true)) {
            return $this->failureResponse('Email sign-in is disabled. Use an enabled sign-in method.', 403);
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ], [], [
            'email' => 'Email',
            'password' => 'Password',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first(), 400);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            if ($user->is_blocked) {
                Auth::logout();
                return $this->failureResponse('This account is unavailable. Please contact support.', 403);
            }

            return $this->successResponse(
                data: [
                    'cookie' => $user->createToken('auth_token')->plainTextToken,
                    'user_data' => ["phone" => $user->phone],
                    'user_id' => $user->id,
                ],
                message: 'Login successful',
                code: 200
            );
        }

        return $this->failureResponse('Invalid email or password', 401);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return $this->failureResponse('User not authenticated', 401);
            }

            $user->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logout successful', 200);
        } catch (\Exception $e) {
            return $this->failureResponse('Logout failed: '.$e->getMessage(), 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return $this->failureResponse('Invalid token', 401, true);
            }

            $user->role = json_decode($user->role, true);
            $user->billing = json_decode($user->billing, true);
            $user->shipping = $user->shipping ? json_decode($user->shipping, true) : null;
            $user->capabilities = json_decode($user->capabilities, true);

            return $this->successResponse(
                data: [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->user_login,
                        'nicename' => $user->user_nicename,
                        'email' => $user->email,
                        'url' => $user->url,
                        'registered' => $user->registered,
                        'displayname' => $user->display_name,
                        'firstname' => $user->first_name,
                        'lastname' => $user->last_name,
                        'nickname' => $user->user_login,
                        'description' => $user->description,
                        'capabilities' => $user->capabilities,
                        'role' => $user->role,
                        'shipping' => $user->shipping,
                        'billing' => $user->billing,
                        'avatar' => $user->avatar,
                        'is_driver_available' => false,
                        'dokan_enable_selling' => false,
                    ],
                ],
                message: 'User retrieved successfully',
                code: 200
            );
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to retrieve user: '.$e->getMessage(), 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return $this->failureResponse('User not authenticated', 401);
            }

            $user->tokens()->delete();

            return $this->successResponse(
                data: [
                    'cookie' => $user->createToken('auth_token')->plainTextToken,
                    'user_id' => $user->id,
                ],
                message: 'Token refreshed successfully',
                code: 200
            );
        } catch (\Exception $e) {
            return $this->failureResponse('Token refresh failed: '.$e->getMessage(), 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return $this->failureResponse('User not authenticated', 401);
            }

            $user->delete();

            return $this->successResponse(null, 'Account deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->failureResponse('Account deletion failed: '.$e->getMessage(), 500);
        }
    }

    public function showResetPasswordFormHTML(Request $request)
    {
        // return response()->json([
        //     'token' => $request->query('token'),
        //     'email' => $request->query('email'),
        // ], 200);
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $request->query('token'),
        ]);
    }

    public function recievingNewPassMod(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:700',
            'email' => 'required|email|max:500',
            'password' => 'required|string|min:8|confirmed|max:50',
            'password_confirmation' => 'required|max:50',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address.',
            'email.max' => 'Email must not exceed 500 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 50 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.max' => 'Password confirmation must not exceed 50 characters.',
            'token.required' => 'Token is required.',
            'token.max' => 'Token must not exceed 700 characters.',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first(), 400);
            // return response()->json([
            //     'errors' => $validator->errors(),
            // ], 422);
        }

        // Attempt to reset the password
        $status = $this->resetWithLockedProof($request,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => now(),
                    'registration_method' => 'email_password',
                ])->save();
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        // Check the result of the password reset attempt
        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'تم إعادة تعيين كلمة المرور بنجاح.', 200);
            // return response()->json([
            //     'message' => 'تم إعادة تعيين كلمة المرور بنجاح.',
            // ], 200);
        }

        return $this->failureResponse(__($status), 422);

        // return response()->json([
        //     'errors' => ['email' => [__($status)]],
        // ], 422);
    }

    private function resetWithLockedProof(Request $request, callable $callback): string
    {
        return DB::transaction(function () use ($request, $callback): string {
            // Match the website recovery lock order before the broker reads proof.
            User::where('email', $request->email)->lockForUpdate()->first();
            DB::table('password_reset_tokens')->where('email', $request->email)->lockForUpdate()->first();
            return Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'), $callback
            );
        });
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email|max:255',
        ], [], [
            'email' => 'Email',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first(), 400);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                ? $this->successResponse(null, 'Password reset link sent successfully', 200)
                : $this->failureResponse('Unable to send reset link', 500);
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to send reset link: '.$e->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:700',
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Rules\Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => 'required|string',
        ], [], [
            'token' => 'Reset Token',
            'email' => 'Email',
            'password' => 'Password',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first(), 400);
        }

        try {
            $status = $this->resetWithLockedProof($request,
                    function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password), 'remember_token' => Str::random(60),
                        'email_verified_at' => now(), 'registration_method' => 'email_password',
                    ])->save();
                    $user->tokens()->delete(); // Invalidate all tokens after password reset
                }
            );

            return $status === Password::PASSWORD_RESET
                ? $this->successResponse(null, 'Password reset successfully', 200)
                : $this->failureResponse('Invalid token or email', 400);
        } catch (\Exception $e) {
            return $this->failureResponse('Password reset failed: '.$e->getMessage(), 500);
        }
    }

    // public function updateProfile(Request $request)
    // {
    //     $user = $request->user();
    //     if (! $user) {
    //         return $this->failureResponse('User not authenticated', 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'string|max:255',
    //         'email' => 'email|unique:users,email,'.$user->id,
    //         'phone' => 'nullable|string|max:20',
    //         'avatar' => 'nullable|string|url',
    //         'url' => 'nullable|string|url',
    //         'user_nicename' => 'nullable|string|max:255',
    //         'display_name' => 'nullable|string|max:255',
    //         'first_name' => 'string|max:255',
    //         'last_name' => 'string|max:255',
    //         'description' => 'nullable|string|max:1000',
    //         'billing_first_name' => 'nullable|string|max:255',
    //         'billing_last_name' => 'nullable|string|max:255',
    //         'billing_company' => 'nullable|string|max:255',
    //         'billing_address_1' => 'nullable|string|max:255',
    //         'billing_address_2' => 'nullable|string|max:255',
    //         'billing_city' => 'nullable|string|max:255',
    //         'billing_state' => 'nullable|string|max:100',
    //         'billing_postcode' => 'nullable|string|max:20',
    //         'billing_country' => 'nullable|string|max:100',
    //         'billing_email' => 'nullable|email|max:255',
    //         'billing_phone' => 'nullable|string|max:20',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->failureResponse($validator->errors()->first(), 400);
    //     }

    //     try {
    //         $user->fill($request->only([
    //             'name', 'email', 'phone', 'avatar', 'url', 'user_nicename',
    //             'display_name', 'first_name', 'last_name', 'description',
    //         ]));

    //         $user->lastname = $request->last_name ?? $user->lastname;
    //         $user->firstname = $request->first_name ?? $user->firstname;
    //         $user->nicename = trim(($request->first_name ?? $user->first_name).' '.($request->last_name ?? $user->last_name));

    //         $billing = json_decode($user->billing, true) ?? [];
    //         $billing = array_merge($billing, array_filter($request->only([
    //             'billing_first_name', 'billing_last_name', 'billing_company',
    //             'billing_address_1', 'billing_address_2', 'billing_city',
    //             'billing_state', 'billing_postcode', 'billing_country',
    //             'billing_email', 'billing_phone',
    //         ])));
    //         $user->billing = json_encode($billing);

    //         if ($request->has('shipping')) {
    //             $user->shipping = json_encode($request->shipping);
    //         }

    //         $user->save();

    //         return $this->successResponse(
    //             data: [
    //                 'user' => [
    //                     'id' => $user->id,
    //                     'username' => $user->user_login,
    //                     'nicename' => $user->user_nicename,
    //                     'email' => $user->email,
    //                     'url' => $user->url,
    //                     'registered' => $user->registered,
    //                     'displayname' => $user->display_name,
    //                     'firstname' => $user->first_name,
    //                     'lastname' => $user->last_name,
    //                     'nickname' => $user->user_login,
    //                     'description' => $user->description,
    //                     'capabilities' => json_decode($user->capabilities, true),
    //                     'role' => json_decode($user->role, true),
    //                     'shipping' => $user->shipping ? json_decode($user->shipping, true) : null,
    //                     'billing' => json_decode($user->billing, true),
    //                     'avatar' => $user->avatar,
    //                     'is_driver_available' => false,
    //                     'dokan_enable_selling' => false,
    //                 ],
    //             ],
    //             message: 'Profile updated successfully',
    //             code: 200
    //         );
    //     } catch (\Exception $e) {
    //         return $this->failureResponse('Profile update failed: '.$e->getMessage(), 500);
    //     }
    // }

    // public function updatePreferences(Request $request)
    // {
    //     $user = $request->user();
    //     if (! $user) {
    //         return $this->failureResponse('User not authenticated', 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'notifications_enabled' => 'boolean',
    //         'theme' => 'nullable|string|in:light,dark',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->failureResponse($validator->errors()->first(), 400);
    //     }

    //     try {
    //         $preferences = json_decode($user->preferences ?? '{}', true);
    //         $preferences = array_merge($preferences, array_filter($request->only([
    //             'notifications_enabled', 'theme',
    //         ])));
    //         $user->preferences = json_encode($preferences);
    //         $user->save();

    //         return $this->successResponse(
    //             data: ['preferences' => $preferences],
    //             message: 'Preferences updated successfully',
    //             code: 200
    //         );
    //     } catch (\Exception $e) {
    //         return $this->failureResponse('Preferences update failed: '.$e->getMessage(), 500);
    //     }
    // }

    // public function getUsers(Request $request)
    // {
    //     $user = $request->user();
    //     if (! $user || ! in_array('admin', json_decode($user->role, true))) {
    //         return $this->failureResponse('Unauthorized access', 403);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'page' => 'integer|min:1',
    //         'limit' => 'integer|min:1|max:100',
    //         'sort' => 'string|in:email,name,registered',
    //         'order' => 'string|in:asc,desc',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->failureResponse($validator->errors()->first(), 400);
    //     }

    //     try {
    //         $page = $request->query('page', 1);
    //         $limit = $request->query('limit', 10);
    //         $sort = $request->query('sort', 'registered');
    //         $order = $request->query('order', 'desc');

    //         $users = User::select([
    //             'id', 'user_login', 'user_nicename', 'email', 'url', 'registered',
    //             'display_name', 'first_name', 'last_name', 'role',
    //         ])
    //             ->orderBy($sort, $order)
    //             ->paginate($limit, ['*'], 'page', $page);

    //         $users->getCollection()->transform(function ($user) {
    //             $user->role = json_decode($user->role, true);

    //             return [
    //                 'id' => $user->id,
    //                 'username' => $user->user_login,
    //                 'nicename' => $user->user_nicename,
    //                 'email' => $user->email,
    //                 'url' => $user->url,
    //                 'registered' => $user->registered,
    //                 'displayname' => $user->display_name,
    //                 'firstname' => $user->first_name,
    //                 'lastname' => $user->last_name,
    //                 'role' => $user->role,
    //             ];
    //         });

    //         return $this->successResponse(
    //             data: [
    //                 'users' => $users->items(),
    //                 'pagination' => [
    //                     'total' => $users->total(),
    //                     'per_page' => $users->perPage(),
    //                     'current_page' => $users->currentPage(),
    //                     'last_page' => $users->lastPage(),
    //                 ],
    //             ],
    //             message: 'Users retrieved successfully',
    //             code: 200
    //         );
    //     } catch (\Exception $e) {
    //         return $this->failureResponse('Failed to retrieve users: '.$e->getMessage(), 500);
    //     }
    // }

    // public function updateUserRole(Request $request, $userId)
    // {
    //     $currentUser = $request->user();
    //     if (! $currentUser || ! in_array('admin', json_decode($currentUser->role, true))) {
    //         return $this->failureResponse('Unauthorized access', 403);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'role' => 'required|array|min:1|max:15',
    //         'role.*' => 'string|in:customer,admin',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->failureResponse($validator->errors()->first(), 400);
    //     }

    //     try {
    //         $targetUser = User::findOrFail($userId);
    //         $targetUser->role = json_encode($request->role);
    //         $targetUser->capabilities = json_encode(array_fill_keys($request->role, true));
    //         $targetUser->save();

    //         return $this->successResponse(
    //             data: [
    //                 'user' => [
    //                     'id' => $targetUser->id,
    //                     'email' => $targetUser->email,
    //                     'role' => json_decode($targetUser->role, true),
    //                 ],
    //             ],
    //             message: 'User role updated successfully',
    //             code: 200
    //         );
    //     } catch (\Exception $e) {
    //         return $this->failureResponse('Role update failed: '.$e->getMessage(), 500);
    //     }
    // }
}
