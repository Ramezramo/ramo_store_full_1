<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Vendors ──────────────────────────────────────────────────────────
        $vendors = [
            10 => 'Cairo Fashion Hub',
            11 => 'TechZone Egypt',
            12 => 'Luxury Bags Co',
            13 => 'Shoe Palace Egypt',
            14 => 'Street Style Store',
            15 => 'Nile Electronics',
            16 => 'Desert Rose Fashion',
            17 => 'Delta Denim Co',
             1 => 'Demo Shop',
        ];

        // ── Products ─────────────────────────────────────────────────────────
        $products = [
            // Bags (category 23) – Luxury Bags Co (12)
            [
                'name'        => 'Classic Leather Tote Bag',
                'slug'        => 'classic-leather-tote-bag',
                'vendor_id'   => 12,
                'category'    => 23,
                'description' => 'Premium full-grain leather tote perfect for everyday use. Spacious interior with magnetic closure.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&h=700&fit=crop',
                'total_sales' => 142,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Black',  'price' => 1850, 'stock' => 25],
                    ['Color' => 'Tan',    'price' => 1850, 'stock' => 18],
                    ['Color' => 'Brown',  'price' => 1850, 'stock' => 12],
                ],
            ],
            [
                'name'        => 'Mini Crossbody Bag',
                'slug'        => 'mini-crossbody-bag',
                'vendor_id'   => 12,
                'category'    => 23,
                'description' => 'Compact crossbody bag with adjustable strap. Fits your phone, keys, and essentials.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&h=700&fit=crop',
                'total_sales' => 98,
                'discount'    => 15,
                'variations'  => [
                    ['Color' => 'Beige',  'price' => 750, 'stock' => 40],
                    ['Color' => 'Black',  'price' => 750, 'stock' => 35],
                    ['Color' => 'Red',    'price' => 750, 'stock' => 20],
                ],
            ],
            [
                'name'        => 'Quilted Chain Shoulder Bag',
                'slug'        => 'quilted-chain-shoulder-bag',
                'vendor_id'   => 12,
                'category'    => 23,
                'description' => 'Elegant quilted bag with gold-tone chain strap. A timeless piece for any outfit.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1591561954555-607968c989ab?w=600&h=700&fit=crop',
                'total_sales' => 67,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Black',  'price' => 2200, 'stock' => 15],
                    ['Color' => 'Cream',  'price' => 2200, 'stock' => 10],
                ],
            ],
            [
                'name'        => 'Canvas Backpack',
                'slug'        => 'canvas-backpack',
                'vendor_id'   => 12,
                'category'    => 23,
                'description' => 'Durable canvas backpack with laptop sleeve and multiple pockets. Perfect for work or travel.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=700&fit=crop',
                'total_sales' => 210,
                'discount'    => 20,
                'variations'  => [
                    ['Color' => 'Navy',   'price' => 950, 'stock' => 60],
                    ['Color' => 'Khaki',  'price' => 950, 'stock' => 45],
                    ['Color' => 'Black',  'price' => 950, 'stock' => 55],
                ],
            ],

            // Jeans (category 29) – Delta Denim Co (17)
            [
                'name'        => 'Slim Fit Blue Denim Jeans',
                'slug'        => 'slim-fit-blue-denim-jeans',
                'vendor_id'   => 17,
                'category'    => 29,
                'description' => 'Classic slim-fit jeans in mid-wash blue denim. Stretch fabric for all-day comfort.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=600&h=700&fit=crop',
                'total_sales' => 325,
                'discount'    => 0,
                'variations'  => [
                    ['Size' => 'S',  'price' => 699, 'stock' => 30],
                    ['Size' => 'M',  'price' => 699, 'stock' => 45],
                    ['Size' => 'L',  'price' => 699, 'stock' => 35],
                    ['Size' => 'XL', 'price' => 699, 'stock' => 20],
                ],
            ],
            [
                'name'        => 'Black Skinny Jeans',
                'slug'        => 'black-skinny-jeans',
                'vendor_id'   => 17,
                'category'    => 29,
                'description' => 'Sleek black skinny jeans with a high-rise waist. A wardrobe essential for every season.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&h=700&fit=crop',
                'total_sales' => 280,
                'discount'    => 10,
                'variations'  => [
                    ['Size' => 'XS', 'price' => 749, 'stock' => 25],
                    ['Size' => 'S',  'price' => 749, 'stock' => 40],
                    ['Size' => 'M',  'price' => 749, 'stock' => 50],
                    ['Size' => 'L',  'price' => 749, 'stock' => 30],
                ],
            ],
            [
                'name'        => 'Distressed Boyfriend Jeans',
                'slug'        => 'distressed-boyfriend-jeans',
                'vendor_id'   => 17,
                'category'    => 29,
                'description' => 'Relaxed boyfriend fit with authentic distressed detailing. Effortlessly cool streetwear look.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1580651315530-69c8e0026377?w=600&h=700&fit=crop',
                'total_sales' => 189,
                'discount'    => 25,
                'variations'  => [
                    ['Size' => 'S',  'price' => 820, 'stock' => 22],
                    ['Size' => 'M',  'price' => 820, 'stock' => 38],
                    ['Size' => 'L',  'price' => 820, 'stock' => 28],
                ],
            ],

            // Shirts (category 19) – Cairo Fashion Hub (10)
            [
                'name'        => 'Classic White Oxford Shirt',
                'slug'        => 'classic-white-oxford-shirt',
                'vendor_id'   => 10,
                'category'    => 19,
                'description' => 'Crisp white Oxford shirt crafted from 100% cotton. Timeless style suitable for work or weekend.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&h=700&fit=crop',
                'total_sales' => 175,
                'discount'    => 0,
                'variations'  => [
                    ['Size' => 'S',   'price' => 450, 'stock' => 35],
                    ['Size' => 'M',   'price' => 450, 'stock' => 50],
                    ['Size' => 'L',   'price' => 450, 'stock' => 40],
                    ['Size' => 'XL',  'price' => 450, 'stock' => 25],
                    ['Size' => 'XXL', 'price' => 450, 'stock' => 15],
                ],
            ],
            [
                'name'        => 'Linen Casual Shirt',
                'slug'        => 'linen-casual-shirt',
                'vendor_id'   => 10,
                'category'    => 19,
                'description' => 'Breathable linen shirt perfect for warm weather. Relaxed fit with a button-down collar.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1607962837359-5e7e89f86776?w=600&h=700&fit=crop',
                'total_sales' => 134,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'White', 'Size' => 'M',  'price' => 520, 'stock' => 30],
                    ['Color' => 'Blue',  'Size' => 'M',  'price' => 520, 'stock' => 28],
                    ['Color' => 'White', 'Size' => 'L',  'price' => 520, 'stock' => 25],
                    ['Color' => 'Blue',  'Size' => 'L',  'price' => 520, 'stock' => 22],
                ],
            ],
            [
                'name'        => 'Polo Shirt',
                'slug'        => 'polo-shirt',
                'vendor_id'   => 10,
                'category'    => 19,
                'description' => 'Classic piqué polo shirt with ribbed collar and cuffs. Available in vibrant colors.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=600&h=700&fit=crop',
                'total_sales' => 201,
                'discount'    => 15,
                'variations'  => [
                    ['Color' => 'Navy',  'Size' => 'S',  'price' => 380, 'stock' => 40],
                    ['Color' => 'Navy',  'Size' => 'M',  'price' => 380, 'stock' => 55],
                    ['Color' => 'Red',   'Size' => 'M',  'price' => 380, 'stock' => 35],
                    ['Color' => 'White', 'Size' => 'L',  'price' => 380, 'stock' => 30],
                ],
            ],

            // Blazers (category 25) – Desert Rose Fashion (16)
            [
                'name'        => "Women's Tailored Blazer",
                'slug'        => 'womens-tailored-blazer',
                'vendor_id'   => 16,
                'category'    => 25,
                'description' => 'Sharp tailored blazer with a modern slim fit. Perfect for the office or a night out.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=600&h=700&fit=crop',
                'total_sales' => 88,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Black', 'Size' => 'S',  'price' => 1250, 'stock' => 18],
                    ['Color' => 'Black', 'Size' => 'M',  'price' => 1250, 'stock' => 22],
                    ['Color' => 'Black', 'Size' => 'L',  'price' => 1250, 'stock' => 15],
                    ['Color' => 'Camel', 'Size' => 'M',  'price' => 1250, 'stock' => 12],
                ],
            ],
            [
                'name'        => "Men's Double-Breasted Blazer",
                'slug'        => 'mens-double-breasted-blazer',
                'vendor_id'   => 16,
                'category'    => 25,
                'description' => 'Sophisticated double-breasted blazer in premium wool blend. A statement piece for any wardrobe.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=700&fit=crop',
                'total_sales' => 55,
                'discount'    => 20,
                'variations'  => [
                    ['Color' => 'Navy',  'Size' => 'M',  'price' => 1890, 'stock' => 10],
                    ['Color' => 'Navy',  'Size' => 'L',  'price' => 1890, 'stock' => 12],
                    ['Color' => 'Grey',  'Size' => 'M',  'price' => 1890, 'stock' => 8],
                    ['Color' => 'Grey',  'Size' => 'L',  'price' => 1890, 'stock' => 9],
                ],
            ],

            // Shoes/Jackets (category 28) – Shoe Palace Egypt (13)
            [
                'name'        => "Men's Classic Sneakers",
                'slug'        => 'mens-classic-sneakers',
                'vendor_id'   => 13,
                'category'    => 28,
                'description' => 'Iconic low-top leather sneakers with cushioned sole. Goes with anything, from jeans to chinos.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=700&fit=crop',
                'total_sales' => 412,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'White', 'Size' => '40', 'price' => 1150, 'stock' => 20],
                    ['Color' => 'White', 'Size' => '41', 'price' => 1150, 'stock' => 30],
                    ['Color' => 'White', 'Size' => '42', 'price' => 1150, 'stock' => 28],
                    ['Color' => 'Black', 'Size' => '41', 'price' => 1150, 'stock' => 25],
                    ['Color' => 'Black', 'Size' => '42', 'price' => 1150, 'stock' => 22],
                ],
            ],
            [
                'name'        => "Women's Ankle Boots",
                'slug'        => 'womens-ankle-boots',
                'vendor_id'   => 13,
                'category'    => 28,
                'description' => 'Sleek leather ankle boots with a block heel. Versatile enough for day or night wear.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=600&h=700&fit=crop',
                'total_sales' => 167,
                'discount'    => 10,
                'variations'  => [
                    ['Color' => 'Black', 'Size' => '37', 'price' => 1480, 'stock' => 15],
                    ['Color' => 'Black', 'Size' => '38', 'price' => 1480, 'stock' => 20],
                    ['Color' => 'Black', 'Size' => '39', 'price' => 1480, 'stock' => 18],
                    ['Color' => 'Brown', 'Size' => '38', 'price' => 1480, 'stock' => 12],
                ],
            ],
            [
                'name'        => 'Formal Oxford Shoes',
                'slug'        => 'formal-oxford-shoes',
                'vendor_id'   => 13,
                'category'    => 28,
                'description' => 'Hand-crafted leather Oxford shoes with Goodyear welt construction. Built to last a lifetime.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?w=600&h=700&fit=crop',
                'total_sales' => 93,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Black', 'Size' => '41', 'price' => 2100, 'stock' => 10],
                    ['Color' => 'Black', 'Size' => '42', 'price' => 2100, 'stock' => 12],
                    ['Color' => 'Brown', 'Size' => '41', 'price' => 2100, 'stock' => 8],
                    ['Color' => 'Brown', 'Size' => '42', 'price' => 2100, 'stock' => 10],
                ],
            ],

            // T-Shirts (category 21) – Street Style Store (14)
            [
                'name'        => 'Graphic Print T-Shirt',
                'slug'        => 'graphic-print-tshirt',
                'vendor_id'   => 14,
                'category'    => 21,
                'description' => 'Bold graphic tee printed on 100% organic cotton. Express your style with attitude.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=700&fit=crop',
                'total_sales' => 398,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'White', 'Size' => 'S',  'price' => 280, 'stock' => 60],
                    ['Color' => 'White', 'Size' => 'M',  'price' => 280, 'stock' => 80],
                    ['Color' => 'Black', 'Size' => 'M',  'price' => 280, 'stock' => 75],
                    ['Color' => 'Black', 'Size' => 'L',  'price' => 280, 'stock' => 65],
                    ['Color' => 'Grey',  'Size' => 'L',  'price' => 280, 'stock' => 50],
                ],
            ],
            [
                'name'        => 'Oversized Hoodie',
                'slug'        => 'oversized-hoodie',
                'vendor_id'   => 14,
                'category'    => 21,
                'description' => 'Super-soft heavyweight fleece hoodie with a relaxed oversized fit. Cozy all day long.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=600&h=700&fit=crop',
                'total_sales' => 244,
                'discount'    => 30,
                'variations'  => [
                    ['Color' => 'Sand',  'Size' => 'S',  'price' => 650, 'stock' => 30],
                    ['Color' => 'Sand',  'Size' => 'M',  'price' => 650, 'stock' => 45],
                    ['Color' => 'Black', 'Size' => 'M',  'price' => 650, 'stock' => 50],
                    ['Color' => 'Black', 'Size' => 'L',  'price' => 650, 'stock' => 40],
                    ['Color' => 'Grey',  'Size' => 'XL', 'price' => 650, 'stock' => 25],
                ],
            ],
            [
                'name'        => 'Striped Long-Sleeve Tee',
                'slug'        => 'striped-long-sleeve-tee',
                'vendor_id'   => 14,
                'category'    => 21,
                'description' => 'Classic Breton stripes on a breathable long-sleeve tee. A French-inspired everyday essential.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&h=700&fit=crop',
                'total_sales' => 156,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Navy/White', 'Size' => 'S',  'price' => 320, 'stock' => 35],
                    ['Color' => 'Navy/White', 'Size' => 'M',  'price' => 320, 'stock' => 50],
                    ['Color' => 'Red/White',  'Size' => 'M',  'price' => 320, 'stock' => 40],
                    ['Color' => 'Red/White',  'Size' => 'L',  'price' => 320, 'stock' => 30],
                ],
            ],

            // Jeans Man (category 30) – Delta Denim Co (17)
            [
                'name'        => 'Slim-Fit Chino Trousers',
                'slug'        => 'slim-fit-chino-trousers',
                'vendor_id'   => 17,
                'category'    => 30,
                'description' => 'Smart-casual chinos in stretch cotton twill. Office-ready yet weekend-worthy.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=600&h=700&fit=crop',
                'total_sales' => 188,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Khaki', 'Size' => 'S',  'price' => 550, 'stock' => 30],
                    ['Color' => 'Khaki', 'Size' => 'M',  'price' => 550, 'stock' => 45],
                    ['Color' => 'Khaki', 'Size' => 'L',  'price' => 550, 'stock' => 35],
                    ['Color' => 'Navy',  'Size' => 'M',  'price' => 550, 'stock' => 40],
                    ['Color' => 'Navy',  'Size' => 'L',  'price' => 550, 'stock' => 30],
                ],
            ],

            // Dresses (category 26) – Desert Rose Fashion (16)
            [
                'name'        => 'Floral Wrap Dress',
                'slug'        => 'floral-wrap-dress',
                'vendor_id'   => 16,
                'category'    => 26,
                'description' => 'Feminine wrap dress in a vibrant floral print. V-neckline and adjustable tie waist for a flattering fit.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&h=700&fit=crop',
                'total_sales' => 223,
                'discount'    => 0,
                'variations'  => [
                    ['Color' => 'Multi', 'Size' => 'XS', 'price' => 890, 'stock' => 20],
                    ['Color' => 'Multi', 'Size' => 'S',  'price' => 890, 'stock' => 35],
                    ['Color' => 'Multi', 'Size' => 'M',  'price' => 890, 'stock' => 40],
                    ['Color' => 'Multi', 'Size' => 'L',  'price' => 890, 'stock' => 25],
                ],
            ],
            [
                'name'        => 'Midi Slip Dress',
                'slug'        => 'midi-slip-dress',
                'vendor_id'   => 16,
                'category'    => 26,
                'description' => 'Satin midi slip dress with thin adjustable straps. Effortlessly elegant for any occasion.',
                'thumbnail'   => 'https://images.unsplash.com/photo-1614170153058-7a8e04b58f76?w=600&h=700&fit=crop',
                'total_sales' => 145,
                'discount'    => 20,
                'variations'  => [
                    ['Color' => 'Black', 'Size' => 'XS', 'price' => 1100, 'stock' => 15],
                    ['Color' => 'Black', 'Size' => 'S',  'price' => 1100, 'stock' => 22],
                    ['Color' => 'Black', 'Size' => 'M',  'price' => 1100, 'stock' => 28],
                    ['Color' => 'Nude',  'Size' => 'S',  'price' => 1100, 'stock' => 18],
                    ['Color' => 'Nude',  'Size' => 'M',  'price' => 1100, 'stock' => 20],
                ],
            ],
        ];

        // ── Insert products + variations + category pivot ─────────────────────
        foreach ($products as $data) {
            $discPct = $data['discount'];
            $imagesJson = json_encode([
                'thumbnail'      => $data['thumbnail'],
                'other_images'   => [],
                'natural_images' => [],
            ]);

            $productId = DB::table('products_data')->insertGetId([
                'name'              => $data['name'],
                'slug'              => $data['slug'],
                'search_text'       => strtolower($data['name'] . ' ' . $data['description']),
                'type'              => 'variable',
                'status'            => 'publish',
                'acceptance_status' => 'approved',
                'description'       => $data['description'],
                'short_description' => $data['description'],
                'featured'          => false,
                'purchasable'       => true,
                'manage_stock'      => true,
                'reviews_allowed'   => true,
                'discount_percentage' => (string) $discPct,
                'on_sale'           => $discPct > 0,
                'images'            => $imagesJson,
                'vendor_id'         => $data['vendor_id'],
                'total_sales'       => $data['total_sales'],
                'stock_quantity'    => array_sum(array_column($data['variations'], 'stock')),
                'date_created'      => $now->subDays(rand(1, 180))->format('Y-m-d H:i:s'),
                'date_modified'     => $now->format('Y-m-d H:i:s'),
                'categories'        => json_encode([['id' => $data['category']]]),
            ]);

            // Category pivot
            DB::table('product_category')->insertOrIgnore([
                'product_id'  => $productId,
                'category_id' => $data['category'],
            ]);

            // Variations
            $isFirst = true;
            foreach ($data['variations'] as $v) {
                $basePrice = $v['price'];
                $salePrice = $discPct > 0 ? round($basePrice * (1 - $discPct / 100), 2) : $basePrice;
                $attrs = array_diff_key($v, array_flip(['price', 'stock']));

                DB::table('product_variations')->insert([
                    'product_id'     => $productId,
                    'main_variation' => $isFirst,
                    'attributes'     => json_encode($attrs),
                    'price'          => $discPct > 0 ? $salePrice : $basePrice,
                    'regular_price'  => $basePrice,
                    'sale_price'     => $discPct > 0 ? $salePrice : null,
                    'stock_quantity' => $v['stock'],
                    'images'         => json_encode([]),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $isFirst = false;
            }
        }
    }
}
