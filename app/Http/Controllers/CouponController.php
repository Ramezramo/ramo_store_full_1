<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandlerRam;
use App\Models\Coupon;
use App\Services\PricingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
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

    public function getByCode(string $code): JsonResponse
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $coupon,
            'message' => 'Coupon retrieved successfully',
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'code' => ['required', 'string', 'max:50', 'unique:coupons'],
                'amount' => ['required', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(['publish', 'draft', 'private'])],
                'discount_type' => ['required', Rule::in(['percent', 'fixed_cart', 'fixed_product'])],
                'date_expires' => ['nullable', 'date', 'after:now'],
                'usage_limit' => ['nullable', 'integer', 'min:0'],
                'usage_limit_per_user' => ['nullable', 'integer', 'min:0'],
                'limit_usage_to_x_items' => ['nullable', 'integer', 'min:0'],
                'minimum_amount' => ['nullable', 'numeric', 'min:0'],
                'maximum_amount' => ['nullable', 'numeric', 'min:0'],
                'product_ids' => ['nullable', 'array'],
                'excluded_product_ids' => ['nullable', 'array'],
                'product_categories' => ['nullable', 'array'],
                'excluded_product_categories' => ['nullable', 'array'],
                'email_restrictions' => ['nullable', 'array'],
                'description' => ['nullable', 'string'],
                'meta_data' => ['nullable', 'array'],
                'individual_use' => ['boolean'],
                'free_shipping' => ['boolean'],
                'exclude_sale_items' => ['boolean'],
            ]);

            $coupon = Coupon::create(array_merge($validatedData, [
                'vendor_id' => $request->user()->id,
                'date_created' => now(),
                'date_created_gmt' => now('UTC'),
                'date_modified' => now(),
                'date_modified_gmt' => now('UTC'),
                'usage_count' => 0,
                'used_by' => [],
            ]));

            return $this->successResponse(
                data: $coupon,
                message: 'Coupon created successfully',
                code: 201
            );
        } catch (ValidationException $e) {
            return $this->failureResponse(
                message: 'Validation failed: '.$e->getMessage(),
                code: 422,
                forceViewMessageDetails: true
            );
        } catch (\Exception $e) {
            // \Log::error('Unexpected error in coupon creation', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString()
            // ]);

            return $this->failureResponse(
                message: 'An unexpected error occurred while creating the coupon',
                code: 500
            );
        }
    }

    public function show(string $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $this->authorizeOwnedCoupon('view', $coupon);

        return $this->successResponse(
            data: $coupon,
            message: 'Coupon retrieved successfully'
        );
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // Validate & sanitize inputs
            $validated = $request->validate([
                'per_page' => 'integer|min:1|max:100',
                'page' => 'integer|min:1',
                'status' => ['nullable', Rule::in(['publish', 'draft', 'private'])],
                'search' => 'nullable|string|max:100',
                'sort_by' => ['nullable', 'string', Rule::in((new Coupon)->getFillable())],
                'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            ]);

            $perPage = $validated['per_page'] ?? 15;
            $page = $validated['page'] ?? 1;
            $status = $validated['status'] ?? null;
            $search = $validated['search'] ?? null;
            $sortBy = $validated['sort_by'] ?? 'id';
            $sortDir = $validated['sort_dir'] ?? 'desc';

            // Vendor CRUD must never expose global or another vendor's coupons.
            $query = Coupon::query()->where('vendor_id', $request->user()->id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $query->orderBy($sortBy, $sortDir);

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            $meta = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ];

            return $this->successResponse(
                data: [
                    'coupons' => $paginator->items(),
                    'pagination' => $meta,
                ],
                message: 'Coupons retrieved successfully'
            );

        } catch (ValidationException $e) {
            return $this->failureResponse(
                message: 'Invalid coupon query parameters.',
                code: 422
            );
        } catch (\Exception $e) {
            return $this->failureResponse(
                message: 'Failed to retrieve coupons',
                code: 500
            );
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            // 1. VALIDATE ID FORMAT
            if (! is_numeric($id) || $id <= 0) {
                return $this->failureResponse(
                    message: 'Invalid coupon ID format',
                    code: 400
                );
            }

            // 2. FIND COUPON - HANDLE NOT FOUND
            $coupon = Coupon::find($id);
            if (! $coupon) {
                return $this->failureResponse(
                    message: 'Coupon not found',
                    code: 404
                );
            }

            $this->authorizeOwnedCoupon('update', $coupon);

            // 3. VALIDATE REQUEST DATA
            try {
                $validatedData = $request->validate([
                    'code' => ['sometimes', 'string', 'max:50', Rule::unique('coupons')->ignore($id)],
                    'amount' => ['sometimes', 'numeric', 'min:0'],
                    'status' => ['sometimes', Rule::in(['publish', 'draft', 'private'])],
                    'discount_type' => ['sometimes', Rule::in(['percent', 'fixed_cart', 'fixed_product'])],
                    'date_expires' => ['nullable', 'date'],
                    'usage_limit' => ['nullable', 'integer', 'min:0'],
                    'usage_limit_per_user' => ['nullable', 'integer', 'min:0'],
                    'limit_usage_to_x_items' => ['nullable', 'integer', 'min:0'],
                    'minimum_amount' => ['nullable', 'numeric', 'min:0'],
                    'maximum_amount' => ['nullable', 'numeric', 'min:0'],
                ]);
            } catch (ValidationException $e) {
                return $this->failureResponse(
                    message: 'Validation failed'.$e->getMessage(),
                    code: 422,
                    forceViewMessageDetails: true
                );
            }

            // 4. BUSINESS LOGIC VALIDATIONS
            $validationErrors = [];

            // Check if amount is required for certain discount types
            if (isset($validatedData['discount_type']) && isset($validatedData['amount'])) {
                if ($validatedData['discount_type'] === 'percent' && ($validatedData['amount'] < 0 || $validatedData['amount'] > 100)) {
                    $validationErrors['amount'] = ['Percentage discount must be between 0 and 100'];
                }
            }

            // Check date_expires must be in future
            if (isset($validatedData['date_expires']) && $validatedData['date_expires'] &&
                strtotime($validatedData['date_expires']) <= now()->timestamp) {
                $validationErrors['date_expires'] = ['Expiry date must be in the future'];
            }

            // Check minimum_amount <= maximum_amount
            if (isset($validatedData['minimum_amount']) && isset($validatedData['maximum_amount'])) {
                if ($validatedData['minimum_amount'] > $validatedData['maximum_amount']) {
                    $validationErrors['minimum_amount'] = ['Minimum amount cannot exceed maximum amount'];
                }
            }

            if (! empty($validationErrors)) {
                return $this->failureResponse(
                    message: 'Business validation failed',
                    code: 422,
                    forceViewMessageDetails: true
                );
            }

            // 5. UPDATE COUPON
            try {
                $updateData = array_merge($validatedData, [
                    'date_modified' => now(),
                    'date_modified_gmt' => now('UTC'),
                ]);

                $updated = $coupon->update($updateData);

                if (! $updated) {
                    return $this->failureResponse(
                        message: 'Failed to update coupon',
                        code: 500
                    );
                }

                // 6. REFRESH AND RETURN
                $coupon->refresh();

                return $this->successResponse(
                    data: $coupon,
                    message: 'Coupon updated successfully'
                );

            } catch (\Exception $e) {
                // Log the database error
                // \Log::error('Coupon update failed', [
                //     'coupon_id' => $id,
                //     'error' => $e->getMessage(),
                //     'trace' => $e->getTraceAsString()
                // ]);

                return $this->failureResponse(
                    message: 'Database update failed',
                    code: 500
                );
            }

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Preserve deliberate 404 responses used for non-owned coupons.
            throw $e;
        } catch (\Exception $e) {
            // 7. CATCH ALL UNEXPECTED ERRORS
            // \Log::error('Unexpected error in coupon update', [
            //     'coupon_id' => $id ?? 'unknown',
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString()
            // ]);

            return $this->failureResponse(
                message: 'An unexpected error occurred',
                code: 500
            );
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $this->authorizeOwnedCoupon('delete', $coupon);
        $coupon->delete();

        return $this->successResponse(
            data: null,
            message: 'Coupon deleted successfully'
        );
    }

    private function authorizeOwnedCoupon(string $ability, Coupon $coupon): void
    {
        try {
            $this->authorize($ability, $coupon);
        } catch (AuthorizationException) {
            // Do not disclose whether an ID belongs to another vendor or is global.
            abort(404);
        }
    }

    /**
     * Calculate an authenticated customer's cart subtotal from current catalog rows.
     * Request-provided totals are intentionally ignored for coupon decisions.
     */
    private function calculateAuthenticatedCartTotal(int $userId): float
    {
        $cartItems = DB::table('cart_items')->where('user_id', $userId)->get();
        if ($cartItems->isEmpty()) {
            throw new \InvalidArgumentException('Your cart is empty.', 422);
        }

        $productIds = $cartItems->pluck('product_id')->unique()->values()->all();
        $products = DB::table('products_data')
            ->whereIn('id', $productIds)
            ->get(['id', 'discount_percentage'])
            ->keyBy('id');
        $variations = DB::table('product_variations')
            ->whereIn('product_id', $productIds)
            ->get(['id', 'product_id', 'main_variation', 'price', 'regular_price', 'status', 'stock_status'])
            ->keyBy('id');

        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $product = $products->get($item->product_id);
            $variation = $item->variation_id
                ? $variations->get($item->variation_id)
                : $variations->first(fn ($row) => (int) $row->product_id === (int) $item->product_id && (bool) $row->main_variation);
            $quantity = (int) $item->qty;

            if (! $product || ! $variation || (int) $variation->product_id !== (int) $item->product_id) {
                throw new \InvalidArgumentException('Your cart contains an unavailable product variation.', 422);
            }
            if ($quantity < 1) {
                throw new \InvalidArgumentException('Your cart contains an invalid quantity.', 422);
            }
            if (($variation->status ?? 'publish') !== 'publish' || ($variation->stock_status ?? 'instock') !== 'instock') {
                throw new \InvalidArgumentException('Your cart contains an unavailable product variation.', 422);
            }

            $subtotal += PricingService::effectiveVariationPrice($variation, $product) * $quantity;
        }

        return round($subtotal, 2);
    }

    /**
     * Validate coupon and return validation result.
     */
    public function validateCouponRules(
        string $code,
        float $cart_total,
        ?int $user_id = null
    ): array {
        // Find coupon
        $coupon = Coupon::where('code', $code)->active()->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid or expired coupon code',
                'code' => 404,
                'data' => null,
            ];
        }

        // ✅ CHECK MINIMUM AMOUNT
        if ($cart_total < $coupon->minimum_amount) {
            return [
                'valid' => false,
                'message' => "Cart total must be at least {$coupon->minimum_amount}",
                'code' => 422,
                'data' => null,
            ];
        }

        // ✅ CHECK MAXIMUM AMOUNT
        if ($coupon->maximum_amount > 0 && $cart_total > $coupon->maximum_amount) {
            return [
                'valid' => false,
                'message' => "Cart total cannot exceed {$coupon->maximum_amount}",
                'code' => 422,
                'data' => null,
            ];
        }

        // Check the global usage cap before showing the coupon as usable.
        if ((int) ($coupon->usage_limit ?? 0) > 0 && (int) ($coupon->usage_count ?? 0) >= (int) $coupon->usage_limit) {
            return [
                'valid' => false,
                'message' => 'Coupon usage limit reached',
                'code' => 422,
                'data' => null,
            ];
        }

        // The live checkout source of truth is coupon_user_limits, regardless of
        // the legacy individual_use/used_by fields on the coupon model.
        if ($user_id && (int) ($coupon->usage_limit_per_user ?? 0) > 0) {
            $userUses = (int) (DB::table('coupon_user_limits')
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $user_id)
                ->value('use_count') ?? 0);
            if ($userUses >= (int) $coupon->usage_limit_per_user) {
                return [
                    'valid' => false,
                    'message' => 'Coupon usage limit reached for this user',
                    'code' => 422,
                    'data' => null,
                ];
            }
        }

        // ✅ VALID - Return the same rounded discount used by order creation.
        $discount = $this->getCouponDiscountAmount($coupon, $cart_total);

        return [
            'valid' => true,
            'message' => 'Coupon is valid',
            'code' => 200,
            'data' => [
                'coupon' => $coupon,
                'discount_amount' => $discount,
                'discount_type' => $coupon->discount_type,
                'new_total' => max(0, $cart_total - $discount),
            ],
        ];
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|max:50',
            ]);

            $cartTotal = $this->calculateAuthenticatedCartTotal((int) $request->user()->id);
            $validation = $this->validateCouponRules(
                $request->code,
                $cartTotal,
                $request->user()->id
            );

            if (! $validation['valid']) {
                return $this->failureResponse(
                    message: $validation['message'],
                    code: $validation['code']
                );
            }

            return $this->successResponse(
                data: $validation['data'],
                message: $validation['message']
            );
        } catch (ValidationException $e) {
            return $this->failureResponse(
                message: 'Validation failed: '.$e->getMessage(),
                code: 422,
                forceViewMessageDetails: true
            );
        } catch (\Exception $e) {
            return $this->failureResponse(
                message: 'An unexpected error occurred',
                code: 500
            );
        }
    }

    public function applyCouponLocally(string $code, float $cartTotal, ?int $userId = null, bool $consumeUsage = false): array
    {
        // 1. Basic input validation
        $code = trim($code);
        if ($code === '' || mb_strlen($code) > 50) {
            return $this->failureResponselocal('Coupon code is required and must be ≤ 50 characters', 422);
        }

        if ($cartTotal < 0) {
            return $this->failureResponselocal('Cart total cannot be negative', 422);
        }

        // 2. Core coupon rules validation
        $validation = $this->validateCouponRules($code, $cartTotal, $userId);

        if (! $validation['valid']) {
            return $this->failureResponselocal($validation['message'], $validation['code']);
        }

        /** @var \App\Models\Coupon $coupon */
        $coupon = $validation['data']['coupon'];

        // Usage is consumed only by the successful order-creation transaction.
        if ($consumeUsage && ! $this->incrementCouponUsage($coupon, $userId)) {
            return $this->failureResponselocal('Could not apply coupon – usage limit reached or DB error', 400);
        }

        // Calculate the preview or order discount from the server-computed subtotal.
        $discount = $this->getCouponDiscountAmount($coupon, $cartTotal);

        if ($consumeUsage) {
            $coupon = $coupon->refresh();
        }

        return $this->successResponselocal([
            'coupon' => $this->couponToArray($coupon),
            'discount_amount' => $discount,
            'discount_type' => $coupon->discount_type ?? 'fixed',
            'new_total' => max(0, $cartTotal - $discount),
        ], 'Coupon applied successfully');
    }

    protected function getCouponDiscountAmount(\App\Models\Coupon $coupon, float $cartTotal): float
    {
        $amount = (float) $coupon->amount;

        return match ($coupon->discount_type) {
            'percent', 'percentage' => round($cartTotal * $amount / 100, 2),
            'fixed_cart', 'fixed' => $amount > $cartTotal ? $cartTotal : $amount,
            default => 0.0,
        };
    }

    protected function couponToArray(\App\Models\Coupon $coupon): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'amount' => (string) $coupon->amount,
            'discount_type' => $coupon->discount_type ?? 'fixed',
            'description' => $coupon->description,
            'date_expires' => $coupon->date_expires?->format('Y-m-d H:i:s'),
            'usage_limit' => (int) $coupon->usage_limit,
            'usage_count' => (int) $coupon->usage_count,
            'usage_limit_per_user' => (int) ($coupon->usage_limit_per_user ?? 0),
            'minimum_amount' => (string) ($coupon->minimum_amount ?? '0'),
            'maximum_amount' => (string) ($coupon->maximum_amount ?? '0'),
            'individual_use' => (bool) $coupon->individual_use,
            'free_shipping' => (bool) $coupon->free_shipping,
        ];
    }

    protected function incrementCouponUsage(\App\Models\Coupon $coupon, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($coupon, $userId) {
            $freshCoupon = \App\Models\Coupon::where('id', $coupon->id)
                ->lockForUpdate()
                ->first();

            if ($freshCoupon->usage_limit > 0 && $freshCoupon->usage_count >= $freshCoupon->usage_limit) {
                return false;
            }

            if ($userId && $freshCoupon->usage_limit_per_user > 0) {
                $userUses = DB::table('coupon_user_limits')
                    ->where('coupon_id', $freshCoupon->id)
                    ->where('user_id', $userId)
                    ->value('use_count') ?? 0;

                if ($userUses >= $freshCoupon->usage_limit_per_user) {
                    return false;
                }

                $usageRow = DB::table('coupon_user_limits')
                    ->where('coupon_id', $freshCoupon->id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($usageRow) {
                    DB::table('coupon_user_limits')
                        ->where('id', $usageRow->id)
                        ->update([
                            'use_count' => ((int) $usageRow->use_count) + 1,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('coupon_user_limits')->insert([
                        'coupon_id' => $freshCoupon->id,
                        'user_id' => $userId,
                        'use_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $freshCoupon->increment('usage_count');

            return true;
        });
    }

    // ----------------------------------------------------------------------
    // Helper response functions (mimics your controller helpers)
    // ----------------------------------------------------------------------
    public function successResponselocal(array $data = [], string $message = 'Success', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ];
    }

    public function failureResponselocal(string $message, int $code = 400): array
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        try {
            // 1. Laravel validation; the acting user is derived from the authenticated principal.
            $request->validate([
                'code' => 'required|string|max:50',
            ]);

            // Compute the subtotal from the authenticated user's DB-backed cart;
            // any client-supplied cart_total is ignored.
            $cartTotal = $this->calculateAuthenticatedCartTotal((int) $request->user()->id);
            $result = $this->applyCouponLocally(
                code: $request->code,
                cartTotal: $cartTotal,
                userId: $request->user()->id,
                consumeUsage: false
            );

            // 3. Convert the array response to JsonResponse with proper HTTP code
            return $this->successResponse(
                data: [
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data' => $result['data'] ?? null,
                ],
                code: $result['code']
            );

        } catch (ValidationException $e) {
            // Laravel validation errors
            return $this->failureResponse($e->errors(), 422);
        } catch (\Throwable $e) {

            return $this->failureResponse($e, 422);

        }
    }
}
