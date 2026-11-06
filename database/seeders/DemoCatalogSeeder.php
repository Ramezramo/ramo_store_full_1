<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    private const SKU_PREFIX = 'DEMO-';

    public function run(): void
    {
        $disk = Storage::disk('public');
        $sources = $this->downloadImages($disk);
        $categoryIds = $this->upsertCategories($sources);
        $now = now();

        foreach ($this->products() as $position => $product) {
            $categoryId = $categoryIds[$product['category']];
            $slug = 'demo-' . Str::slug($product['name']);
            $imagePath = $sources[$product['image']]['path'];
            $discount = (int) ($product['discount'] ?? 0);
            $options = $product['options'] ?? [];
            $variations = $this->variationRows($options, (float) $product['price'], $discount, $position);
            $totalStock = array_sum(array_column($variations, 'stock_quantity'));
            $prices = array_column($variations, 'regular_price');

            $images = json_encode([
                'thumbnail' => $imagePath,
                'other_images' => [$imagePath],
                'natural_images' => [],
            ], JSON_UNESCAPED_SLASHES);

            $productId = DB::table('products_data')->where('slug', $slug)->value('id');
            $values = [
                'name' => $product['name'],
                'slug' => $slug,
                'search_text' => strtolower($product['name'] . ' ' . $product['description'] . ' ' . $product['category']),
                'type' => count($variations) > 1 ? 'variable' : 'simple',
                'status' => 'publish',
                'featured' => ($position % 7) === 0,
                'catalog_visibility' => 'visible',
                'description' => $product['description'],
                'short_description' => $product['description'],
                'sku' => self::SKU_PREFIX . strtoupper(Str::slug($product['name'], '-')),
                'on_sale' => $discount > 0,
                'purchasable' => true,
                'total_sales' => 20 + (($position * 37) % 460),
                'manage_stock' => true,
                'stock_quantity' => $totalStock,
                'stock_status' => $totalStock > 0 ? 'instock' : 'outofstock',
                'sold_individually' => false,
                'shipping_required' => true,
                'shipping_taxable' => true,
                'reviews_allowed' => true,
                'categories' => json_encode([['id' => $categoryId, 'name' => $product['category']]]),
                'tags' => json_encode(['demo catalog', $product['category']]),
                'images' => $images,
                'attributes' => json_encode(array_keys($options)),
                'has_options' => count($options) > 0,
                'has_variations' => count($variations) > 1,
                'lang' => 'en',
                'min_price' => (string) min($prices),
                'max_price' => (string) max($prices),
                'acceptance_status' => 'approved',
                'discount_percentage' => (string) $discount,
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 10,
                'product_type' => 'physical',
                'unit' => $product['unit'] ?? 'piece',
                'button_mode' => 'both',
                'date_created' => $now->copy()->subMinutes($position * 13)->format('Y-m-d H:i:s'),
                'date_modified' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now,
            ];

            if ($productId) {
                DB::table('products_data')->where('id', $productId)->update($values);
            } else {
                $values['created_at'] = $now;
                $productId = DB::table('products_data')->insertGetId($values);
            }

            DB::table('product_category')->where('product_id', $productId)->delete();
            DB::table('product_category')->insert([
                'product_id' => $productId,
                'category_id' => $categoryId,
            ]);

            DB::table('product_variations')->where('product_id', $productId)->delete();
            foreach ($variations as $index => $variation) {
                DB::table('product_variations')->insert(array_merge($variation, [
                    'product_id' => $productId,
                    'main_variation' => $index === 0,
                    'images' => json_encode([$imagePath], JSON_UNESCAPED_SLASHES),
                    'stock_status' => $variation['stock_quantity'] > 0 ? 'instock' : 'outofstock',
                    'status' => 'publish',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        foreach ($categoryIds as $categoryId) {
            $count = DB::table('product_category')->where('category_id', $categoryId)->count();
            DB::table('categories2')->where('id', $categoryId)->update(['count' => $count]);
        }

        app(\App\Services\ShopPriceRangeService::class)->refreshGlobal();
    }

    private function upsertCategories(array $sources): array
    {
        $definitions = [
            'Computer Components' => ['computer-components', 'PC parts, storage, cooling, power and upgrade components.', 'gpu'],
            'Laptops & Computers' => ['laptops-computers', 'Portable computers, desktops and displays.', 'laptop'],
            'Gaming Accessories' => ['gaming-accessories', 'Keyboards, mice, headsets, controllers and streaming gear.', 'keyboard'],
            'Phones & Tablets' => ['phones-tablets', 'Smartphones, tablets, watches and mobile charging.', 'phone'],
            'Audio & Cameras' => ['audio-cameras', 'Personal audio, speakers, cameras and creator equipment.', 'camera'],
            'Home & Kitchen' => ['home-kitchen', 'Useful appliances and cookware for everyday living.', 'coffee'],
            'Furniture & Decor' => ['furniture-decor', 'Furniture, lighting and decorative home accents.', 'sofa'],
            'Sports & Outdoors' => ['sports-outdoors', 'Training, cycling, football and outdoor essentials.', 'dumbbells'],
            'Beauty & Personal Care' => ['beauty-personal-care', 'Fragrance, skincare, makeup and grooming tools.', 'perfume'],
            'Books & Toys' => ['books-toys', 'Books, building sets, games and family entertainment.', 'books'],
        ];

        $ids = [];
        foreach ($definitions as $name => [$slug, $description, $imageKey]) {
            $id = DB::table('categories2')->where('slug', $slug)->value('id');
            $values = [
                'name' => $name,
                'slug' => $slug,
                'parent' => 0,
                'description' => $description,
                'display' => 'default',
                'image' => $sources[$imageKey]['path'],
                'menu_order' => 0,
                'count' => 0,
                'has_children' => false,
                '_links' => '{}',
            ];
            if ($id) {
                DB::table('categories2')->where('id', $id)->update($values);
            } else {
                $id = DB::table('categories2')->insertGetId($values);
            }
            $ids[$name] = $id;
        }

        return $ids;
    }

    private function variationRows(array $options, float $basePrice, int $discount, int $seed): array
    {
        $combinations = [[]];
        foreach ($options as $name => $values) {
            $expanded = [];
            foreach ($combinations as $combination) {
                foreach ($values as $value) {
                    $expanded[] = array_merge($combination, [$name => $value]);
                }
            }
            $combinations = $expanded;
        }
        if ($combinations === [[]]) $combinations = [[]];

        return array_map(function (array $attributes, int $index) use ($basePrice, $discount, $seed) {
            $regular = round($basePrice + ($index * max(10, $basePrice * 0.035)), 2);
            $sale = $discount > 0 ? round($regular * (1 - $discount / 100), 2) : null;
            return [
                'attributes' => json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'price' => $regular,
                'regular_price' => $regular,
                'sale_price' => $sale,
                'stock_quantity' => 6 + (($seed * 11 + $index * 7) % 35),
            ];
        }, $combinations, array_keys($combinations));
    }

    private function downloadImages($disk): array
    {
        $remote = [
            'gpu' => 'https://images.pexels.com/photos/6704953/pexels-photo-6704953.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'pc' => 'https://images.pexels.com/photos/15109652/pexels-photo-15109652.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'laptop' => 'https://images.pexels.com/photos/18105/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1200',
            'monitor' => 'https://images.pexels.com/photos/1714208/pexels-photo-1714208.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'keyboard' => 'https://images.pexels.com/photos/2115257/pexels-photo-2115257.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'mouse' => 'https://images.pexels.com/photos/3945655/pexels-photo-3945655.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'headset' => 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'microphone' => 'https://images.pexels.com/photos/164829/pexels-photo-164829.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'controller' => 'https://images.pexels.com/photos/442576/pexels-photo-442576.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'phone' => 'https://images.pexels.com/photos/607812/pexels-photo-607812.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'tablet' => 'https://images.pexels.com/photos/5082566/pexels-photo-5082566.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'watch' => 'https://images.pexels.com/photos/267394/pexels-photo-267394.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'charger' => 'https://images.pexels.com/photos/4219861/pexels-photo-4219861.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'speaker' => 'https://images.pexels.com/photos/157534/pexels-photo-157534.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'camera' => 'https://images.pexels.com/photos/90946/pexels-photo-90946.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'drone' => 'https://images.pexels.com/photos/724921/pexels-photo-724921.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'coffee' => 'https://images.pexels.com/photos/414630/pexels-photo-414630.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'blender' => 'https://images.pexels.com/photos/4226805/pexels-photo-4226805.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'cookware' => 'https://images.pexels.com/photos/699608/pexels-photo-699608.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'vacuum' => 'https://images.pexels.com/photos/4107278/pexels-photo-4107278.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'chair' => 'https://images.pexels.com/photos/534151/pexels-photo-534151.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'sofa' => 'https://images.pexels.com/photos/276528/pexels-photo-276528.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'lamp' => 'https://images.pexels.com/photos/112811/pexels-photo-112811.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'plant' => 'https://images.pexels.com/photos/1084199/pexels-photo-1084199.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'dumbbells' => 'https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'yoga' => 'https://images.pexels.com/photos/3823039/pexels-photo-3823039.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'bike' => 'https://images.pexels.com/photos/100582/pexels-photo-100582.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'football' => 'https://images.pexels.com/photos/274506/pexels-photo-274506.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'backpack' => 'https://images.pexels.com/photos/2905238/pexels-photo-2905238.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'perfume' => 'https://images.pexels.com/photos/965989/pexels-photo-965989.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'skincare' => 'https://images.pexels.com/photos/4041392/pexels-photo-4041392.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'makeup' => 'https://images.pexels.com/photos/2113855/pexels-photo-2113855.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'haircare' => 'https://images.pexels.com/photos/3993314/pexels-photo-3993314.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'grooming' => 'https://images.pexels.com/photos/3998414/pexels-photo-3998414.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'books' => 'https://images.pexels.com/photos/159711/books-bookstore-book-reading-159711.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'toys' => 'https://images.pexels.com/photos/163036/mario-luigi-yoschi-figures-funny-163036.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'toycar' => 'https://images.pexels.com/photos/163696/toy-car-miniature-automobile-163696.jpeg?auto=compress&cs=tinysrgb&w=1200',
            'boardgame' => 'https://images.pexels.com/photos/4691555/pexels-photo-4691555.jpeg?auto=compress&cs=tinysrgb&w=1200',
        ];

        $downloaded = [];
        foreach ($remote as $key => $url) {
            $path = 'products/demo-catalog/' . $key . '.jpg';
            $fallbackUrl = 'https://picsum.photos/seed/ramo-' . $key . '/1000/1000';
            if (!$disk->exists($path) || $disk->size($path) < 5000) {
                $response = Http::timeout(30)->retry(2, 400, null, false)->get($url);
                if (!$response->successful() || strlen($response->body()) < 5000) {
                    $response = Http::timeout(30)->get($fallbackUrl);
                }
                if (!$response->successful()) {
                    throw new \RuntimeException('Unable to download demo catalog image: ' . $key);
                }
                $disk->put($path, $response->body());
            }
            $downloaded[$key] = [
                'path' => $path,
                'preferred_source' => $url,
                'fallback_source' => $fallbackUrl,
            ];
        }

        $disk->put('products/demo-catalog/sources.json', json_encode($downloaded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $downloaded;
    }

    private function products(): array
    {
        return [
            ['name'=>'Nebula RTX Graphics Card','category'=>'Computer Components','image'=>'gpu','price'=>28999,'discount'=>8,'description'=>'Triple-fan graphics card for high-refresh gaming and creative workloads.','options'=>['Color'=>['Black','Silver'],'Size'=>['8GB','12GB']]],
            ['name'=>'Vector Radeon Graphics Card','category'=>'Computer Components','image'=>'gpu','price'=>22499,'discount'=>5,'description'=>'Efficient graphics card with quiet cooling and strong 1440p performance.','options'=>['Color'=>['Black','Red'],'Size'=>['12GB','16GB']]],
            ['name'=>'Atlas 8-Core Processor','category'=>'Computer Components','image'=>'pc','price'=>11999,'description'=>'Eight-core desktop processor designed for gaming, streaming and daily productivity.','options'=>['Size'=>['Standard','Unlocked']]],
            ['name'=>'Pulse Gaming Motherboard','category'=>'Computer Components','image'=>'pc','price'=>8499,'discount'=>10,'description'=>'Feature-rich motherboard with fast networking, multiple M.2 slots and RGB headers.','options'=>['Size'=>['ATX','Micro-ATX']]],
            ['name'=>'Titan DDR5 Memory Kit','category'=>'Computer Components','image'=>'pc','price'=>3999,'description'=>'Low-latency DDR5 memory kit with aluminum heat spreaders.','options'=>['Color'=>['Black','White'],'Size'=>['16GB','32GB']]],
            ['name'=>'Swift NVMe SSD','category'=>'Computer Components','image'=>'pc','price'=>2499,'discount'=>12,'description'=>'Fast NVMe solid-state drive for quick boot, game and application loading.','options'=>['Size'=>['500GB','1TB','2TB']]],
            ['name'=>'Frost RGB CPU Cooler','category'=>'Computer Components','image'=>'pc','price'=>2199,'description'=>'Quiet tower cooler with addressable lighting and broad socket support.','options'=>['Color'=>['Black','White'],'Size'=>['120mm','140mm']]],
            ['name'=>'CoreMax Modular Power Supply','category'=>'Computer Components','image'=>'pc','price'=>4599,'discount'=>7,'description'=>'Fully modular high-efficiency power supply with a silent fan profile.','options'=>['Size'=>['650W','750W','850W']]],

            ['name'=>'ProBook 14 Laptop','category'=>'Laptops & Computers','image'=>'laptop','price'=>32999,'discount'=>6,'description'=>'Slim everyday laptop with a bright 14-inch display and all-day battery.','options'=>['Color'=>['Silver','Midnight'],'Size'=>['8GB / 512GB','16GB / 1TB']]],
            ['name'=>'Creator 16 Laptop','category'=>'Laptops & Computers','image'=>'laptop','price'=>58999,'description'=>'Large-screen performance laptop for design, editing and development.','options'=>['Color'=>['Graphite'],'Size'=>['16GB / 1TB','32GB / 1TB']]],
            ['name'=>'Mini Office PC','category'=>'Laptops & Computers','image'=>'pc','price'=>17999,'discount'=>9,'description'=>'Compact desktop computer with quiet cooling and flexible connectivity.','options'=>['Color'=>['Black'],'Size'=>['8GB / 256GB','16GB / 512GB']]],
            ['name'=>'Curved Gaming Monitor','category'=>'Laptops & Computers','image'=>'monitor','price'=>12499,'description'=>'Immersive curved monitor with fast refresh and adaptive synchronization.','options'=>['Color'=>['Black'],'Size'=>['27 inch','32 inch']]],
            ['name'=>'4K Creator Monitor','category'=>'Laptops & Computers','image'=>'monitor','price'=>15499,'discount'=>5,'description'=>'Color-accurate 4K display with USB-C connectivity for creative work.','options'=>['Color'=>['Black','Silver'],'Size'=>['27 inch','32 inch']]],

            ['name'=>'Mechanical Gaming Keyboard','category'=>'Gaming Accessories','image'=>'keyboard','price'=>2299,'discount'=>15,'description'=>'Hot-swappable mechanical keyboard with per-key lighting and media controls.','options'=>['Color'=>['Black','White'],'Size'=>['TKL','Full Size']]],
            ['name'=>'Precision Wireless Mouse','category'=>'Gaming Accessories','image'=>'mouse','price'=>1399,'description'=>'Lightweight wireless mouse with adjustable sensitivity and long battery life.','options'=>['Color'=>['Black','White'],'Size'=>['Compact','Standard']]],
            ['name'=>'Surround Gaming Headset','category'=>'Gaming Accessories','image'=>'headset','price'=>1899,'discount'=>10,'description'=>'Comfortable over-ear headset with clear voice chat and spatial sound.','options'=>['Color'=>['Black','White'],'Size'=>['Wired','Wireless']]],
            ['name'=>'Pro USB Microphone','category'=>'Gaming Accessories','image'=>'microphone','price'=>2799,'description'=>'USB condenser microphone for streaming, meetings and podcast recording.','options'=>['Color'=>['Black','Silver'],'Size'=>['Desk Stand','Boom Arm Kit']]],
            ['name'=>'Wireless Game Controller','category'=>'Gaming Accessories','image'=>'controller','price'=>1699,'discount'=>8,'description'=>'Low-latency controller with responsive triggers and textured grips.','options'=>['Color'=>['Black','Blue','White'],'Size'=>['Standard']]],

            ['name'=>'Nova 5G Smartphone','category'=>'Phones & Tablets','image'=>'phone','price'=>21999,'discount'=>7,'description'=>'Fast 5G smartphone with a bright OLED display and versatile cameras.','options'=>['Color'=>['Black','Blue'],'Size'=>['128GB','256GB']]],
            ['name'=>'Compact Android Phone','category'=>'Phones & Tablets','image'=>'phone','price'=>10999,'description'=>'Pocket-friendly smartphone with reliable battery life and dual SIM support.','options'=>['Color'=>['Black','Mint'],'Size'=>['64GB','128GB']]],
            ['name'=>'AirTab 11 Tablet','category'=>'Phones & Tablets','image'=>'tablet','price'=>18499,'discount'=>5,'description'=>'Thin 11-inch tablet for work, entertainment and sketching.','options'=>['Color'=>['Gray','Silver'],'Size'=>['128GB Wi-Fi','256GB 5G']]],
            ['name'=>'Active Smartwatch','category'=>'Phones & Tablets','image'=>'watch','price'=>4999,'description'=>'Fitness-focused smartwatch with heart-rate tracking and notifications.','options'=>['Color'=>['Black','Rose','Silver'],'Size'=>['41mm','45mm']]],
            ['name'=>'65W GaN Fast Charger','category'=>'Phones & Tablets','image'=>'charger','price'=>1299,'discount'=>12,'description'=>'Compact multi-port fast charger for phones, tablets and laptops.','options'=>['Color'=>['Black','White'],'Size'=>['2 Port','3 Port']]],

            ['name'=>'Studio ANC Headphones','category'=>'Audio & Cameras','image'=>'headset','price'=>4599,'discount'=>10,'description'=>'Wireless headphones with active noise cancellation and detailed sound.','options'=>['Color'=>['Black','Cream','Blue'],'Size'=>['Standard']]],
            ['name'=>'Portable Bluetooth Speaker','category'=>'Audio & Cameras','image'=>'speaker','price'=>2199,'description'=>'Water-resistant portable speaker with rich sound and strong battery life.','options'=>['Color'=>['Black','Orange','Teal'],'Size'=>['Mini','Max']]],
            ['name'=>'Mirrorless Travel Camera','category'=>'Audio & Cameras','image'=>'camera','price'=>34999,'discount'=>6,'description'=>'Compact interchangeable-lens camera for sharp travel photos and video.','options'=>['Color'=>['Black','Silver'],'Size'=>['Body Only','Kit Lens']]],
            ['name'=>'4K Action Camera','category'=>'Audio & Cameras','image'=>'camera','price'=>6999,'description'=>'Rugged action camera with stabilization and waterproof housing.','options'=>['Color'=>['Black'],'Size'=>['Camera','Adventure Kit']]],
            ['name'=>'Pocket Camera Drone','category'=>'Audio & Cameras','image'=>'drone','price'=>18999,'discount'=>5,'description'=>'Foldable drone with stabilized video and intelligent flight modes.','options'=>['Color'=>['Gray','White'],'Size'=>['Standard','Fly More Kit']]],

            ['name'=>'Barista Coffee Maker','category'=>'Home & Kitchen','image'=>'coffee','price'=>6499,'discount'=>9,'description'=>'Countertop coffee maker with programmable brewing and milk frothing.','options'=>['Color'=>['Black','Silver'],'Size'=>['1.2L','1.8L']]],
            ['name'=>'PowerBlend Blender','category'=>'Home & Kitchen','image'=>'blender','price'=>2799,'description'=>'High-speed blender for smoothies, soups and everyday preparation.','options'=>['Color'=>['Black','White'],'Size'=>['1.5L','2L']]],
            ['name'=>'Digital Air Fryer','category'=>'Home & Kitchen','image'=>'cookware','price'=>4499,'discount'=>11,'description'=>'Digital air fryer with preset programs and easy-clean basket.','options'=>['Color'=>['Black','Cream'],'Size'=>['4L','7L']]],
            ['name'=>'Ceramic Cookware Set','category'=>'Home & Kitchen','image'=>'cookware','price'=>3899,'description'=>'Non-stick ceramic cookware set for efficient everyday meals.','options'=>['Color'=>['Black','Sage'],'Size'=>['7 Piece','11 Piece']]],
            ['name'=>'Cordless Stick Vacuum','category'=>'Home & Kitchen','image'=>'vacuum','price'=>7299,'discount'=>8,'description'=>'Light cordless vacuum with multiple tools and washable filtration.','options'=>['Color'=>['Graphite','White'],'Size'=>['Standard','Pet Kit']]],

            ['name'=>'Nordic Lounge Chair','category'=>'Furniture & Decor','image'=>'chair','price'=>5999,'description'=>'Comfortable lounge chair with a clean Scandinavian silhouette.','options'=>['Color'=>['Beige','Gray','Green'],'Size'=>['Standard']]],
            ['name'=>'Modular Living Room Sofa','category'=>'Furniture & Decor','image'=>'sofa','price'=>18999,'discount'=>5,'description'=>'Flexible modular sofa with deep cushions and durable fabric.','options'=>['Color'=>['Beige','Charcoal'],'Size'=>['2 Seat','3 Seat']]],
            ['name'=>'Minimal Desk Lamp','category'=>'Furniture & Decor','image'=>'lamp','price'=>1199,'description'=>'Adjustable LED desk lamp with touch dimming and warm light modes.','options'=>['Color'=>['Black','White','Gold'],'Size'=>['Desk','Floor']]],
            ['name'=>'Indoor Plant Stand','category'=>'Furniture & Decor','image'=>'plant','price'=>899,'discount'=>10,'description'=>'Modern plant stand that adds height and texture to indoor greenery.','options'=>['Color'=>['Black','Natural'],'Size'=>['Small','Large']]],

            ['name'=>'Adjustable Dumbbell Set','category'=>'Sports & Outdoors','image'=>'dumbbells','price'=>7999,'discount'=>8,'description'=>'Space-saving adjustable dumbbells for strength training at home.','options'=>['Color'=>['Black'],'Size'=>['20kg','32kg']]],
            ['name'=>'Premium Yoga Mat','category'=>'Sports & Outdoors','image'=>'yoga','price'=>999,'description'=>'Cushioned non-slip yoga mat with carrying strap.','options'=>['Color'=>['Purple','Teal','Black'],'Size'=>['6mm','10mm']]],
            ['name'=>'City Commuter Bicycle','category'=>'Sports & Outdoors','image'=>'bike','price'=>13499,'discount'=>6,'description'=>'Comfortable city bicycle with practical gearing and reliable brakes.','options'=>['Color'=>['Black','Blue'],'Size'=>['Medium','Large']]],
            ['name'=>'Match Football','category'=>'Sports & Outdoors','image'=>'football','price'=>749,'description'=>'Durable match-style football with consistent flight and touch.','options'=>['Color'=>['White','Orange'],'Size'=>['4','5']]],
            ['name'=>'Trail Hiking Backpack','category'=>'Sports & Outdoors','image'=>'backpack','price'=>1899,'discount'=>12,'description'=>'Supportive hiking backpack with ventilated back panel and rain cover.','options'=>['Color'=>['Black','Olive','Red'],'Size'=>['30L','50L']]],

            ['name'=>'Velvet Eau de Parfum','category'=>'Beauty & Personal Care','image'=>'perfume','price'=>2199,'discount'=>10,'description'=>'Warm modern fragrance with floral, amber and soft woody notes.','options'=>['Size'=>['50ml','100ml']]],
            ['name'=>'Vitamin C Skin Serum','category'=>'Beauty & Personal Care','image'=>'skincare','price'=>849,'description'=>'Lightweight brightening serum with vitamin C and hydrating ingredients.','options'=>['Size'=>['30ml','60ml']]],
            ['name'=>'Professional Makeup Palette','category'=>'Beauty & Personal Care','image'=>'makeup','price'=>1299,'discount'=>15,'description'=>'Versatile makeup palette with blendable everyday and evening shades.','options'=>['Color'=>['Warm','Cool'],'Size'=>['12 Shade','24 Shade']]],
            ['name'=>'Ionic Hair Dryer','category'=>'Beauty & Personal Care','image'=>'haircare','price'=>1799,'description'=>'Fast-drying hair dryer with ionic care and multiple heat settings.','options'=>['Color'=>['Black','Rose'],'Size'=>['1800W','2200W']]],
            ['name'=>'Electric Grooming Kit','category'=>'Beauty & Personal Care','image'=>'grooming','price'=>1499,'discount'=>8,'description'=>'Rechargeable grooming kit with precision attachments and travel case.','options'=>['Color'=>['Black','Silver'],'Size'=>['6 Piece','10 Piece']]],

            ['name'=>'Design Thinking Book Set','category'=>'Books & Toys','image'=>'books','price'=>1199,'description'=>'Curated set of practical books for design, business and creative problem solving.','options'=>['Size'=>['3 Books','5 Books']]],
            ['name'=>'STEM Building Blocks','category'=>'Books & Toys','image'=>'toys','price'=>999,'discount'=>10,'description'=>'Creative building set that introduces mechanisms, structures and problem solving.','options'=>['Color'=>['Classic','Pastel'],'Size'=>['300 Pieces','600 Pieces']]],
            ['name'=>'Remote Control Rally Car','category'=>'Books & Toys','image'=>'toycar','price'=>1399,'description'=>'Responsive remote-control car with rechargeable battery and rugged tires.','options'=>['Color'=>['Red','Blue'],'Size'=>['1:24','1:18']]],
            ['name'=>'Family Strategy Board Game','category'=>'Books & Toys','image'=>'boardgame','price'=>749,'discount'=>5,'description'=>'Easy-to-learn strategy game designed for lively family game nights.','options'=>['Size'=>['Classic','Deluxe']]],
        ];
    }
}
