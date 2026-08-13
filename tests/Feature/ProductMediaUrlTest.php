<?php

namespace Tests\Feature;

use App\Constants\AppConstants;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductMediaUrlTest extends TestCase
{
    public function test_local_media_url_is_returned_only_when_the_public_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/present.jpg', 'image-content');

        $this->assertSame('/storage/products/present.jpg', AppConstants::imageUrl('products/present.jpg'));
        $this->assertNull(AppConstants::imageUrl('products/missing.jpg'));
    }

    public function test_missing_primary_media_falls_back_to_an_available_secondary_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/secondary.jpg', 'image-content');

        $this->assertSame('/storage/products/secondary.jpg', AppConstants::productThumbnailUrl([
            'thumbnail' => 'products/missing.jpg',
            'other_images' => ['products/secondary.jpg'],
        ]));
    }

    public function test_object_storage_media_uses_the_configured_cdn_base_when_present(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('products/cdn-image.webp', 'image-content');
        Config::set('filesystems.default', 's3');
        Config::set('app.image_base_url', 'https://cdn.example.com/media');

        $this->assertSame('https://cdn.example.com/media/products/cdn-image.webp', AppConstants::imageUrl('products/cdn-image.webp'));
    }

    public function test_object_storage_media_uses_the_disk_url_without_a_cdn_override(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('products/object-image.webp', 'image-content');
        Config::set('filesystems.default', 's3');
        Config::set('app.image_base_url', null);

        $this->assertSame(Storage::disk('s3')->url('products/object-image.webp'), AppConstants::imageUrl('products/object-image.webp'));
    }

    public function test_legacy_local_media_url_is_hidden_when_its_storage_file_is_missing(): void
    {
        Storage::fake('public');

        $this->assertNull(AppConstants::imageUrl('http://127.0.0.1:5000/storage/products/missing.jpg'));
        $this->assertSame('https://cdn.example.com/products/image.jpg', AppConstants::imageUrl('https://cdn.example.com/products/image.jpg'));
    }
}
