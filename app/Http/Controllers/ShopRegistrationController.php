<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHandlerRam;
use App\Models\Product;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
// use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class ShopRegistrationController extends Controller
{
    protected string $image_link;

    public function __construct()
    {
        $this->image_link = AppConstants::DOMAIN.AppConstants::IMAGE_PATH;
    }

    private function successResponse($data, $message = '', $code = 200)
    {
        return ResponseHandlerRam::success(
            data: $data,
            message: $message,
            statusCode: $code
        );
    }

    private function failureResponse($message, $code = 400, $forceViewMessageDetails = false)
    {
        return ResponseHandlerRam::error(
            forceViewMessageDetails: $forceViewMessageDetails,
            message: $message,
            statusCode: $code
        );
    }

    private function validatrionErrorResponse($errors, $code = 422)
    {
        return ResponseHandlerRam::validationError(
            errors: $errors,
            message: 'Validation failed',
            statusCode: $code
        );
    }

 /**
     * Register a new shop (user + shop data + optional images)
     */
    public function registerShopAndVendor(Request $request)
    {
        try {
            // ==============================================================
            // 1. FORCE GD DRIVER (ImageTragick protection)
            // ==============================================================
            Config::set('image.driver', 'gd');
            if (! extension_loaded('gd')) {
                return $this->failureResponse(
                    message: 'Image processing is temporarily unavailable.',
                    code: Response::HTTP_SERVICE_UNAVAILABLE
                );
            }

            // ==============================================================
            // 2. VALIDATION RULES
            // ==============================================================
            $rules = [
                'shop_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:vendor_users,phone',
                'email' => 'required|email|unique:vendor_users,email',
                'shop_address' => 'required|string|max:1000',
                'password' => 'required|string|min:8|confirmed',

                // Required profile image
                'profile_image' => 'required|image|mimes:jpeg,png,webp|max:5120',
                'shop_logo' => 'required|image|mimes:jpeg,png,webp|max:5120',
                'shop_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'secondary_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'bottom_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->validatrionErrorResponse($validator->errors());
            }

            // ==============================================================
            // 3. SANITIZE ALL INPUTS
            // ==============================================================
            $sanitized = $this->sanitizeRecursive($request->all());

            // ==============================================================
            // 4. PREPARE IMAGE FIELDS & UPLOAD TRACKING
            // ==============================================================
            $imageFields = [
                'profile_image' => 'stores/profiles',
                'shop_logo' => 'stores/logo',
                'shop_banner' => 'stores/shop_banner',
                'secondary_banner' => 'stores/secondary_banner',
                'bottom_banner' => 'stores/bottom_banner',
            ];

            $uploadedPaths = []; // For rollback

            DB::beginTransaction();

            // ==============================================================
            // 5. SECURE IMAGE UPLOADS
            // ==============================================================
            foreach ($imageFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $path = $this->uploadSecureImage($file, $folder);
                    $uploadedPaths[] = $path;
                    $sanitized[$field] = $path; // Replace file object with path
                }
            }

            // ==============================================================
            // 6. CREATE VENDOR USER
            // ==============================================================
            $user = VendorUser::create([
                'first_name' => $sanitized['first_name'],
                'last_name' => $sanitized['last_name'],
                'phone' => $sanitized['phone'],
                'email' => $sanitized['email'],
                'password' => Hash::make($request->password),
                'shop_name' => $sanitized['shop_name'],
                'shop_address' => $sanitized['shop_address'],
                'status' => 'pending',
                'profile_image' => $sanitized['profile_image'] ?? null,
                'shop_logo' => $sanitized['shop_logo'] ?? null,
                'shop_banner' => $sanitized['shop_banner'] ?? null,
                'secondary_banner' => $sanitized['secondary_banner'] ?? null,
                'bottom_banner' => $sanitized['bottom_banner'] ?? null,
                'product_count' => 0,
                'orders_count' => 0,
                'wallet' => json_encode([]),
                'minimum_order_amount' => 0,
                'free_delivery_over_amount' => 0,
                'free_delivery_status' => 0,
                'sales_commission_percentage' => 0,
                'auth_token' => 0,
                'holder_name' => 0,
                'account_no' => 0,
                'bank_name' => 'not set',
                'branch' => 'not set',
                'free_delivery_features_status' => '1',
                'free_delivery_responsibility' => '1',
                'minimum_order_amount_by_seller' => '0',
            ]);

            // ==============================================================
            // 7. CONVERT IMAGE PATHS → FULL URLs
            // ==============================================================
            // $baseUrl = rtrim(AppConstants::DOMAIN.AppConstants::IMAGE_PATH, '/');

            // foreach (array_keys($imageFields) as $col) {
            //     if ($user->{$col}) {
            //         $user->{$col} = $baseUrl.'/'.ltrim($user->{$col}, '/');
            //     } else {
            //         $user->{$col} = null;
            //     }
            // }

            DB::commit();

            return $this->successResponse(
                data: $user,
                message: 'Shop registered successfully. Awaiting approval.',
                code: Response::HTTP_CREATED
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete any uploaded images
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Vendor registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['password', 'profile_image', 'shop_logo', 'shop_banner', 'secondary_banner', 'bottom_banner']),
            ]);

            return $this->failureResponse(
                message: 'Failed to register shop: '.$e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle shop update
     */
    public function updateShop(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return $this->failureResponse('Unauthorized', 401);
            }

            // ==============================================================
            // 1. FORCE GD DRIVER (ImageTragick protection)
            // ==============================================================
            Config::set('image.driver', 'gd');
            if (! extension_loaded('gd')) {
                return $this->failureResponse(
                    message: 'Image processing is temporarily unavailable.',
                    code: Response::HTTP_SERVICE_UNAVAILABLE
                );
            }

            // ==============================================================
            // 2. VALIDATION – only fields that are present
            // ==============================================================
            $rules = [
                'shop_name' => 'nullable|string|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                // 'phone' => 'nullable|string|regex:/^\+?[0-9]{10,15}$/',
                // 'email' => 'nullable|email|unique:users,email,'.$user->id,
                'shop_address' => 'nullable|string|max:1000',
                // 'current_password' => 'required_with:password|string',
                // 'password' => 'nullable|string|min:8|confirmed',

                // Images – only validate if a file is sent
                'shop_logo' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'profile_image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'secondary_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'bottom_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'offer_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
                'shop_banner' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->validatrionErrorResponse($validator->errors());
            }

            // ==============================================================
            // 3. SANITIZE ALL STRING INPUTS
            // ==============================================================
            $sanitized = $this->sanitizeRecursive($request->all());

            // ==============================================================
            // 4. PREPARE UPDATE ARRAY – only fields that are present
            // ==============================================================
            $updateData = [];
            $textFields = ['shop_name', 'first_name', 'last_name', 'shop_address'];
            foreach ($textFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $sanitized[$field] ?? $request->input($field);
                }
            }

            // ---- Password ------------------------------------------------
            if ($request->filled('password')) {
                if (! Hash::check($request->input('current_password'), $user->password)) {
                    return $this->failureResponse('Current password is incorrect.', 422);
                }
                $updateData['password'] = Hash::make($request->password);
            }

            // ==============================================================
            // 5. SECURE IMAGE UPLOAD + ROLLBACK
            // ==============================================================
            $uploadedPaths = [];                     // for rollback
            $imageFields = [
                'shop_logo' => 'stores/logo',
                'profile_image' => 'stores/profiles',
                'secondary_banner' => 'stores/secondary_banner',
                'bottom_banner' => 'stores/bottom_banner',
                'offer_banner' => 'stores/offer_banner',
                'shop_banner' => 'stores/shop_banner',
                
            ];

            DB::beginTransaction();

            foreach ($imageFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    // ---- Delete old image (if any) --------------------
                    $oldPath = $user->{$field};
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    // ---- Secure upload --------------------------------
                    $newPath = $this->uploadSecureImage($file, $folder);
                    $uploadedPaths[] = $newPath;
                    $updateData[$field] = $newPath;
                }
            }

            // ==============================================================
            // 6. UPDATE USER
            // ==============================================================
            $user->update($updateData);
            $user->refresh();

            // ==============================================================
            // 7. CONVERT STORED PATHS TO FULL URLs
            // ==============================================================
            // $baseUrl = rtrim(AppConstants::DOMAIN.AppConstants::IMAGE_PATH, '/');
            // foreach (array_keys($imageFields) as $col) {
            //     if ($user->{$col}) {
            //         $user->{$col} = $baseUrl.'/'.ltrim($user->{$col}, '/');
            //     } else {
            //         $user->{$col} = null;
            //     }
            // }

            DB::commit();

            return $this->successResponse(
                data: $user,
                message: 'Shop updated successfully',
                code: 200
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            // ---- Delete any images that were uploaded before the error
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Shop update failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(array_keys($imageFields)),
            ]);

            return $this->failureResponse(
                message: 'Failed to update shop: '.$e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /* -----------------------------------------------------------------
       RE-USED SECURE HELPERS (exact copy from addNewProduct)
       ----------------------------------------------------------------- */
    private function uploadSecureImage($file, string $folder): string
    {
        // 1. Whitelist base folders
        $allowedBases = [
            'stores/logo',
            'stores/profiles',
            'stores/secondary_banner',
            'stores/bottom_banner',
            'stores/offer_banner',
            'stores/shop_banner',
        ];

        $parts = explode('/', trim($folder, '/'));
        $base = ($parts[0] ?? '').'/'.($parts[1] ?? '');

        if (! in_array($base, $allowedBases, true)) {
            throw new \InvalidArgumentException("Invalid upload directory: {$folder}");
        }

        if (! $file || ! $file->isValid()) {
            throw new \Exception('Invalid or corrupted file upload.');
        }

        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, WebP allowed.');
        }

        $manager = new ImageManager(new Driver);
        try {
            $image = $manager->read($file->getRealPath());

            if (! $image->width() || ! $image->height()) {
                throw new \Exception('File is not a valid image.');
            }

            $image->scaleDown(width: 1920, height: 1920);
            $encoded = $image->encode(new JpegEncoder(quality: 85));
        } catch (\Throwable $e) {
            throw new \Exception('Failed to process image: '.$e->getMessage());
        }

        $filename = Str::random(40).'.jpg';
        $path = $folder.'/'.$filename;

        Storage::disk('public')->makeDirectory($folder);
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function sanitizeRecursive($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeRecursive'], $data);
        }

        return is_string($data) ? strip_tags($data) : $data;
    }

    private function getProductsCount($vendorId)
    {
        // Using Eloquent (Laravel) to count products where vendor_id matches
        return Product::where('vendor_id', $vendorId)->count();
        // return 1;
    }

    public function getVendor(Request $request)
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Check if user exists and is authenticated
            if (! $user) {
                return $this->failureResponse('Unauthenticated', 401);
            }
            return $this->successResponse(
                data: $user,
                message: 'Vendor retrieved successfully',
                code: 200
            );

        } catch (\Exception $e) {
            return $this->failureResponse(
                'Failed to retrieve user: '.$e->getMessage(),
                500
            );
        }
    }


    public function getUserLocally($userid)
    {
        try {
            // Get the authenticated user
            $user = VendorUser::find($userid);

            // Check if user exists and is authenticated
            if (! $user) {
                return ['user not found' => $userid];
            }

            $userData = [
                'id' => $user->id,
                'vacation_status' => $user->vacation_status,
                'vacation_end_date' => $user->vacation_end_date,
                'temporary_close' => $user->temporary_close,
                'rating_count' => $user->rating_count,
                'rating' => $user->rating,
                'name' => $user->first_name.' '.$user->last_name,
                'email' => $user->email,
                'contact' => $user->phone,
                'shop_name' => $user->shop_name,
                'address' => $user->shop_address,
                'status' => $user->status,

                'offer_banner' => $this->image_link.'/'.$user->offer_banner,
                'image' => $this->image_link.'/'.$user->profile_image,
                'shop_logo' => $this->image_link.'/'.$user->shop_logo,
                'banner' => $this->image_link.'/'.$user->shop_banner,
                'bottom_banner' => $this->image_link.'/'.$user->secondary_banner,

                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
            ];

            return $userData;
            // return $this->successResponse(
            //     $userData,
            //     'User details retrieved successfully'
            // );
        } catch (\Exception $e) {
            return ['error' => $userid];
        }
    }

   
  public function login(Request $request)
{
    try {
        // ==============================================================
        // 1. VALIDATION
        // ==============================================================
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:1',
        ], [
            'email.required'    => 'Email is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return $this->validatrionErrorResponse($validator->errors()->first());
        }

        // ==============================================================
        // 2. SANITIZE INPUTS (prevent XSS in logs, etc.)
        // ==============================================================
        $email    = strip_tags($request->input('email'));
        $password = $request->input('password'); // Don't log raw password

        // ==============================================================
        // 3. RATE LIMITING (6 attempts per minute per IP + email)
        // ==============================================================
        $key = 'login:' . sha1($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 6)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Login rate limit exceeded', [
                'email' => $email,
                'ip'    => $request->ip(),
                'wait'  => $seconds . ' seconds',
            ]);

            return $this->failureResponse(
                message: 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                code: 429
            );
        }

        // ==============================================================
        // 4. FIND USER (case-insensitive email)
        // ==============================================================
        $user = VendorUser::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        $authFailed = true; // Assume failure

        if ($user && $user->status === 'approved' && Hash::check($password, $user->password)) {
            $authFailed = false;
        }

        // ==============================================================
        // 5. INCREMENT RATE LIMITER ON FAILURE
        // ==============================================================
        if ($authFailed) {
            RateLimiter::hit($key, 60); // 1-minute decay

            Log::info('Failed login attempt', [
                'email'     => $email,
                'ip'        => $request->ip(),
                'user_agent'=> $request->userAgent(),
                'user_id'   => $user?->id,
            ]);

            return $this->failureResponse('Invalid credentials', 401);
        }

        // ==============================================================
        // 6. CLEAR RATE LIMITER ON SUCCESS
        // ==============================================================
        RateLimiter::clear($key);

        // ==============================================================
        // 7. GENERATE SANCTUM TOKEN
        // ==============================================================
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Vendor login successful', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        return $this->successResponse(
            data: [
                'token'   => $token,
                // 'user_id' => $user->id,
            ],
            message: 'Login successful',
            code: 200
        );

    } catch (\Throwable $e) {
        Log::error('Login exception', [
            'error'  => $e->getMessage(),
            'trace'  => $e->getTraceAsString(),
            'ip'     => $request->ip(),
        ]);

        return $this->failureResponse(
            message: 'An unexpected error occurred. Please try again later.',
            code: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}
