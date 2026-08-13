<?php

namespace Tests\Feature;

use App\Constants\AppConstants;
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

    public function test_legacy_local_media_url_is_hidden_when_its_storage_file_is_missing(): void
    {
        Storage::fake('public');

        $this->assertNull(AppConstants::imageUrl('http://127.0.0.1:5000/storage/products/missing.jpg'));
        $this->assertSame('https://cdn.example.com/products/image.jpg', AppConstants::imageUrl('https://cdn.example.com/products/image.jpg'));
    }
}
