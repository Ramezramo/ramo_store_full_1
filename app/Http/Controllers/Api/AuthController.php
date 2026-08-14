<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHandlerRam;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    // Helper method to convert to integer
    private function tryConvertToInt($value)
    {
        try {
            return (int)$value;
        } catch (\Exception $e) {
            return $this->failureResponse('Conversion to integer failed: '.$e->getMessage(), 400);
        }
    }

    /**
     * Step 1: Send OTP to the provided phone number
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors(), 400, true);
        }
        $phone = $this->tryConvertToInt($request->phone);

        try {
            // Logic to generate and send OTP via SMS provider (e.g., Twilio, Firebase, etc.)
            // For this implementation, we'll assume the OTP is sent successfully.
            $otp = rand(100000, 999999);

            // Store OTP in cache or database with expiration (e.g., 5 minutes)
            Cache::put('otp_'.$request->phone, $otp, now()->addMinutes(5));

            return $this->successResponse(
                data: ['phone' => $request->phone, 'otp' => $otp],
                message: 'OTP sent successfully to '.$request->phone,
                code: 200
            );
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to send OTP: '.$e->getMessage(), 500);
        }
    }

    /**
     * Step 2: Register or Login with Phone and OTP
     */
    public function registerWithPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
            'name' => 'nullable|string|max:255', // Optional name during registration
        ]);
        if ($validator->fails()) {
            return $this->failureResponse($validator->errors(), 400, true);
        }
        // Verify OTP logic
        $storedOtp = Cache::get('otp_'.$request->phone);
        if (! $storedOtp || $storedOtp != $request->otp) {
            return $this->failureResponse('Invalid or expired OTP', 401);
        }
        try {
            // Check if user already exists with this phone
            $user = User::where('phone', $request->phone)->first();
            if (! $user) {
                // Create new user if not exists
                $user = new User;
                $user->phone = $request->phone;
                $displayName = $request->name ?? 'User_'.Str::random(5);
                $user->name = $displayName;
                $user->email = $request->phone.'@phone.user';
                $user->password = bcrypt($request->password);
                $user->avatar = $request->avatar;
                $user->user_login = 'phone_'.$request->phone;
                $user->url = $request->url;
                $user->user_nicename = $request->user_nicename ?? Str::slug($displayName);
                $user->display_name = $request->display_name ?? $displayName;
                $user->phone = $request->phone ?? '';
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->role = json_encode(['customer']); // Default role
                // $user->billing = $request->billing ? json_encode($request->billing) : null;
                $user->shipping = json_encode([
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'address_1' => '123 Main St',
                    'address_2' => '432 Main St',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postcode' => '10001',
                    'country' => 'USA',
                ]);
                $user->capabilities = json_encode(['customer' => true]);
                $user->description = $request->description ?? '';
                $user->lastname = $displayName;
                $user->firstname = $displayName;
                $user->registered = now();
                $user->nicename = $displayName;
                // Indicator that this is a phone-only registration
                $user->registration_method = 'phone_otp';
                $user->is_phone_verified = true;
                $user->save();
                $message = 'Registration successful';
                $code = 201;
            } else {
                $message = 'Login successful';
                $code = 200;
            }
            // Delete the OTP from cache after successful login or registration
            Cache::forget('otp_'.$request->phone);

            return $this->successResponse(
                data: [
                    'cookie' => $user->createToken('auth_token')->plainTextToken,
                    'user_id' => $user->id,
                    'user_data' => ["phone" => $user->phone, "name" => $user->name],
                    'registration_method' => $user->registration_method,
                ],
                message: $message,
                code: $code
            );
        } catch (\Exception $e) {
            return $this->failureResponse('Authentication failed: '.$e->getMessage(), 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_login' => 'string|max:255',
            'avatar' => 'nullable|string|url',
            'url' => 'nullable|string|url',
            'user_nicename' => 'nullable|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
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
            $user->shipping = $request->shipping ? json_encode($request->shipping) : null;
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
            'token' => 'required|max:700',
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
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

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

    public function generateTokenTesting(Request $request)
    {
        // -------------------------------------------------
        // 1. Validate the incoming email
        // -------------------------------------------------
        $request->validate([
            'email' => 'required|email|exists:users,email|max:255',
        ]);

        try {
            // -------------------------------------------------
            // 2. Find the user
            // -------------------------------------------------
            $user = User::where('email', $request->email)->firstOrFail();

            // -------------------------------------------------
            // 3. Generate a **plain-text** reset token
            // -------------------------------------------------
            $token = Password::createToken($user);

            // -------------------------------------------------
            // 4. Build the **front-end** reset URL
            // -------------------------------------------------
            $resetUrl = route('password.reset.form', [
                'email' => $request->email,
                'token' => $token,
            ]);

            // -------------------------------------------------
            // 5. (Optional) Log for debugging – only in local/dev
            // -------------------------------------------------
            // if (app()->environment(['local', 'testing'])) {
            //     \Log::info('Password reset token generated', [
            //         'email' => $request->email,
            //         'plain_token' => $token,
            //         'hashed_token' => DB::table('password_reset_tokens')
            //             ->where('email', $request->email)
            //             ->value('token'),
            //         'reset_url' => $resetUrl,
            //     ]);
            // }

            // -------------------------------------------------
            // 6. Return success response
            // -------------------------------------------------
            return $this->successResponse(
                data: [
                    'email' => $request->email,
                    'token' => $token,        // plain token (for testing or email)
                    'url' => $resetUrl,     // full clickable reset link
                ],
                message: 'Reset token generated successfully',
                code: 200
            );

        } catch (\Exception $e) {
            // -------------------------------------------------
            // 7. Handle any unexpected error
            // -------------------------------------------------
            // if (app()->environment(['local', 'testing'])) {
            //     \Log::error('Failed to generate reset token', [
            //         'email' => $request->email,
            //         'exception' => $e->getMessage(),
            //     ]);
            // }

            return $this->failureResponse(
                'Failed to generate reset token: '.$e->getMessage(),
                500
            );
        }
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
            'token' => 'required|string|',
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
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = bcrypt($password);
                    $user->save();
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
