<?php

namespace Tests\Feature;

use App\Models\ImageGalleryImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageGalleryUploadTest extends TestCase
{
    public function test_authenticated_admin_can_upload_an_image_through_the_real_gallery_route(): void
    {
        Storage::fake('public');
        $recordId = null;
        $adminId = null;

        try {
            $admin = User::create([
                'name' => 'Gallery Upload Test Admin',
                'email' => 'gallery-upload-test-' . uniqid() . '@ramostore.local',
                'password' => 'temporary-test-password',
                'role' => json_encode(['admin']),
            ]);
            $adminId = $admin->id;

            $upload = UploadedFile::fake()->createWithContent(
                'campaign-banner-automated.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            );

            $csrfToken = 'gallery-upload-test-csrf';
            $response = $this->withSession(['_token' => $csrfToken])
                ->actingAs($admin)
                ->post(route('admin.image-gallery.store'), [
                    '_token' => $csrfToken,
                    'images' => [$upload],
                ]);

            $response->assertRedirect(route('admin.image-gallery'));
            $response->assertSessionHasNoErrors();

            $record = ImageGalleryImage::query()
                ->where('original_name', 'campaign-banner-automated.png')
                ->where('uploaded_by', $admin->id)
                ->latest('id')
                ->firstOrFail();
            $recordId = $record->id;

            $this->assertSame('image/png', $record->mime_type);
            $this->assertSame(1, $record->width);
            $this->assertSame(1, $record->height);
            $this->assertSame($admin->id, $record->uploaded_by);
            Storage::disk('public')->assertExists($record->path);
        } finally {
            if ($recordId) {
                ImageGalleryImage::query()->whereKey($recordId)->delete();
            }
            if ($adminId) {
                User::query()->whereKey($adminId)->delete();
            }
        }
    }
}
