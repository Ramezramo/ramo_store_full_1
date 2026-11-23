<?php

namespace Tests\Feature;

use App\Helpers\AuthConfig;
use App\Models\User;
use App\Models\VendorUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebsitePagesTest extends TestCase
{
    use DatabaseTransactions;

    private User $customer;
    private VendorUser $seller;
    private int $product;
    private int $variation;
    private int $order;
    private int $shipment;
    private int $refund;

    protected function setUp(): void
    {
        parent::setUp();
        if (!str_contains(DB::connection()->getDatabaseName(), '_test')) {
            $this->markTestSkipped('Select a dedicated PostgreSQL database with _test in its name.');
        }

        $this->withSession(['locale' => 'en', 'locale_source' => 'manual']);
        $this->customer = User::create(['name' => 'Page Test Customer', 'email' => 'page-customer@example.test', 'password' => 'test-only-password']);
        $this->seller = VendorUser::forceCreate([
            'first_name' => 'Page', 'last_name' => 'Seller', 'email' => 'page-seller@example.test',
            'phone' => '01000000002', 'password' => 'test-only-password', 'status' => 'approved',
            'shop_name' => 'Page Test Store', 'shop_address' => 'Test Street', 'auth_token' => '0',
            'holder_name' => '', 'bank_name' => 'not set', 'branch' => 'not set',
        ]);
        $this->product = DB::table('products_data')->insertGetId([
            'name' => 'Page Test Lamp', 'slug' => 'page-test-lamp', 'sku' => 'PAGE-TEST',
            'search_text' => 'Page Test Lamp', 'vendor_id' => $this->seller->id, 'status' => 'publish',
            'acceptance_status' => 'approved', 'purchasable' => true, 'minimum_order_qty' => 1,
            'images' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->variation = DB::table('product_variations')->insertGetId([
            'product_id' => $this->product, 'main_variation' => true, 'status' => 'publish',
            'stock_status' => 'instock', 'stock_quantity' => 5, 'attributes' => '{}', 'images' => '[]',
            'regular_price' => 100, 'price' => 100, 'sale_price' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->order = DB::table('orders')->insertGetId([
            'customer_id' => $this->customer->id, 'order_key' => 'page-test-order', 'status' => 'pending',
            'currency' => 'EGP', 'currency_symbol' => 'EGP', 'payment_method' => 'cod',
            'payment_method_title' => 'Cash on Delivery', 'date_created' => now(), 'date_modified' => now(),
            'date_created_gmt' => now(), 'date_modified_gmt' => now(), 'date_paid_gmt' => '', 'date_completed_gmt' => '',
            'billing' => '{}', 'shipping' => '{}', 'line_items' => '[]', 'timeline' => '[]',
            'original_total' => 100, 'final_total' => 100, 'discount_total' => 0, 'number' => 1,
            'parent_id' => 0, 'cart_hash' => '', 'created_via' => 'website', 'payment_url' => '',
            'shipping_total' => 0, 'shipping_tax' => 0, 'cart_tax' => 0, 'total_tax' => 0,
            'is_editable' => true, 'needs_payment' => false, 'needs_processing' => true, 'set_paid' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->shipment = DB::table('order_sub_orders')->insertGetId([
            'parent_order_id' => $this->order, 'vendor_id' => $this->seller->id, 'customer_id' => $this->customer->id,
            'status' => 'pending', 'line_items' => '[]', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->refund = DB::table('refund_requests')->insertGetId([
            'order_id' => $this->order, 'vendor_id' => $this->seller->id, 'customer_id' => $this->customer->id,
            'type' => 'refund', 'reason' => 'other', 'description' => 'Page test refund', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public static function pages(): iterable
    {
        $pages = [
            'guest' => [
                'home' => 'web.home',
                'shop' => 'web.shop',
                'search' => 'web.search-entry',
                'offers' => 'web.offers',
                'product' => 'web.product',
                'vendor.store' => 'web.vendor',
                'cart' => 'web.cart',
                'wishlist' => 'web.wishlist',
                'login' => 'web.auth.login',
                'register' => 'web.auth.register',
                'auth.otp-verify' => 'web.auth.otp-verify',
                'auth.complete-profile' => 'web.auth.complete-profile',
                'password.forgot' => 'web.auth.forgot-password',
                'password.reset.form' => 'web.auth.reset-password',
                'guest.order' => 'web.guest-order-lookup',
                'order.track' => 'web.order-tracking',
                'policy.contact' => 'web.policy-page',
                'policy.privacy' => 'web.policy-page',
                'policy.terms' => 'web.policy-page',
                'policy.shipping' => 'web.policy-page',
                'policy.returns' => 'web.policy-page',
                'policy.payment' => 'web.policy-page',
                'vendor.login' => 'web.vendor.login',
                'admin.login' => 'admin.login',
            ],
            'customer' => [
                'account.hub' => 'web.account.hub',
                'account.profile' => 'web.account.profile',
                'account.referral' => 'web.account.referral',
                'account.orders' => 'web.account.orders',
                'account.order' => 'web.account.order-detail',
                'account.notifications' => 'web.account.notifications',
                'account.reviews' => 'web.account.reviews',
                'account.refunds' => 'web.account.refunds',
                'account.refunds.create' => 'web.account.refund-create',
                'account.refunds.show' => 'web.account.refund-show',
                'vendor.register' => 'web.vendor.application',
                'email.verify.notice' => 'web.auth.verify-email',
                'checkout' => 'web.checkout',
                'order.success' => 'web.order-success',
            ],
            'seller' => [
                'vendor.dashboard' => 'web.vendor.dashboard',
                'vendor.store.profile' => 'web.vendor.store-profile',
                'vendor.orders' => 'web.vendor.orders.index',
                'vendor.orders.show' => 'web.vendor.orders.show',
                'vendor.refunds' => 'web.vendor.refunds',
                'vendor.refunds.show' => 'web.vendor.refund-show',
                'vendor.products' => 'web.vendor.products.index',
                'vendor.products.create' => 'web.vendor.products.create',
                'vendor.products.show' => 'web.vendor.products.show',
                'vendor.products.edit' => 'web.vendor.products.create',
                'vendor.requests' => 'web.vendor.requests.index',
                'vendor.requests.create' => 'web.vendor.requests.create',
            ],
            'admin' => [
                'admin.dashboard' => 'admin.dashboard',
                'admin.users' => 'admin.users',
                'admin.users.flags' => 'admin.user-flags',
                'admin.orders' => 'admin.orders',
                'admin.orders.detail' => 'admin.order-detail',
                'admin.vendors' => 'admin.vendors',
                'admin.vendors.show' => 'admin.vendors.show',
                'admin.vendor-applications' => 'admin.vendor-applications',
                'admin.products' => 'admin.products',
                'admin.products.show' => 'admin.products.show',
                'admin.devices' => 'admin.devices',
                'admin.homepage-hero' => 'admin.homepage-hero',
                'admin.timeline' => 'admin.timeline',
                'admin.image-gallery' => 'admin.image-gallery',
                'admin.live.preview' => 'admin.live_preview',
                'admin.analytics' => 'admin.analytics',
                'admin.coupons' => 'admin.coupons',
                'admin.refunds' => 'admin.refunds',
                'admin.refunds.show' => 'admin.refunds.show',
                'admin.reviews' => 'admin.reviews',
                'admin.configs' => 'admin.configs.index',
                'admin.auth-settings' => 'admin.auth-settings',
                'admin.shipping-settings' => 'admin.shipping-settings',
                'admin.payment-methods' => 'admin.payment-methods',
                'admin.referral.index' => 'admin.referral',
                'admin.cbr' => 'admin.category-brand-requests.index',
            ],
        ];
        foreach ($pages as $role => $routes) {
            foreach ($routes as $route => $view) {
                yield "$role $route" => [$role, $route, $view];
            }
        }
        yield 'guest search results' => ['guest', 'search', 'web.search', ['q' => 'Page Test Lamp']];
        yield 'guest API reset form' => ['guest', '/api/user/reset-password-page', 'auth.reset-password'];
    }

    #[DataProvider('pages')]
    public function test_page(string $role, string $route, string $view, array $query = []): void
    {
        $id = match ($route) {
            'product', 'vendor.products.show', 'vendor.products.edit', 'admin.products.show' => $this->product,
            'vendor.store', 'admin.vendors.show' => $this->seller->id,
            'order.success', 'account.order', 'admin.orders.detail' => $this->order,
            'vendor.orders.show' => $this->shipment,
            'account.refunds.show', 'vendor.refunds.show', 'admin.refunds.show' => $this->refund,
            'admin.users.flags' => $this->customer->id,
            default => null,
        };
        if ($id !== null) {
            $query['id'] = $id;
        }
        if ($route === 'password.reset.form' || $route === '/api/user/reset-password-page') {
            $query = ['token' => 'test-form-token', 'email' => $this->customer->email];
        }
        $url = match ($route) {
            'password.reset.form' => '/reset-password?'.http_build_query($query),
            '/api/user/reset-password-page' => $route.'?'.http_build_query($query),
            default => route($route, $query),
        };
        if (in_array($role, ['admin', 'seller'], true) || str_starts_with($route, 'account.')) {
            $this->get($url)->assertRedirect(route('login'));
        }

        if ($role === 'admin') {
            $admin = User::forceCreate(['name' => 'Page Test Admin', 'email' => 'page-admin@example.test',
                'role' => json_encode(['admin']), 'password' => 'test-only-password']);
            $this->actingAs($admin);
        } elseif ($role === 'seller') {
            $this->actingAs($this->seller, 'vendor_web');
            // Storefront and vendor guards are independent in a real request.
            auth()->shouldUse('web');
        } elseif ($role === 'customer') {
            $this->actingAs($this->customer);
        }

        if ($route === 'checkout') {
            DB::table('cart_items')->insert(['user_id' => $this->customer->id, 'product_id' => $this->product,
                'variation_id' => $this->variation, 'qty' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        if ($route === 'register' || $route === 'auth.complete-profile') {
            AuthConfig::save(['email_login' => true, 'phone_otp_login' => $route === 'auth.complete-profile', 'auto_register_otp' => true]);
            if ($route === 'auth.complete-profile') {
                $this->withSession(['otp_temp_token' => 'test-token', 'otp_temp_phone' => '+201000000003',
                    'otp_temp_expires_at' => now()->addMinutes(3)->timestamp]);
            }
        }
        if ($route === 'admin.homepage-hero') {
            DB::table('homepage_heroes')->updateOrInsert(['id' => 1], ['enabled' => false, 'media_type' => 'image']);
        }

        $response = $this->withoutExceptionHandling()->get($url);
        $response->assertOk()->assertViewIs($view);
        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
        if (in_array($route, ['account.order', 'order.success', 'admin.orders.detail'], true)) {
            $response->assertViewHas('order', fn ($order) => (int) $order->id === $this->order);
        }
        if (in_array($route, ['product', 'vendor.products.show', 'vendor.products.edit', 'admin.products.show', 'checkout'], true) || $view === 'web.search') {
            $response->assertSee('Page Test Lamp');
        }
        if (in_array($route, ['account.refunds.show', 'vendor.refunds.show', 'admin.refunds.show'], true)) {
            $response->assertSee('Page test refund');
        }
    }

    public function test_storefront_reset_link_opens_the_storefront_form(): void
    {
        config(['mail.default' => 'array']);
        AuthConfig::save(['email_login' => true]);
        $this->post('/forgot-password', ['email' => $this->customer->email])->assertSessionHas('status');

        $mail = Mail::mailer()->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertSame($this->customer->email, $mail->getTo()[0]->getAddress());
        $document = new \DOMDocument();
        $document->loadHTML($mail->getHtmlBody());
        $url = $document->getElementsByTagName('a')->item(0)->getAttribute('href');
        $this->assertSame('/reset-password', parse_url($url, PHP_URL_PATH));
        $this->get($url)->assertOk()->assertViewIs('web.auth.reset-password');

        parse_str(parse_url($url, PHP_URL_QUERY), $fields);
        $password = 'Reset-password-for-page-test!';
        $this->post('/reset-password', [
            'email' => $fields['email'], 'token' => $fields['token'],
            'password' => $password, 'password_confirmation' => $password,
        ])->assertRedirect(route('login'))->assertSessionHasNoErrors();
        $this->post('/login', ['email' => $this->customer->email, 'password' => $password])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($this->customer);
    }

    public function test_every_browser_page_has_a_case(): void
    {
        $covered = array_column(iterator_to_array(self::pages()), 1);
        // These web routes serve data, files, language changes or authentication callbacks.
        $nonPages = ['health', 'sitemap', 'home.sections', 'home.products', 'language.switch',
            'cart.count', 'wishlist.state', 'search.suggestions', 'account.notifications.unread',
            'vendor.products.search', 'admin.products.search', 'admin.vendor-applications.identity',
            'auth.google', 'auth.google.callback', 'email.verify', 'sanctum.csrf-cookie'];
        $missing = [];
        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true) || !in_array('web', $route->middleware(), true)
                || str_starts_with($route->uri(), '_debugbar') || str_starts_with($route->uri(), 'storage/')) {
                continue;
            }
            if (!in_array($route->getName(), $covered, true) && !in_array($route->getName(), $nonPages, true)) {
                $missing[] = $route->uri();
            }
        }
        $this->assertSame([], $missing, 'Add a page case or classify the new endpoint.');
    }
}
