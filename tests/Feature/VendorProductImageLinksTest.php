<?php

namespace Tests\Feature;

use App\Models\VendorUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorProductImageLinksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_links_are_rendered_and_stay_with_their_variation_after_editing(): void
    {
        Storage::fake('public');
        Http::fake();
        $vendor = $this->signInSeller();
        $cover = 'https://images.example.com/cover.webp?size=600';
        $gallery = 'https://images.example.com/gallery.jpg';
        $lifestyle = 'https://images.example.com/lifestyle.jpg';
        $colorImage = 'https://images.example.com/blue.jpg';
        $optionImage = 'https://images.example.com/capacity.jpg';
        $sizeImage = 'https://images.example.com/size-m.jpg';
        $payload = [
            '_token' => csrf_token(),
            'name' => 'Linked media test',
            'status' => 'publish',
            'thumbnail_url' => $cover,
            'other_image_urls' => $gallery."\r\n\r\n".$gallery,
            'natural_image_urls' => $lifestyle,
            'has_variations' => true,
            'colors' => [
                2 => ['type' => 'color', 'name' => 'blue', 'color_code' => '#0000ff',
                    'sizes' => ['M', 'L'], 'price_map' => ['M' => 100, 'L' => 110], 'stock' => ['M' => 2, 'L' => 2]],
                7 => ['type' => 'attribute', 'attribute_name' => 'Capacity', 'name' => 'blue',
                    'sizes' => ['Default'], 'price_map' => ['Default' => 120], 'stock' => ['Default' => 2]],
            ],
            'variation_image_urls' => [2 => $colorImage, 7 => $optionImage],
        ];

        $this->post(route('vendor.products.store'), $payload)->assertRedirect(route('vendor.products'));
        $product = DB::table('products_data')->where('vendor_id', $vendor->id)->first();
        $this->assertSame(['thumbnail' => $cover, 'other_images' => [$gallery], 'natural_images' => [$lifestyle]], json_decode($product->images, true));
        [$medium, $large, $capacity] = DB::table('product_variations')->where('product_id', $product->id)->orderBy('id')->get()->all();
        $this->assertSame([$colorImage], json_decode($medium->images, true));
        $this->assertSame([$colorImage], json_decode($large->images, true));
        $this->assertSame([$optionImage], json_decode($capacity->images, true));

        $this->post(route('vendor.products.update-section', $product->id), [
            '_token' => csrf_token(), '_section' => 'var_images', 'variation_id' => $medium->id, 'new_image_urls' => $sizeImage,
        ])->assertRedirect(route('vendor.products.show', $product->id));
        $this->get(route('vendor.products.edit', $product->id))->assertOk()->assertSee($cover);
        $this->get(route('vendor.products.show', $product->id))->assertOk()->assertSee($sizeImage);

        unset($payload['variation_image_urls'], $payload['natural_image_urls'], $payload['other_image_urls']);
        $payload['thumbnail_url'] = 'https://images.example.com/replacement.webp';
        $payload['delete_other_images'] = [$gallery];
        $payload['colors'][2]['name'] = 'Navy';
        $payload['colors'][2]['retain_existing_images'] = '1';
        $payload['colors'][2]['existing_variations'] = ['M' => $medium->id, 'L' => $large->id];
        $payload['colors'][2]['existing_images'] = [$colorImage, $sizeImage];
        $this->post(route('vendor.products.update', $product->id), $payload)->assertRedirect(route('vendor.products.edit', $product->id));

        $updated = DB::table('products_data')->find($product->id);
        $this->assertSame(['thumbnail' => $payload['thumbnail_url'], 'other_images' => [], 'natural_images' => [$lifestyle]], json_decode($updated->images, true));
        [$medium, $large, $capacity] = DB::table('product_variations')->where('product_id', $product->id)->orderBy('id')->get()->all();
        $this->assertSame([$colorImage, $sizeImage], json_decode($medium->images, true));
        $this->assertSame([$colorImage], json_decode($large->images, true));
        $this->assertSame([$optionImage], json_decode($capacity->images, true));

        DB::table('products_data')->where('id', $product->id)->update(['acceptance_status' => 'approved']);
        $this->get(route('product', $product->id))->assertOk()
            ->assertSee($payload['thumbnail_url'])->assertSee($lifestyle)->assertSee($sizeImage)->assertSee($optionImage)
            ->assertDontSee($gallery);
        $this->assertSame([], Storage::disk('public')->allFiles());
        Http::assertNothingSent();
    }

    public function test_uploads_can_be_mixed_with_links_and_invalid_links_are_rejected(): void
    {
        Storage::fake('public');
        Http::fake();
        $vendor = $this->signInSeller();
        $payload = [
            '_token' => csrf_token(), 'name' => 'Mixed media test', 'status' => 'publish', 'regular_price' => 100, 'stock_quantity' => 2,
            'thumbnail' => UploadedFile::fake()->image('cover.jpg'), 'other_image_urls' => 'https://images.example.com/photo.jpg',
        ];
        $this->post(route('vendor.products.store'), $payload + [
            'thumbnail_url' => 'https://images.example.com/second-cover.jpg',
            'natural_image_urls' => 'http://images.example.com/insecure.jpg',
        ])->assertSessionHasErrors(['thumbnail_url', 'natural_image_urls']);
        $this->assertDatabaseMissing('products_data', ['vendor_id' => $vendor->id, 'name' => $payload['name']]);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->post(route('vendor.products.store'), $payload)->assertRedirect(route('vendor.products'));
        $product = DB::table('products_data')->where('vendor_id', $vendor->id)->first();
        $images = json_decode($product->images, true);
        $this->assertSame([$payload['other_image_urls']], $images['other_images']);
        Storage::disk('public')->assertExists($images['thumbnail']);
        Http::assertNothingSent();
    }

    private function signInSeller(): VendorUser
    {
        $vendor = new VendorUser;
        $vendor->forceFill([
            'first_name' => 'Media', 'last_name' => 'Test', 'phone' => '01000000000',
            'email' => uniqid('media-vendor-').'@example.test', 'password' => 'temporary-test-password',
            'shop_name' => 'Image link test shop', 'shop_address' => 'Test address', 'status' => 'approved',
            'auth_token' => uniqid('media-test-token-'), 'holder_name' => 'Test Holder', 'bank_name' => 'Test Bank', 'branch' => 'Test Branch',
        ])->save();
        $this->actingAs($vendor, 'vendor_web')->withSession(['_token' => 'vendor-image-link-test-token']);

        return $vendor;
    }
}
