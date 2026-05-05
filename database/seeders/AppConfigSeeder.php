<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $configs = [
            // ── Identity ──────────────────────────────────────────────────────
            ['identity', null,  'app_name',    'App Name',            '"Ramo Store"',                       'Application display name',                     true,  1],
            ['identity', null,  'app_tagline', 'App Tagline',         '"Style That Speaks For Itself"',     'Tagline shown on splash/onboarding',            true,  2],
            ['identity', null,  'admin_email', 'Admin Email',         '"adminramoui@gmail.com"',            'Primary admin contact email',                   false, 3],
            ['identity', null,  'admin_name',  'Admin Name',          '"ramoui"',                           'Admin display name',                            false, 4],

            // ── Server ────────────────────────────────────────────────────────
            ['server', null, 'server_config', 'Server Config', json_encode([
                'type'            => 'user',
                'vendorAvailable' => false,
                'baseUrl'         => env('APP_URL', 'https://a8c774fa-0c2e-4a23-8ee1-58cd9836dadf-00-1bmcvifo8bqnv.kirk.replit.dev'),
                'imageurl'        => env('APP_URL', 'https://a8c774fa-0c2e-4a23-8ee1-58cd9836dadf-00-1bmcvifo8bqnv.kirk.replit.dev') . '/storage',
            ]), 'Server URL, type, and vendor settings', true, 1],

            // ── Theme ─────────────────────────────────────────────────────────
            ['theme', null, 'default_dark_theme', 'Dark Theme Default', 'false',         'Start in dark mode by default',      true, 1],
            ['theme', null, 'main_color',          'Primary Color',      '"#e85d26"',     'Primary brand color (hex)',           true, 2],
            ['theme', null, 'font_family',          'Body Font',          '"Roboto"',      'Body text font family',              true, 3],
            ['theme', null, 'font_header',          'Header Font',        '"Raleway"',     'Heading font family',                true, 4],
            ['theme', null, 'use_material3',        'Use Material 3',     'false',         'Use Material Design 3 components',   true, 5],
            ['theme', null, 'product_list_layout',  'Product List Layout','"list"',        'Default product list view (list/grid)', true, 6],
            ['theme', null, 'sticky_header',        'Sticky Header',      'false',         'Keep header visible while scrolling', true, 7],
            ['theme', null, 'show_chat',             'Show Chat',          'true',          'Show chat/support button',           true, 8],

            // ── Language ──────────────────────────────────────────────────────
            ['language', null, 'default_language', 'Default Language', '"en"', 'Default app language code (en, ar, de)', true, 1],
            ['language', null, 'languages_info', 'Available Languages', json_encode([
                ['name' => 'English', 'icon' => 'assets/images/country/gb.png',  'code' => 'en', 'text' => 'English',  'storeViewCode' => '',   'dir' => 'ltr'],
                ['name' => 'Arabic',  'icon' => 'assets/images/country/ar.png',  'code' => 'ar', 'text' => 'العربية', 'storeViewCode' => 'ar', 'dir' => 'rtl'],
                ['name' => 'German',  'icon' => 'assets/images/country/de.png',  'code' => 'de', 'text' => 'Deutsch',  'storeViewCode' => 'de', 'dir' => 'ltr'],
            ]), 'List of supported languages with flag icons and RTL flag', true, 2],

            // ── Currency ──────────────────────────────────────────────────────
            ['currency', null, 'default_currency', 'Default Currency', json_encode([
                'symbol' => '£', 'decimalDigits' => 2, 'symbolBeforeTheNumber' => false,
                'currency' => 'EGP', 'currencyCode' => 'EGP', 'smallestUnitRate' => 100,
            ]), 'Default currency for the app', true, 1],
            ['currency', null, 'currencies', 'Available Currencies', json_encode([
                ['symbol' => '£', 'decimalDigits' => 2, 'symbolBeforeTheNumber' => false, 'currency' => 'EGP', 'currencyCode' => 'EGP', 'smallestUnitRate' => 100],
                ['symbol' => '$', 'decimalDigits' => 2, 'symbolBeforeTheNumber' => true,  'currency' => 'USD', 'currencyCode' => 'USD', 'smallestUnitRate' => 100],
            ]), 'All supported currencies for currency switcher', true, 2],

            // ── Product ───────────────────────────────────────────────────────
            ['product', null, 'grid_count',              'Grid Column Count',    '3',      'Number of columns in grid view', true, 1],
            ['product', null, 'enable_rating',            'Enable Rating',        'true',   'Show star ratings on products',  true, 2],
            ['product', null, 'enable_review',            'Enable Reviews',       'true',   'Allow customer product reviews', true, 3],
            ['product', null, 'hide_out_of_stock',        'Hide Out of Stock',    'false',  'Hide products with zero stock',  true, 4],
            ['product', null, 'show_stock_status',        'Show Stock Status',    'true',   'Show In Stock / Out of Stock badge', true, 5],
            ['product', null, 'enable_cart',              'Enable Cart',          'true',   'Enable shopping cart',           true, 6],
            ['product', null, 'hide_empty_categories',    'Hide Empty Categories','true',   'Hide categories with no products', true, 7],
            ['product', null, 'hide_empty_tags',          'Hide Empty Tags',      'true',   'Hide tags with no products',     true, 8],
            ['product', null, 'enable_sku_search',        'Enable SKU Search',    'true',   'Search by product SKU code',     true, 9],
            ['product', null, 'excluded_category',        'Excluded Category IDs','"311"',  'Comma-separated category IDs to exclude from lists', true, 10],
            ['product', null, 'product_detail_config', 'Product Detail Config', json_encode([
                'height' => 0.6, 'marginTop' => 0, 'safeArea' => false, 'showVideo' => true,
                'showBrand' => true, 'layout' => 'simpleType', 'borderRadius' => 3.0,
                'ShowSelectedImageVariant' => true, 'autoPlayGallery' => false,
                'SliderShowGoBackButton' => true, 'ShowImageGallery' => true,
                'SliderIndicatorType' => 'number', 'ForceWhiteBackground' => false,
                'AutoSelectFirstAttribute' => true, 'enableReview' => true,
                'attributeImagesSize' => 50.0, 'showSku' => true,
                'showStockQuantity' => true, 'showProductCategories' => true,
                'showProductTags' => true, 'hideInvalidAttributes' => false,
                'showQuantityInList' => false, 'showAddToCartInSearchResult' => true,
                'productListItemHeight' => 125, 'limitDayBooking' => 14,
                'showRelatedProduct' => true, 'showRecentProduct' => true,
                'productImageLayout' => 'page', 'expandDescription' => true,
                'expandReviews' => true, 'fixedBuyButtonToBottom' => false,
            ]), 'Product detail page layout and feature flags', true, 11],
            ['product', null, 'product_variant_layout', 'Variant Display Layout', json_encode([
                'color' => 'color', 'size' => 'box', 'height' => 'option', 'color-image' => 'image',
            ]), 'How each variant type is displayed (color circle/box/option/image)', true, 12],
            ['product', null, 'product_variant_language', 'Variant Labels by Language', json_encode([
                'en' => ['color' => 'Color', 'size' => 'Available Sizes', 'height' => 'Height', 'color-image' => 'Color'],
                'ar' => ['color' => 'اللون', 'size' => 'الاحجام المتوفرة', 'height' => 'الارتفاع', 'color-image' => 'اللون'],
                'de' => ['color' => 'Farbe', 'size' => 'Verfügbare Größen', 'height' => 'Höhe', 'color-image' => 'Farbe'],
            ]), 'Variant attribute label translations per language', true, 13],
            ['product', null, 'cart_detail', 'Cart Limits', json_encode([
                'minAllowTotalCartValue' => 0, 'maxAllowQuantity' => 10,
            ]), 'Cart minimum total and maximum per-item quantity', true, 14],
            ['product', null, 'sale_off_config', 'Sale/Discount Config', json_encode([
                'ShowCountDown' => true, 'HideEmptySaleOffLayout' => false, 'Color' => '#e85d26',
            ]), 'Sale countdown timer and color settings', true, 15],

            // ── Payment ───────────────────────────────────────────────────────
            ['payment', null, 'payment_config', 'Payment Config', json_encode([
                'DefaultCountryISOCode'      => 'EG',
                'DefaultStateISOCode'        => 'Cairo',
                'EnableShipping'             => true,
                'EnableAddress'              => true,
                'EnableCustomerNote'         => true,
                'EnableAlphanumericZipCode'  => false,
                'EnableReview'              => true,
                'allowSearchingAddress'     => true,
                'GuestCheckout'             => true,
                'EnableOnePageCheckout'     => false,
                'NativeOnePageCheckout'     => false,
                'ShowWebviewCheckoutSuccessScreen' => true,
                'CheckoutPageSlug'          => ['en' => 'checkout', 'ar' => 'checkout'],
                'EnableCreditCard'          => false,
                'UpdateOrderStatus'         => false,
                'ShowOrderNotes'            => true,
                'EnableRefundCancel'        => true,
                'RefundPeriod'              => 7,
                'SmartCOD'                  => ['enabled' => true, 'extraFee' => 10, 'amountStop' => 200],
                'excludedPaymentIds'        => [],
                'ShowTransactionDetails'    => true,
            ]), 'Checkout and payment method settings', true, 1],

            // ── Auth ──────────────────────────────────────────────────────────
            ['auth', null, 'login_setting', 'Login Settings', json_encode([
                'IsRequiredLogin'                  => false,
                'onlyPhoneLogin'                   => false,
                'showAppleLogin'                   => true,
                'showFacebook'                     => false,
                'showSMSLogin'                     => true,
                'showGoogleLogin'                  => true,
                'showPhoneNumberWhenRegister'      => true,
                'requirePhoneNumberWhenRegister'   => false,
                'isResetPasswordSupported'         => true,
                'smsLoginAsDefault'                => false,
                'facebookAppId'                    => '',
                'facebookLoginProtocolScheme'      => '',
                'appleLoginSetting'                => ['iOSBundleId' => '', 'appleAccountTeamID' => ''],
            ]), 'Login/registration options and third-party auth providers', true, 1],
            ['auth', null, 'phone_number_config', 'Phone Number Config', json_encode([
                'enable'                  => false,
                'countryCodeDefault'      => 'EG',
                'dialCodeDefault'         => '+20',
                'useInternationalFormat'  => true,
                'selectorFlagAsPrefixIcon'=> true,
                'showCountryFlag'         => true,
                'customCountryList'       => [],
                'selectorType'            => 'BOTTOM_SHEET',
            ]), 'Phone number input configuration for registration/login', true, 2],
            ['auth', null, 'gdpr_config', 'GDPR / Privacy Config', json_encode([
                'showPrivacyPolicyFirstTime' => false,
                'showDeleteAccount'          => true,
                'confirmCaptcha'             => 'PERMANENTLY DELETE',
            ]), 'GDPR compliance and account deletion settings', true, 3],

            // ── Onboarding ────────────────────────────────────────────────────
            ['onboarding', null, 'onboarding_config', 'Onboarding Slides', json_encode([
                'enableOnBoarding'       => true,
                'version'                => 2,
                'autoCropImageByDesign'  => true,
                'isOnlyShowOnFirstTime'  => true,
                'showLanguage'           => true,
                'data'                   => [
                    ['title' => 'Welcome to Ramo Store',   'image' => 'assets/images/fogg-delivery-1.png',      'desc' => 'Your one-stop shop for fashion and lifestyle.'],
                    ['title' => 'Browse & Discover',       'image' => 'assets/images/fogg-uploading-1.png',     'desc' => 'Explore hundreds of products across all categories.'],
                    ['title' => "Let's Get Started",       'image' => 'assets/images/fogg-order-completed.png', 'desc' => 'Shop now and get free delivery on orders over 500 EGP!'],
                ],
            ]), 'Onboarding slide content and behavior', true, 1],

            // ── App ───────────────────────────────────────────────────────────
            ['app', null, 'splash_screen', 'Splash Screen', json_encode([
                'enable'                 => true,
                'duration'               => 2000,
                'type'                   => 'ramo-splash',
                'image'                  => 'assets/images/ramologo.png',
                'boxFit'                 => 'contain',
                'backgroundColor'        => '#e85d26',
                'innerBoxBackground'     => '#ffffff',
                'afterInnerBoxBackground'=> '#ffffff',
                'appNameColor'           => '#111111',
            ]), 'Splash screen appearance, animation type and colors', true, 1],
            ['app', null, 'loading_icon', 'Loading Indicator', json_encode([
                'size' => 30.0, 'type' => 'fadingCube',
            ]), 'Loading spinner style (fadingCube, circle, etc.)', true, 2],
            ['app', null, 'app_rating_config', 'App Rating Prompt', json_encode([
                'showOnOpen'     => false,
                'android'        => 'com.ramoui.ramostore',
                'ios'            => '1469772800',
                'minDays'        => 7,
                'minLaunches'    => 10,
                'remindDays'     => 7,
                'remindLaunches' => 10,
            ]), 'When and how to prompt users to rate the app', true, 3],
            ['app', null, 'times_to_view_vendor_poster', 'Vendor Poster Views', '50',
             'Number of times to show vendor poster before hiding', true, 4],
            ['app', null, 'default_drawer', 'Side Drawer Menu', json_encode([
                'logo'       => 'assets/images/logo.png',
                'background' => null,
                'items'      => [
                    ['type' => 'home',       'show' => true],
                    ['type' => 'blog',       'show' => true],
                    ['type' => 'categories', 'show' => true],
                    ['type' => 'cart',       'show' => true],
                    ['type' => 'profile',    'show' => true],
                    ['type' => 'login',      'show' => true],
                    ['type' => 'category',   'show' => true],
                ],
            ]), 'Side drawer logo and menu items', true, 5],
            ['app', null, 'default_settings', 'Settings Screen Items', json_encode([
                'login','biometrics','products','chat','wishlist','notifications',
                'language','currencies','order','rating','privacy','logout','about',
            ]), 'Settings screen visible menu items', true, 6],
            ['app', null, 'social_connect', 'Social Media Links', json_encode([
                ['name' => 'Facebook',  'icon' => 'assets/icons/logins/facebook.png',  'url' => 'https://www.facebook.com/ramoui'],
                ['name' => 'Instagram', 'icon' => 'assets/icons/logins/instagram.png', 'url' => 'https://www.instagram.com/ramoui9/'],
            ]), 'Social media profile links', true, 7],
            ['app', null, 'privacy_policy_url', 'Privacy Policy URL', '"https://policies.google.com/"',   'Privacy policy page URL', true, 8],
            ['app', null, 'support_url',         'Support URL',         '"https://support.ramoui.com/"',   'Customer support page URL', true, 9],
            ['app', null, 'version_check', 'Version Check Config', json_encode([
                'enable' => false, 'iOSAppStoreCountry' => 'EG',
            ]), 'Force-update settings for the mobile app', true, 10],
            ['app', null, 'in_app_update', 'In-App Update (Android)', json_encode([
                'enable' => false, 'typeUpdate' => 'flexible',
            ]), 'Android in-app update type (flexible/immediate)', true, 11],

            // ── Layout — English ──────────────────────────────────────────────
            ['layout', 'en', 'horizon_layout', 'Home Layout (English)', json_encode([
                ['layout' => 'logo', 'showMenu' => true, 'showSearch' => true, 'showLogo' => true, 'showliked' => true],
                ['layout' => 'category', 'type' => 'icon', 'wrap' => false, 'size' => 1.0, 'radius' => 50.0, 'items' => [
                    ['category' => 18, 'label' => 'Phones',   'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg', 'colors' => ['#3CC2BF','#3CC2BF']],
                    ['category' => 23, 'label' => 'Bag',      'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg',   'colors' => ['#3E6AB5','#3E6AB5']],
                    ['category' => 25, 'label' => 'Blazers',  'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp','colors' => ['#53A2CC','#53A2CC']],
                    ['category' => 28, 'label' => 'Shoes',    'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg',        'colors' => ['#53688A','#53688A']],
                    ['category' => 29, 'label' => 'Jeans',    'image' => 'https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564', 'colors' => ['#43506A','#43506A']],
                    ['category' => 30, 'label' => 'Jeans Man','image' => 'https://images.squarespace-cdn.com/content/v1/58add8dd6a49639a87822092/1654105465923-95DJO7H19YLTGOSB4CLO/how-to-style-mens-jeans.jpg?format=750w','colors' => ['#12B58C','#12B58C']],
                ]],
                ['layout' => 'bannerImage', 'isSlider' => true, 'autoPlay' => true, 'showNumber' => false, 'design' => 'default', 'showBackGround' => true, 'radius' => 2.0, 'items' => [
                    ['category' => 29, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp',       'padding' => 7.0],
                    ['product'  => 30, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-04.webp',  'padding' => 7.0],
                    ['category' => 28, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp',  'padding' => 7.0],
                ]],
                ['layout' => 'saleImages', 'category' => 23, 'headerText' => 'Shop by Look', 'maxItemsToShow' => 8, 'productWidth' => 130.0, 'productConfig' => ['imageRatio' => 1.4, 'borderRadius' => 10.0]],
                ['name' => 'Man Collections', 'layout' => 'twoColumn', 'headerText' => 'On Sale Today ⚡️', 'productWidth' => 200, 'maxItemsToShow' => 7, 'category' => 23,
                    'addToCartButtonStyle' => ['style' => 'iconed', 'backgroundColor' => '#E0E0E0', 'textColor' => '#3D3D3D'],
                    'productConfig' => ['borderRadius' => 12.5, 'hMargin' => 10.0, 'vMargin' => 6.0, 'showHeart' => true, 'imageRatio' => 1.5, 'layout' => 'grid']],
                ['layout' => 'bannerImage', 'design' => 'static', 'fit' => 'fitWidth', 'marginLeft' => 0.0, 'marginRight' => 0.0, 'marginTop' => 20.0, 'marginBottom' => 0.0, 'height' => 0.15, 'items' => [
                    ['product' => 30, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/kobunatkhasm.png', 'padding' => 7.0],
                ]],
                ['name' => 'SuperMarket Stars', 'layout' => 'seupermarketstars', 'category' => 21],
                ['name' => 'Brands', 'layout' => 'brands', 'category' => 21],
            ]), 'Homepage block layout configuration for English', true, 1],

            ['layout', 'en', 'tab_bar', 'Tab Bar (English)', json_encode([
                ['layout' => 'home',       'name' => 'Home',       'icon' => 'assets/icons/tabs-icons/home-icon.png'],
                ['layout' => 'categories', 'name' => 'Categories', 'categoryLayout' => 'card', 'icon' => 'assets/icons/tabs-icons/category.png'],
                ['layout' => 'cart',       'name' => 'Cart',       'icon' => 'assets/icons/tabs-icons/carticon.png'],
                ['layout' => 'profile',    'name' => 'Profile',    'icon' => 'assets/icons/tabs-icons/user_icon.png', 'showChat' => true, 'showBackground' => true, 'styleItem' => 'listTile', 'settingStyle' => 'normal'],
            ]), 'Bottom navigation tabs for English', true, 2],

            ['layout', 'en', 'app_setting', 'App Settings (English)', json_encode([
                'MainColor' => '#e85d26', 'FontFamily' => 'Roboto', 'FontHeader' => 'Raleway',
                'ProductListLayout' => 'list', 'StickyHeader' => false, 'ShowChat' => true, 'useMaterial3' => false,
                'AgeRestriction' => ['enable' => false, 'minimumAge' => 21, 'alwaysShowUponOpen' => false],
                'SmartEngagementBanner' => ['popup' => ['enable' => false], 'flexible' => ['enable' => false], 'sticky' => ['enable' => false]],
            ]), 'App appearance and engagement settings for English layout', true, 3],

            ['layout', 'en', 'search_suggestion', 'Search Suggestions (English)', json_encode([
                'Here are some suggestions:', 'Print', 'T-Shirt', 'Jersey', 'Hoodie', 'Jeans', 'Shoes', 'Bag',
            ]), 'Default search suggestions shown in the search bar', true, 4],

            // ── Layout — Arabic ───────────────────────────────────────────────
            ['layout', 'ar', 'horizon_layout', 'Home Layout (Arabic)', json_encode([
                ['layout' => 'logo', 'showMenu' => true, 'showSearch' => true, 'showLogo' => true, 'showliked' => true],
                ['layout' => 'category', 'type' => 'icon', 'wrap' => false, 'size' => 1.0, 'radius' => 50.0, 'items' => [
                    ['category' => 18, 'label' => 'هواتف',  'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg', 'colors' => ['#3CC2BF','#3CC2BF']],
                    ['category' => 23, 'label' => 'حقائب',  'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg',   'colors' => ['#3E6AB5','#3E6AB5']],
                    ['category' => 25, 'label' => 'بليزرات','image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp','colors' => ['#53A2CC','#53A2CC']],
                    ['category' => 28, 'label' => 'أحذية',  'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg',        'colors' => ['#53688A','#53688A']],
                    ['category' => 29, 'label' => 'جينز',   'image' => 'https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564', 'colors' => ['#43506A','#43506A']],
                ]],
                ['layout' => 'bannerImage', 'isSlider' => true, 'autoPlay' => true, 'design' => 'default', 'radius' => 2.0, 'items' => [
                    ['category' => 29, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp',      'padding' => 7.0],
                    ['category' => 28, 'image' => 'https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp', 'padding' => 7.0],
                ]],
                ['layout' => 'saleImages', 'category' => 23, 'headerText' => 'تسوق بالمظهر', 'maxItemsToShow' => 8, 'productWidth' => 130.0, 'productConfig' => ['imageRatio' => 1.4, 'borderRadius' => 10.0]],
                ['name' => 'مجموعات الرجال', 'layout' => 'twoColumn', 'headerText' => 'تخفيضات اليوم ⚡️', 'productWidth' => 200, 'maxItemsToShow' => 7, 'category' => 23,
                    'productConfig' => ['borderRadius' => 12.5, 'showHeart' => true, 'imageRatio' => 1.5, 'layout' => 'grid']],
            ]), 'Homepage block layout configuration for Arabic (RTL)', true, 1],

            ['layout', 'ar', 'tab_bar', 'Tab Bar (Arabic)', json_encode([
                ['layout' => 'home',       'name' => 'الرئيسية', 'icon' => 'assets/icons/tabs-icons/home-icon.png'],
                ['layout' => 'categories', 'name' => 'الفئات',   'categoryLayout' => 'card', 'icon' => 'assets/icons/tabs-icons/category.png'],
                ['layout' => 'cart',       'name' => 'السلة',    'icon' => 'assets/icons/tabs-icons/carticon.png'],
                ['layout' => 'profile',    'name' => 'حسابي',    'icon' => 'assets/icons/tabs-icons/user_icon.png', 'showChat' => true],
            ]), 'Bottom navigation tabs for Arabic', true, 2],

            ['layout', 'ar', 'app_setting', 'App Settings (Arabic)', json_encode([
                'MainColor' => '#e85d26', 'FontFamily' => 'Cairo', 'FontHeader' => 'Cairo',
                'ProductListLayout' => 'list', 'StickyHeader' => false, 'ShowChat' => true, 'useMaterial3' => false,
            ]), 'App appearance settings for Arabic RTL layout', true, 3],

            ['layout', 'ar', 'search_suggestion', 'Search Suggestions (Arabic)', json_encode([
                'اقتراحات البحث:', 'قميص', 'جينز', 'أحذية', 'حقائب', 'فساتين', 'بليزر',
            ]), 'Default search suggestions in Arabic', true, 4],

            // ── Layout — German ───────────────────────────────────────────────
            ['layout', 'de', 'tab_bar', 'Tab Bar (German)', json_encode([
                ['layout' => 'home',       'name' => 'Start',      'icon' => 'assets/icons/tabs-icons/home-icon.png'],
                ['layout' => 'categories', 'name' => 'Kategorien', 'categoryLayout' => 'card', 'icon' => 'assets/icons/tabs-icons/category.png'],
                ['layout' => 'cart',       'name' => 'Warenkorb',  'icon' => 'assets/icons/tabs-icons/carticon.png'],
                ['layout' => 'profile',    'name' => 'Profil',     'icon' => 'assets/icons/tabs-icons/user_icon.png'],
            ]), 'Bottom navigation tabs for German', true, 2],

            ['layout', 'de', 'search_suggestion', 'Search Suggestions (German)', json_encode([
                'Vorschläge:', 'T-Shirt', 'Jeans', 'Schuhe', 'Tasche', 'Jacke',
            ]), 'Default search suggestions in German', true, 4],
        ];

        foreach ($configs as $c) {
            DB::table('app_configs')->updateOrInsert(
                ['config_key' => $c[2], 'lang' => $c[1]],
                [
                    'config_group' => $c[0],
                    'lang'         => $c[1],
                    'config_key'   => $c[2],
                    'label'        => $c[3],
                    'value'        => $c[4],
                    'description'  => $c[5],
                    'is_public'    => $c[6],
                    'sort_order'   => $c[7],
                    'updated_at'   => $now,
                ]
            );
        }
    }
}
