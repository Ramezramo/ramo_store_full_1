<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RamoStoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ────────────────────────────────────────────
        DB::table('users')->insertOrIgnore([
            'name'       => 'Admin',
            'email'      => 'adminramoui@gmail.com',
            'password'   => Hash::make('Admin@12345'),
            'role'       => 'admin',
            'phone'      => '',
            'nicename'   => '',
            'registered' => '',
            'firstname'  => '',
            'lastname'   => '',
            'description'=> '',
            'capabilities'=> '',
            'shipping'   => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Brands ────────────────────────────────────────────────
        DB::table('brands')->insertOrIgnore([
            ['id' => 1, 'name' => 'Apple'],
            ['id' => 2, 'name' => 'Samsung'],
            ['id' => 3, 'name' => 'Microsoft'],
            ['id' => 4, 'name' => 'Sony'],
            ['id' => 5, 'name' => 'Intel'],
        ]);

        // ── Categories ────────────────────────────────────────────
        DB::table('categories2')->insertOrIgnore([
            ['id'=>18, 'name'=>'Men',          'slug'=>'men',            'parent'=>0,  'menu_order'=>6],
            ['id'=>19, 'name'=>'Shirts',        'slug'=>'shirts',         'parent'=>18, 'menu_order'=>11],
            ['id'=>20, 'name'=>'Shoes',         'slug'=>'shoes-men',      'parent'=>28, 'menu_order'=>10],
            ['id'=>21, 'name'=>'T-Shirts',      'slug'=>'t-shirts',       'parent'=>18, 'menu_order'=>12],
            ['id'=>22, 'name'=>'Women',         'slug'=>'women',          'parent'=>24, 'menu_order'=>13],
            ['id'=>23, 'name'=>'Bags-ramo',     'slug'=>'bags',           'parent'=>0,  'menu_order'=>4],
            ['id'=>24, 'name'=>'Bag-ramo',      'slug'=>'bags-men-ramo',  'parent'=>18, 'menu_order'=>7],
            ['id'=>25, 'name'=>'Blazers-ramo',  'slug'=>'blazers',        'parent'=>22, 'menu_order'=>14],
            ['id'=>26, 'name'=>'Dresses',       'slug'=>'dresses',        'parent'=>22, 'menu_order'=>15],
            ['id'=>28, 'name'=>'Jackets',       'slug'=>'jackets-men',    'parent'=>30, 'menu_order'=>9],
            ['id'=>29, 'name'=>'Jeans',         'slug'=>'jeans',          'parent'=>22, 'menu_order'=>17],
            ['id'=>30, 'name'=>'Jeans Man',     'slug'=>'jeans-men',      'parent'=>18, 'menu_order'=>8],
            ['id'=>208,'name'=>'Clothing',      'slug'=>'clothing',       'parent'=>0,  'menu_order'=>3],
            ['id'=>311,'name'=>'mobile-phones', 'slug'=>'Mobile-phones',  'parent'=>2,  'menu_order'=>2],
            ['id'=>314,'name'=>'Uncategorized', 'slug'=>'uncategorized-ar','parent'=>0, 'menu_order'=>0],
        ]);

        // ── Homepage Layout (EN) ──────────────────────────────────
        $horizonEn = '[{"layout":"logo","showMenu":true,"showSearch":true,"showLogo":true,"showliked":true},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"Phones","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":23,"label":"Bag","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":25,"label":"Blazers","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp","colors":["#53A2CC","#53A2CC"]},{"category":28,"label":"Shoes","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg","colors":["#53688A","#53688A"]},{"category":29,"label":"Jeans","image":"https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]},{"category":30,"label":"Jeans Man","image":"https://images.squarespace-cdn.com/content/v1/58add8dd6a49639a87822092/1654105465923-95DJO7H19YLTGOSB4CLO/how-to-style-mens-jeans.jpg?format=750w","colors":["#12B58C","#12B58C"]}]},{"layout":"bannerImage","isSlider":true,"autoPlay":true,"showNumber":false,"design":"default","showBackGround":true,"radius":2,"items":[{"category":29,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp","padding":7},{"product":30,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-04.webp","padding":7},{"category":28,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp","padding":7}]},{"layout":"saleImages","category":23,"headerText":"Shop by Look","maxItemsToShow":8,"productWidth":130,"productConfig":{"imageRatio":1.4,"borderRadius":10}},{"name":"Man Collections","layout":"twoColumn","headerText":"On Sale Today \u26a1\ufe0f","productWidth":200,"maxItemsToShow":7,"category":23,"addToCartButtonStyle":{"style":"iconed","backgroundColor":"#E0E0E0","textColor":"#3D3D3D"},"productConfig":{"borderRadius":12.5,"hMargin":10,"vMargin":6,"showHeart":true,"imageRatio":1.5,"layout":"grid"}},{"layout":"bannerImage","design":"static","fit":"fitWidth","marginLeft":0,"marginRight":0,"marginTop":20,"marginBottom":0,"height":0.15,"items":[{"product":30,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/kobunatkhasm.png","padding":7}]},{"name":"SuperMarket Stars","layout":"seupermarketstars","category":21},{"name":"Brands","layout":"brands","category":21}]';

        // ── Homepage Layout (AR) ──────────────────────────────────
        $horizonAr = '[{"layout":"logo","showMenu":true,"showSearch":true,"showLogo":true,"showliked":true},{"layout":"category","type":"icon","wrap":false,"size":1,"radius":50,"items":[{"category":18,"label":"\u0647\u0648\u0627\u062a\u0641","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/phones_image.jpg","colors":["#3CC2BF","#3CC2BF"]},{"category":23,"label":"\u062d\u0642\u0627\u0626\u0628","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/bag_image_.jpg","colors":["#3E6AB5","#3E6AB5"]},{"category":25,"label":"\u0628\u0644\u064a\u0632\u0631\u0627\u062a","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/women_blazers.webp","colors":["#53A2CC","#53A2CC"]},{"category":28,"label":"\u0623\u062d\u0630\u064a\u0629","image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/sheos.jpg","colors":["#53688A","#53688A"]},{"category":29,"label":"\u062c\u064a\u0646\u0632","image":"https://us.dockers.com/cdn/shop/files/Monte-Mid-Rise-Jeans-Relaxed-Fit-alt5-A64720005_360x450_crop_center.png?v=1741351564","colors":["#43506A","#43506A"]}]},{"layout":"bannerImage","isSlider":true,"autoPlay":true,"design":"default","radius":2,"items":[{"category":29,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/HP-Banner.webp","padding":7},{"category":28,"image":"https://raw.githubusercontent.com/Ramezramo/projectxmedia1/refs/heads/main/Campaign-LP-07.webp","padding":7}]},{"layout":"saleImages","category":23,"headerText":"\u062a\u0633\u0648\u0642 \u0628\u0627\u0644\u0645\u0638\u0647\u0631","maxItemsToShow":8,"productWidth":130,"productConfig":{"imageRatio":1.4,"borderRadius":10}},{"name":"\u0645\u062c\u0645\u0648\u0639\u0627\u062a \u0627\u0644\u0631\u062c\u0627\u0644","layout":"twoColumn","headerText":"\u062a\u062e\u0641\u064a\u0636\u0627\u062a \u0627\u0644\u064a\u0648\u0645 \u26a1\ufe0f","productWidth":200,"maxItemsToShow":7,"category":23,"productConfig":{"borderRadius":12.5,"showHeart":true,"imageRatio":1.5,"layout":"grid"}}]';

        DB::table('app_configs')->insertOrIgnore([
            [
                'config_key'   => 'horizon_layout',
                'config_group' => 'layout',
                'lang'         => 'en',
                'value'        => $horizonEn,
                'label'        => 'Homepage Layout (EN)',
                'is_public'    => true,
                'sort_order'   => 0,
                'updated_at'   => now(),
            ],
            [
                'config_key'   => 'horizon_layout',
                'config_group' => 'layout',
                'lang'         => 'ar',
                'value'        => $horizonAr,
                'label'        => 'Homepage Layout (AR)',
                'is_public'    => true,
                'sort_order'   => 0,
                'updated_at'   => now(),
            ],
        ]);

        // ── Version Config ────────────────────────────────────────
        DB::table('version_config')->insertOrIgnore([
            ['id' => 1, 'supported_ver_from' => '1.0.0', 'supported_ver_to' => '4.0.0'],
        ]);

        // ── Sample Coupons ────────────────────────────────────────
        DB::table('coupons')->insertOrIgnore([
            [
                'code'          => 'SAVER20',
                'amount'        => 20.00,
                'status'        => 'publish',
                'discount_type' => 'percent',
                'minimum_amount'=> 50.00,
                'maximum_amount'=> 0,
            ],
            [
                'code'          => 'SAVERR20',
                'amount'        => 20.00,
                'status'        => 'publish',
                'discount_type' => 'percent',
                'minimum_amount'=> 50.00,
                'maximum_amount'=> 0,
            ],
        ]);
    }
}
