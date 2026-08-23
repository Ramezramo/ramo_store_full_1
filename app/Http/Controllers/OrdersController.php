<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHandlerRam;
use App\Helpers\PaymentConfig;
use App\Helpers\ShippingConfig;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\UserNote;
use App\Services\PricingService;
use App\Services\ReferralOrderLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrdersController extends Controller
{
    protected string $image_link;

    protected $shopController;

    public function __construct(ShopRegistrationController $shopController)
    {
        $this->image_link = AppConstants::DOMAIN.AppConstants::IMAGE_PATH;
        $this->shopController = $shopController;
    }
    // public function __construct()
    // {

    // }
    private function validatrionErrorResponse($errors, $code = 422)
    {
        return ResponseHandlerRam::validationError(
            errors: $errors,
            message: 'Validation failed',
            statusCode: $code
        );
    }

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

    public function getAllUserOrders(Request $request)
    {
        try {
            $userId = Auth::id();

            if (! $userId) {
                return $this->failureResponse('User is not authenticated', 401);
            }

            $perPage = $request->query('per_page', AppConstants::PAGINATION_LIMIT);
            $viewType = $request->query('view_type', 'short'); // 'short' or 'long'
            $orderId = $request->query('order_id');   // For single order detail

            // Base query
            $query = Order::where('customer_id', $userId)->orderBy('created_at', 'desc');

            // Single order detail view
            if ($orderId) {
                $order = $query->where('id', $orderId)->first();

                if (! $order) {
                    return $this->failureResponse('Order not found', 404);
                }

                $formattedOrder = $this->formatOrderForView($order, 'long');

                return $this->successResponse($formattedOrder, 'Order details retrieved successfully');
            }

            // Paginated list view
            $paginator = $query->paginate($perPage);

            // Preserve query params in pagination links (per_page, view_type)
            $paginator->appends($request->only(['per_page', 'view_type']));

            // Transform each Order model using your format method (keeps it as model → no array key errors)
            $paginator->getCollection()->transform(function ($order) use ($viewType) {
                return $this->formatOrderForView($order, $viewType);
            });

            // Convert to array only at the final step
            $ordersArray = $paginator->toArray();
            unset(
                $ordersArray['links'],
                // $data['first_page_url'],
                // $data['last_page_url'],
                $ordersArray['next_page_url'],
                // $data['prev_page_url'],
                // $data['path']
            );

            return $this->successResponse($ordersArray, 'Orders retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Error in getAllUserOrders: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse('An error occurred while fetching orders.', 500);
        }
    }

    /**
     * Format order data based on view type
     */
    private function formatOrderForView($order, $viewType = 'short')
    {
        $formattedOrder = [
            'order_id' => $order['id'],
            'order_number' => $order['number'] ?? '#'.str_pad($order['id'], 6, '0', STR_PAD_LEFT),
            'created_at' => $order['created_at'] ?? $order['date_created'],
            'status' => $order['status'],
            'final_total' => (float) ($order['final_total'] ?? 0),
            'currency' => $order['currency'] ?? 'USD',
            'products_ordered_total' => count($order['line_items'] ?? []),
            'products_items_count' => collect($order['line_items'] ?? [])->sum('quantity'),
            'payment_status' => $this->getPaymentStatus($order),
        ];

        // Short view - minimal data for list
        if ($viewType === 'short') {
            return $formattedOrder;
        }

        // Long view - full details
        if ($viewType === 'long') {
            $formattedOrder = $order->toArray(); // Start with full order data
        }

        return $formattedOrder;
    }

    /**
     * Format line items for display
     */
    private function formatLineItems($lineItems)
    {
        $formattedItems = [];
        foreach ($lineItems as $item) {
            $formattedItems[] = [
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'total' => (float) ($item['total'] ?? 0),
                'sku' => $item['sku'] ?? '',
                'image' => $this->getProductImage($item),
            ];
        }

        return $formattedItems;
    }

    /**
     * Get payment status
     */
    private function getPaymentStatus($order)
    {
        $status = $order['payment_method'] ?? '';
        $datePaid = $order['date_paid'] ?? null;

        if ($datePaid && $order['total'] > 0) {
            return 'paid';
        }

        return $status ? 'pending' : 'not_paid';
    }

    /**
     * Format address data
     */
    private function formatAddress($address)
    {
        if (empty($address) || ! is_array($address)) {
            return null;
        }

        return [
            'first_name' => $address['first_name'] ?? '',
            'last_name' => $address['last_name'] ?? '',
            'address_1' => $address['address_1'] ?? '',
            'address_2' => $address['address_2'] ?? '',
            'city' => $address['city'] ?? '',
            'state' => $address['state'] ?? '',
            'postcode' => $address['postcode'] ?? '',
            'country' => $address['country'] ?? '',
            'email' => $address['email'] ?? '',
            'phone' => $address['phone'] ?? '',
        ];
    }

    /**
     * Format shipping lines
     */
    private function formatShippingLines($shippingLines)
    {
        $formatted = [];
        foreach ($shippingLines as $line) {
            $formatted[] = [
                'method_title' => $line['method_title'] ?? '',
                'total' => (float) ($line['total'] ?? 0),
                'tracking_number' => $line['meta_data']['tracking_number'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Get basic order timeline
     */
    private function getOrderTimeline($order)
    {
        $timeline = [];

        // Add key events
        $timeline[] = [
            'event' => 'order_placed',
            'timestamp' => $order['date_created'] ?? $order['created_at'],
            'status' => 'Order Placed',
        ];

        if ($order['date_paid']) {
            $timeline[] = [
                'event' => 'payment_completed',
                'timestamp' => $order['date_paid'],
                'status' => 'Payment Completed',
            ];
        }

        // can extend this with more events from meta_data or status changes
        return $timeline;
    }

    /**
     * Get product image URL
     */
    private function getProductImage($item)
    {
        // Priority 1: Direct image from item
        if (isset($item['image']) && is_array($item['image']) && ! empty($item['image'])) {
            return $item['image'][0]['src'] ?? null;
        }

        // Priority 2: Check if product_id exists
        if (isset($item['product_id'])) {
            $productController = app(ProductController::class);
            $result = $productController->getProductImages($item['product_id'], 'thumbnail');

            if (isset($result['success']) && $result['success'] && ! empty($result['images'])) {
                return $this->image_link.'/'.$result['images'][0] ?? null;
            }
        }

        return null;
    }


    public function updateOrderState(Request $request)
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return $this->failureResponse('Unauthorized', 401);
            }

            $allowedStatuses = [
                'auto-draft',        // طلب تم إنشاؤه تلقائيًا لكن العميل لسة مخلّصش الدفع ولا فتح صفحة الدفع حتى
                'checkout-draft',    // العميل دخل صفحة الدفع (Checkout) وبدأ يكتب بياناته لكن لسة مكملش الطلب
                'order_placed',      // الطلب اتبعت رسمي ووصل للمتجر (العميل ضغط "إتمام الطلب" بنجاح) ← بداية الطلب الحقيقي
                'pending',           // الطلب موجود لكن الدفع لسة ما اتمدفعش (مثل الدفع عند الاستلام أو تحويل بنكي لسة ما اتأكدش)
                'processing',        // الدفع اتأكد وتمام، والطلب دلوقتي في مرحلة التجهيز (بنعبّي المنتجات وبنحضر الفاتورة)
                'packed',            // المنتجات اتعبّت في الكرتونة وجاهزة للشحن، بس لسة ما اتسلمتش لشركة الشحن
                'shipped',           // الشحنة اتسلمّت لشركة الشحن ورقم التتبع (Tracking) موجود
                'out_for_delivery',  // الشحنة وصلت المنطقة بتاعة العميل والمندوب خارج يوصّلها النهاردة
                'on-hold',           // الطلب متوقف مؤقتًا (مثلًا العميل طلب تعديل أو في مشكلة في المخزون أو في انتظار رد من العميل)
                'completed',         // الطلب خلصان تمامًا والعميل استلم الشحنة (أو في حالة الدفع عند الاستلام: اتم الدفع والتسليم)
                'cancelled',         // الطلب اتلغى (من العميل أو من الإدارة لأي سبب)
                'refund-req',        // العميل طلب إرجاع فلوس (رفع طلب رجوع)
                'refunded',          // الفلوس فعليًا رجعت للعميل (تمت عملية الـ Refund)
                'failed',            // الدفع فشل خالص (مثلًا البطاقة اتضربت وما نفعتش أو العملية اتلغت من البنك)
            ];

            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'status' => 'required|string|in:'.implode(',', $allowedStatuses),
                'tracking_number' => 'nullable|string|max:100|required_if:status,shipped',
                'carrier' => 'nullable|string|required_if:status,shipped|in:egypt_post,bosta,pharmaexpress,casinex,aramex,dhl,fedex,dpd,ups,own_fleet,other|max:50',
                'note' => 'nullable|string|max:1000',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            // === Secure Vendor Authorization ===
            // This must run before any state-dependent branch so a vendor cannot
            // probe the current status of an order they do not own.
            if (! Gate::forUser($user)->check('vendor', $order)) {
                return $this->failureResponse('Forbidden: You cannot update this order', 403);
            }

            $newStatus = $validated['status'];
            $oldStatus = $order->status;

            // Prevent no-op only after vendor ownership has been confirmed.
            if ($oldStatus === $newStatus) {
                return $this->failureResponse("Order is already {$this->getStatusLabel($newStatus)}", 400);
            }

            // === Prepare Timeline Entry ===
            $timelineEntry = [
                'timestamp' => now(),
                'changed_by' => $user->id,
                'changed_by_name' => $user->name ?? $user->email ?? 'System',
                'from' => $oldStatus,
                'to' => $newStatus,
                'from_label' => $this->getStatusLabel($oldStatus),
                'to_label' => $this->getStatusLabel($newStatus),
            ];

            // Customize event type and extra data
            match ($newStatus) {
                'packed' => $timelineEntry += [
                    'event' => 'order_packed',
                    'message' => 'Order has been packed and is ready for shipping',
                ],
                'shipped' => $timelineEntry += [
                    'event' => 'order_shipped',
                    'message' => 'Order has been shipped',
                    'tracking_number' => $validated['tracking_number'] ?? null,
                    'carrier' => $validated['carrier'] ?? null,
                    'tracking_url' => $this->generateTrackingUrl($validated['carrier'] ?? '', $validated['tracking_number'] ?? ''),
                ],
                'completed' => $timelineEntry += [
                    'event' => 'order_delivered',
                    'message' => 'Order marked as delivered and completed',
                ],
                default => $timelineEntry += [
                    'event' => 'status_changed',
                    'message' => "Status changed to {$this->getStatusLabel($newStatus)}",
                ],
            };

            // Add optional manual note
            if (! empty($validated['note'])) {
                $timelineEntry['vendor_note'] = $validated['note'];
            }

            // Lifecycle state is platform-controlled, never mass assigned.
            $order->status = $newStatus;
            $order->tracking_number = $newStatus === 'shipped' ? ($validated['tracking_number'] ?? null) : $order->tracking_number;
            $order->carrier = $newStatus === 'shipped' ? ($validated['carrier'] ?? null) : $order->carrier;
            $order->save();

            app(ReferralOrderLifecycle::class)->dispatchForTransition(
                (int) $order->id,
                $oldStatus,
                $newStatus,
            );

            // === Append to Timeline ===
            $timeline = $order->fresh()->timeline ?? [];
            if (! is_array($timeline)) {
                $timeline = [];
            }
            $timeline[] = $timelineEntry;
            $order->update(['timeline' => $timeline]);

            // === Internal Note (for admin panel) ===
            // === Internal Note (for admin panel) ===
            try {
                UserNote::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id ?? null,           // لو الـ user مش موجود هيحط null
                    'note' => "Status → {$this->getStatusLabel($newStatus)}"
                        .($newStatus === 'shipped'
                            ? " | Tracking: {$validated['tracking_number']} ({$validated['carrier']})"
                            : '')
                        .(! empty($validated['note']) ? ' | Note: '.strip_tags($validated['note']) : ''),
                    'customer_note' => false,
                ]);
            } catch (\Exception $e) {
                // لو حصل أي مشكلة في الـ note (زي اللي حصل دلوقتي) متوقفش باقي العملية
                Log::warning('Failed to create UserNote for order '.$order->id, [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                // متعملش return failure هنا → خلي الـ response يطلع success عادي
            }

            return $this->successResponse($order->refresh(), 'Order status updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validatrionErrorResponse($e->errors(), 422, true);
        } catch (\Exception $e) {
            Log::error('Order status update failed', [
                'user_id' => $user->id ?? null,
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse($e, 500);
        }
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'order_placed' => 'Order Placed',
            'pending' => 'Awaiting Payment',
            'processing' => 'Processing',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'on-hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refund-req' => 'Refund Requested',
            'refunded' => 'Refunded',
            'failed' => 'Payment Failed',
            'checkout-draft' => 'Draft',
            'auto-draft' => 'Auto-Saved Draft',
            default => ucwords(str_replace(['-', '_'], ' ', $status)),
        };
    }

    private function generateTrackingUrl(string $carrier, string $trackingNumber): ?string
    {
        if (empty($trackingNumber)) {
            return null;
        }

        $carrier = strtolower(trim($carrier));

        return match ($carrier) {
            // Global (already there, but Egypt-specific tweaks)
            'fedex' => "https://www.fedex.com/en-eg/tracking.html?tracknumber={$trackingNumber}",
            'ups' => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'dhl', 'dhl_express' => "https://www.dhl.com/eg-en/home/tracking.html?trackingNumber={$trackingNumber}",
            'aramex' => "https://www.aramex.com/eg/en/track/shipments?trackingNumber={$trackingNumber}",

            // Egypt Local Additions
            'egypt_post' => "https://www.egyptpost.org/ar/?trackingNumber={$trackingNumber}",
            'bosta' => "https://bosta.co/en/track?trackNumber={$trackingNumber}",
            'pharmaexpress' => "https://pharmaexpress.com/track?awb={$trackingNumber}",
            'casinex' => "https://www.casinex.com/track-shipment?number={$trackingNumber}",
            'dpd' => "https://www.dpd.com/eg/en/tracking/?query={$trackingNumber}",

            // Other globals (from before)
            'dhl_ecommerce' => "https://webtrack.dhlecs.com/?trackingnumber={$trackingNumber}",
            'bluedart' => "https://www.bluedart.com/web/guest/track?trackdto.awbno={$trackingNumber}",
            'delhivery' => "https://www.delhivery.com/tracking#{$trackingNumber}",
            'ecom_express' => "https://ecomexpress.in/tracking/?shipment={$trackingNumber}",
            'dtdc' => "https://www.dtdc.in/tracking/shipment-tracking.asp?awbno={$trackingNumber}",
            'india_post' => "https://www.indiapost.gov.in/_layouts/15/DOP.Portal/TrackConsignment.aspx?TconsignmentNumber={$trackingNumber}",
            'tnt' => "https://www.tnt.com/express/en_gb/site/shipping-tools/track-and-trace.html?searchType=con&cons={$trackingNumber}",
            'royal_mail' => "https://www.royalmail.com/track-your-item#/tracking-results/{$trackingNumber}",
            'australia_post' => "https://auspost.com.au/mypost/track/#/details/{$trackingNumber}",
            'canada_post' => "https://www.canadapost-postescanada.ca/track-reperage/en#/resultList?searchFor={$trackingNumber}",
            'ninja_van' => "https://www.ninjavan.co/en-my/tracking/?tracking_number={$trackingNumber}",
            'jnt', 'j&t' => "https://www.jtexpress.com/track/{$trackingNumber}",

            // Fallback: No auto-link for locals without public URLs
            'own_fleet', 'lalamove', 'grab_express', 'other' => null,

            default => null,
        };
    }

    public function createOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // ──────── IDEMPOTENCY ────────
                'idempotency_key' => 'required|uuid',

                // ──────── ORDER BASICS ────────
                'coupon' => 'nullable|string|max:30|exists:coupons,code',
                'currency' => 'required|string|size:3|in:EGP,USD,EUR',
                'payment_method' => 'required|string|in:cod,bank_transfer,vodafone_cash,fawry,wallet,credit_card',
                'payment_method_title' => 'required|string|max:100',

                // ──────── BILLING & SHIPPING (unchanged, perfect as-is) ────────
                'billing' => 'required|array',
                'billing.first_name' => 'required|string|max:100',
                'billing.last_name' => 'required|string|max:100',
                'billing.email' => 'required|email|max:255',
                'billing.address_1' => 'required|string|max:255',
                'billing.address_2' => 'nullable|string|max:255',
                'billing.company' => 'nullable|string|max:100',
                'billing.city' => 'required|string|max:100',
                'billing.state' => 'required|string|max:100',
                'billing.postcode' => 'nullable|string|max:20',
                'billing.country' => 'required|string|size:2|in:EG',
                'billing.phone' => ['required', 'string', function ($attr, $value, $fail) {
                    $clean = preg_replace('/\D/', '', $value);
                    if (strlen($clean) >= 10 && ! preg_match('/^(20|0)?1[0125][0-9]{8}$/', $clean)) {
                        $fail('Please enter a valid Egyptian mobile number.');
                    }
                }],

                'shipping' => 'required|array',
                'shipping.first_name' => 'required|string|max:100',
                'shipping.last_name' => 'required|string|max:100',
                'shipping.email' => 'nullable|email|max:255',
                'shipping.address_1' => 'required|string|max:255',
                'shipping.address_2' => 'nullable|string|max:255',
                'shipping.company' => 'nullable|string|max:100',
                'shipping.city' => 'required|string|max:100',
                'shipping.state' => 'required|string|max:100',
                'shipping.postcode' => 'nullable|string|max:20',
                'shipping.country' => 'required|string|size:2|in:EG',
                'shipping.phone' => ['required', 'string', function ($attr, $value, $fail) {
                    $clean = preg_replace('/\D/', '', $value);
                    if (strlen($clean) >= 10 && ! preg_match('/^(20|0)?1[0125][0-9]{8}$/', $clean)) {
                        $fail('Shipping phone must be a valid Egyptian mobile number.');
                    }
                }],

                // ──────── SHIPPING LINES ────────
                'shipping_lines' => 'required|array|min:1|max:5',
                'shipping_lines.*.method_id' => 'required|string',
                'shipping_lines.*.method_title' => 'required|string',
                'shipping_lines.*.total' => 'required|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return $this->failureResponse('Validation failed: '.json_encode($validator->errors(), JSON_UNESCAPED_UNICODE), 422);
            }

            $validatedData = $validator->validated();
            $userId        = Auth::id();
            $idempotencyKey = $validatedData['idempotency_key'];

            // ──────── Idempotency pre-check ────────
            // If the same key was already successfully processed within the last 24 hours,
            // return the original order without touching stock or creating a new record.
            $existingKey = DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $userId)
                ->where('created_at', '>', now()->subHours(24))
                ->first();

            if ($existingKey) {
                if ($existingKey->order_id) {
                    // Completed request — replay the stored order
                    $cachedOrder = Order::find($existingKey->order_id);
                    if ($cachedOrder) {
                        return $this->successResponse($cachedOrder, 'Order already created (idempotent replay).');
                    }
                }
                // order_id is null → another request with this key is still in-flight
                return $this->failureResponse('A request with this idempotency key is already being processed. Please try again shortly.', 409);
            }

            // ──────── Load cart from DB (server-side source of truth) ────────
            $cartItems = DB::table('cart_items')->where('user_id', $userId)->get();
            if ($cartItems->isEmpty()) {
                return $this->failureResponse('Your cart is empty.', 422);
            }

            $validatedData['line_items'] = $cartItems->map(fn($item) => [
                'product_id'           => $item->product_id,
                'variation_id'         => $item->variation_id,
                'quantity'             => $item->qty,
                'main_variation_order' => 0,
            ])->all();

            // ──────── STEP 1: Collect IDs ────────
            $productIds = array_column($validatedData['line_items'], 'product_id');

            // Get all variation IDs that are explicitly sent and not zero
            $explicitVariationIds = collect($validatedData['line_items'])
                ->pluck('variation_id')
                ->filter(fn ($id) => ! empty($id) && $id != 0)
                ->unique()
                ->values()
                ->toArray();

            // ──────── STEP 2: Load Products ────────
            $products = Product::whereIn('id', $productIds)
                ->select('id', 'name', 'vendor_id', 'sku', 'minimum_order_qty', 'max_orders_per_person', 'sold_individually', 'discount_percentage', 'manage_stock'
)
                ->get()
                ->keyBy('id');

            // ──────── STEP 3: Load ALL Needed Variations (including main ones) ────────
            $variations = ProductVariation::whereIn('product_id', $productIds) // All variations of these products
                ->when(! empty($explicitVariationIds), function ($query) use ($explicitVariationIds) {
                    return $query->orWhereIn('id', $explicitVariationIds); // Plus any specific ones sent
                })
                ->select('id', 'product_id', 'main_variation', 'price', 'regular_price', 'sale_price', 'stock_quantity', 'stock_status', 'status', 'attributes', 'images'
)
                ->get()
                ->keyBy('id'); // Critical: fast lookup by ID

            // ──────── STEP 4 → Order Creation (transaction with pessimistic locking) ────────
            $order = DB::transaction(function () use ($validatedData, $userId, $request, $products, $variations, $idempotencyKey) {
                // ── Claim the idempotency key atomically ──
                // insertOrIgnore returns the number of rows inserted (1 = claimed, 0 = conflict).
                $claimed = DB::table('idempotency_keys')->insertOrIgnore([
                    'key'        => $idempotencyKey,
                    'user_id'    => $userId,
                    'order_id'   => null,
                    'created_at' => now(),
                ]);

                if (! $claimed) {
                    // Another concurrent request beat us to this key.
                    // Lock the row and inspect it to decide how to respond.
                    $keyRecord = DB::table('idempotency_keys')
                        ->where('key', $idempotencyKey)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if ($keyRecord && $keyRecord->order_id) {
                        // The other request already finished — replay its order.
                        return Order::find($keyRecord->order_id);
                    }

                    // The other request is still in-flight inside its own transaction.
                    throw new \InvalidArgumentException(
                        'A request with this idempotency key is already being processed. Please try again shortly.',
                        409
                    );
                }

                $calculatedTotal = 0;
                $enrichedLineItems = [];

                foreach ($validatedData['line_items'] as $item) {
                    $productId = $item['product_id'];
                    $quantity  = intval($item['quantity'] ?? 1);

                    $product = $products->get($productId);
                    if (! $product) {
                        throw new \InvalidArgumentException("Product ID {$productId} not found.", 422);
                    }

                    $variation = null;

                    // Resolve the requested variation from the server-side cart data.
                    if (! empty($item['main_variation_order'])) {
                        $variation = $variations
                            ->where('product_id', $productId)
                            ->where('main_variation', 1)
                            ->first();

                        if (! $variation) {
                            throw new \InvalidArgumentException("This product (ID: {$productId}) has no main variation defined.", 422);
                        }
                    } elseif (! empty($item['variation_id']) && $item['variation_id'] != 0) {
                        $variation = $variations->get($item['variation_id']);

                        if (! $variation || (int) $variation->product_id !== (int) $productId) {
                            throw new \InvalidArgumentException("Invalid variation ID {$item['variation_id']} for product {$productId}.", 422);
                        }
                    } else {
                        throw new \InvalidArgumentException("You must provide either 'main_variation_order' or a valid 'variation_id' for product: {$product->name}", 422);
                    }

                    // Re-read the authoritative row under lock so price, status, and stock
                    // cannot become stale between cart loading and order creation.
                    $variation = ProductVariation::where('id', $variation->id)->lockForUpdate()->first();
                    if (! $variation || (int) $variation->product_id !== (int) $productId) {
                        throw new \InvalidArgumentException("The selected variation for {$product->name} is no longer available.", 422);
                    }
                    if (($variation->stock_status ?? 'instock') === 'outofstock') {
                        throw new \InvalidArgumentException("The selected variation of {$product->name} is currently out of stock.", 422);
                    }
                    if (($variation->status ?? 'publish') !== 'publish') {
                        throw new \InvalidArgumentException("The selected variation of {$product->name} is not available.", 422);
                    }

                    $variations->put($variation->id, $variation);
                    $price = PricingService::effectiveVariationPrice($variation, $product);

                    // ── Vendor per-order quantity limits ──
                    if ($product->sold_individually && $quantity > 1) {
                        throw new \InvalidArgumentException("'{$product->name}' can only be purchased one at a time.", 422);
                    }
                    if ($product->minimum_order_qty > 1 && $quantity < $product->minimum_order_qty) {
                        throw new \InvalidArgumentException("Minimum order quantity for '{$product->name}' is {$product->minimum_order_qty}.", 422);
                    }
                    if ($product->max_orders_per_person > 0 && $quantity > $product->max_orders_per_person) {
                        throw new \InvalidArgumentException("You can only order up to {$product->max_orders_per_person} units of '{$product->name}' per order.", 422);
                    }

                    // Acquire a row-level lock, re-read stock from DB, then decrement atomically.
                    // This prevents overselling when two requests race on the same variation.
                    if ((bool) ($product->manage_stock ?? false)) {
                        $lockedVariation = ProductVariation::where('id', $variation->id)->lockForUpdate()->first();
                        if ($lockedVariation->stock_quantity < $quantity) {
                            throw new \InvalidArgumentException("Insufficient stock for {$product->name}. Only {$lockedVariation->stock_quantity} left.", 422);
                        }
                        $lockedVariation->decrement('stock_quantity', $quantity);
                    }

                    $subtotal         = $price * $quantity;
                    $calculatedTotal += $subtotal;

                    $enrichedLineItems[] = [
                        'item'       => $item,
                        'product'    => $product,
                        'variation'  => $variation,
                        'price_used' => $price,
                        'subtotal'   => $subtotal,
                        'quantity'   => $quantity,
                    ];
                }

                // ──────── Coupon Logic ────────
                $couponController = app(CouponController::class);
                $discountTotal        = '0.00';
                $finalTotal           = $calculatedTotal;
                $couponLines          = [];
                $couponData           = null;
                $appliedCoupon        = null;

                if (! empty($validatedData['coupon'])) {
                    $couponCode = strtoupper(trim($validatedData['coupon']));

                    $couponResult = $couponController->applyCouponLocally(
                        code: $couponCode,
                        cartTotal: $calculatedTotal,
                        userId: $userId,
                        consumeUsage: true
                    );

                    if (! $couponResult['success']) {
                        throw new \InvalidArgumentException($couponResult['message'], $couponResult['code'] ?: 422);
                    }

                    $resultData    = $couponResult['data'];
                    $appliedCoupon = $resultData['coupon'];
                    $discountAmount = $resultData['discount_amount'];
                    $newTotal      = $resultData['new_total'];

                    $discountTotal       = number_format($discountAmount, 2, '.', '');
                    $finalTotal          = $newTotal;

                    $couponLines = [[
                        'code'                      => $couponCode,
                        'cart_total_before_discount' => $calculatedTotal,
                        'cart_final_total'           => $finalTotal,
                        'discount'                  => $discountTotal,
                    ]];

                    $couponData           = $appliedCoupon;
                    $couponData['amount'] = (string) ($couponData['amount'] ?? 0);
                }
                $afterDiscount = max(0, $calculatedTotal - (float) $discountTotal);
                $hasFreeShippingCoupon = (bool) ($couponData['free_shipping'] ?? false);
                $shippingFee = $hasFreeShippingCoupon
                    ? 0.0
                    : ShippingConfig::feeForSubtotal($afterDiscount);
                $codFee = $validatedData['payment_method'] === 'cod' && ! $hasFreeShippingCoupon
                    ? PaymentConfig::codFee()
                    : 0.0;
                $finalTotal = round($afterDiscount + $shippingFee + $codFee, 2);
                $shippingMethodId = preg_replace('/[^a-zA-Z0-9:_-]/', '', (string) ($validatedData['shipping_lines'][0]['method_id'] ?? 'standard_shipping')) ?: 'standard_shipping';
                $shippingLines = [[
                    'method_id' => $shippingMethodId,
                    'method_title' => $shippingFee > 0 ? 'Standard shipping' : 'Free shipping',
                    'total' => number_format($shippingFee, 2, '.', ''),
                ]];
                $timeLine = [['event' => 'order_placed',
                    'timestamp' => now(),
                    'status'    => 'Order Placed']];
                // will make a changes in the change order status to add more events
                // ──────── Vendor Info ────────
                $vendorIds  = $products->pluck('vendor_id')->unique()->filter()->values()->all();
                $vendorsById = [];
                foreach ($vendorIds as $vendorId) {
                    $vendorsById[$vendorId] = $this->shopController->getUserLocally($vendorId);
                }

                $protectedOrderAttributes = [
                    // Platform-calculated or lifecycle-controlled values: never mass assign.
                    'set_paid'       => false,
                    'status'         => 'order_placed',
                    'original_total' => (int) round($calculatedTotal),
                    'discount_total' => round((float) $discountTotal, 2),
                    'discount_tax'   => '0.00',
                    'shipping_total' => round($shippingFee, 2),
                    'shipping_tax'   => '0.00',
                    'cart_tax'       => '0.00',
                    'total_tax'      => '0.00',
                    'final_total'    => round($finalTotal, 2),
                ];

                $orderDbData = [
                    'parent_id'           => 0,
                    'timeline'            => $timeLine,
                    'currency'            => $validatedData['currency'],
                    'version'             => '0.0.0',
                    'prices_include_tax'  => false,
                    'date_created'        => now(),
                    'date_modified'       => now(),
                    'order_key'           => 'RAMORDER'.Str::upper(Str::random(8)).now()->format('ymd'),
                    'billing'             => $validatedData['billing'],
                    'shipping'            => $validatedData['shipping'],
                    'payment_method'      => $validatedData['payment_method'],
                    'payment_method_title' => $validatedData['payment_method_title'],
                    'transaction_id'      => '',
                    'customer_ip_address' => $request->ip(),
                    'customer_userAgent'  => $request->userAgent(),
                    'created_via'         => 'flutter-api',
                    'customer_note'       => $validatedData['customer_note'] ?? '',
                    'cart_hash'           => '',
                    'number'              => 0,
                    'coupon_code'         => $validatedData['coupon'] ?? null,
                    'coupon_applied'      => ! empty($couponLines),
                    'meta_data'           => ! empty($couponData) ? [['key' => '_coupon_data', 'value' => $couponData]] : [],
                    'line_items'          => collect($this->buildLineItems($enrichedLineItems))->map(function ($line) use ($variations) {
                        $variation = ($line['variation_id'] ?? 0) ? ($variations->get($line['variation_id']) ?? null) : null;

                        return [
                            'product_id'   => $line['product_id'],
                            'variation_id' => $line['variation_id'] ?? 0,
                            'name'         => $line['name'],
                            'quantity'     => $line['quantity'],
                            'sku'          => $line['sku'],
                            'image'        => $line['image']['src'] ?? null,

                            // Price breakdown
                            'price' => [
                                'final'    => $line['price'],
                                'subtotal' => $line['subtotal'],
                                'total'    => $line['total'],
                            ],

                            // Detailed variation info
                            'variation' => $variation ? [
                                'id'             => $variation->id,
                                'attributes'     => $variation->attributes ?? [],
                                'regular_price'  => $variation->regular_price ? number_format($variation->regular_price, 2, '.', '') : null,
                                'sale_price'     => $variation->sale_price ? number_format($variation->sale_price, 2, '.', '') : null,
                                'base_price'     => $variation->price ? number_format($variation->price, 2, '.', '') : null,
                                'price_used'     => $line['price'],
                                'stock_quantity' => $variation->stock_quantity,
                                'images'         => $variation->images ?? [],
                            ] : null,

                            // Raw meta data
                            'meta_data' => $line['meta_data'] ?? [],
                        ];
                    })->values()->all(),
                    'tax_lines'          => [],
                    'shipping_lines'     => $shippingLines,
                    'fee_lines'          => $codFee > 0 ? [[
                        'name' => 'Cash on Delivery fee',
                        'total' => number_format($codFee, 2, '.', ''),
                    ]] : [],
                    'coupon_lines'       => $couponLines,
                    'refunds'            => [],
                    'payment_url'        => '',
                    'is_editable'        => true,
                    'needs_payment'      => true,
                    'needs_processing'   => true,
                    'date_created_gmt'   => now(),
                    'date_modified_gmt'  => now(),
                    'date_completed_gmt' => '1970-01-01 00:00:00',
                    'date_paid_gmt'      => '1970-01-01 00:00:00',
                    'bacs_info'          => [[
                        'account_name'  => 'TEST RAMO', 'account_number' => '1212121212121212',
                        'bank_name'     => 'FARMERS STATE BANK & TRUST', 'sort_code' => '12121212',
                        'iban'          => 'TSTSTSTSTSTSSTTSTSTSTSSTST', 'bic' => 'TSTSTSTST',
                    ]],
                    'currency_symbol'     => $validatedData['currency'] === 'EGP' ? 'EGP' : '$',
                    '_links'             => [
                        'self'       => [['href' => 'https://ramo.io/wp-json/api/flutter_order/orders/']],
                        'collection' => [['href' => 'https://ramo.io/wp-json/api/flutter_order/orders']],
                        'customer'   => [['href' => "https://ramo.io/wp-json/api/flutter_order/customers/{$userId}"]],
                    ],
                    'parent_vendors_data' => $vendorsById,
                    'parent_vendors_ids'  => array_keys($vendorsById),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                // ──────── Create Order ────────
                $order = new Order($orderDbData);
                foreach ($protectedOrderAttributes as $attribute => $value) {
                    $order->{$attribute} = $value;
                }
                // The authenticated customer owns the order; never mass assign ownership.
                $order->customer_id = $userId;
                $order->save();
                $order->update(['number' => $order->id + 2000]);

                // ──────── Seal the idempotency key with the new order ID ────────
                // Any future request with the same key will get this order replayed to them.
                DB::table('idempotency_keys')
                    ->where('key', $idempotencyKey)
                    ->where('user_id', $userId)
                    ->update(['order_id' => $order->id]);

                // ──────── Clear the user's cart after successful order ────────
                DB::table('cart_items')->where('user_id', $userId)->delete();

                return $order;
            });

            return $this->successResponse($order, 'Order created successfully.');

        } catch (\InvalidArgumentException $e) {
            // User-facing validation errors (bad input, out of stock, etc.) — no server log needed
            return $this->failureResponse($e->getMessage(), $e->getCode() ?: 422);
        } catch (\Exception $e) {
            Log::error('Order creation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->failureResponse($e, 500);
        }
    }

    // ──────── Updated buildLineItems with Variation Support ────────
    private function buildLineItems($enrichedItems)
    {
        return collect($enrichedItems)->map(function ($ei) {
            $item = $ei['item'];
            $product = $ei['product'];
            $variation = $ei['variation'];

            $name = $product->name;
            $metaData = $item['meta_data'] ?? [];

            if ($variation) {
                // Add variation attributes to name and meta_data
                if (! empty($variation->attributes)) {
                    $attrString = collect($variation->attributes)->map(function ($value, $key) {
                        return ucfirst(str_replace('_', ' ', $key)).': '.$value;
                    })->join(' | ');

                    $name .= " - $attrString";

                    foreach ($variation->attributes as $key => $value) {
                        $metaData[] = [
                            'key' => $key,
                            'value' => $value,
                            'display_key' => ucfirst(str_replace('_', ' ', $key)),
                            'display_value' => $value,
                        ];
                    }
                }
            }

            $subtotal = number_format($ei['subtotal'], 2, '.', '');

            return [
                'product_id' => $product->id,
                'variation_id' => $variation ? $variation->id : 0,
                'name' => $name,
                'quantity' => $item['quantity'],
                'price' => number_format($ei['price_used'], 2, '.', ''),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'subtotal_tax' => '0.00',
                'total_tax' => '0.00',
                'tax_class' => '',
                'sku' => $product->sku ?? '',
                'meta_data' => $metaData,
                'image' => $variation && ! empty($variation->images[0]) ? ['src' => $variation->images[0]] : null,
            ];
        })->values()->toArray();
    }
}
