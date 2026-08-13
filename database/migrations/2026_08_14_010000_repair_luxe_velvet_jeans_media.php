<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PRODUCT_ID = 22;
    private const PRODUCT_NAME = 'Luxe Velvet Jeans — Olive';
    private const MEDIA_PATH = 'products/luxe-velvet-jeans-olive.jpg';

    /**
     * Restore a customer-visible product whose imported media paths no longer exist.
     *
     * The committed image is stored on Laravel's managed public disk and is resolved
     * by AppConstants for product, gallery, cart, search, and category rendering.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products_data')) {
            return;
        }

        DB::table('products_data')
            ->where('id', self::PRODUCT_ID)
            ->where('name', self::PRODUCT_NAME)
            ->update([
                'images' => json_encode([
                    'thumbnail' => self::MEDIA_PATH,
                    'other_images' => [self::MEDIA_PATH],
                    'natural_images' => [],
                ], JSON_UNESCAPED_SLASHES),
            ]);
    }

    /**
     * Restore the imported paths if this deployment is deliberately rolled back.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products_data')) {
            return;
        }

        DB::table('products_data')
            ->where('id', self::PRODUCT_ID)
            ->where('name', self::PRODUCT_NAME)
            ->update([
                'images' => json_encode([
                    'thumbnail' => 'products/thumbnails/jWxe2g5AHxyoQJgVxo8FknSBZq8ohIJy3W1G29QP.jpg',
                    'other_images' => ['products/other_images/nVBVb7y51SbUZuaEfKPlJVsYmzUAbbUdQrGlOQRF.jpg'],
                    'natural_images' => ['products/natural_images/ILzxE3ijlarqJIbl6BJGyWZXcgTun45P5ydqzWMh.jpg'],
                ], JSON_UNESCAPED_SLASHES),
            ]);
    }
};
