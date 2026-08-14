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
            $admin = new User([
                'name' => 'Gallery Upload Test Admin',
                'email' => 'gallery-upload-test-' . uniqid() . '@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            $admin->role = json_encode(['admin']);
            $admin->save();
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

    public function test_gallery_upload_validation_names_the_file_that_cannot_be_uploaded(): void
    {
        $admin = new User([
            'name' => 'Gallery Validation Test Admin',
            'email' => 'gallery-validation-test-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
        ]);
        $admin->role = json_encode(['admin']);
        $admin->save();

        try {
            $csrfToken = 'gallery-validation-test-csrf';
            $response = $this->withSession(['_token' => $csrfToken])
                ->actingAs($admin)
                ->from(route('admin.image-gallery'))
                ->post(route('admin.image-gallery.store'), [
                    '_token' => $csrfToken,
                    'images' => [UploadedFile::fake()->create('campaign-not-an-image.pdf', 100, 'application/pdf')],
                ]);

            $response->assertRedirect(route('admin.image-gallery'));
            $response->assertSessionHasErrors([
                'images.0' => 'campaign-not-an-image.pdf is not a supported image. Upload a JPG, PNG, WEBP, GIF, or AVIF file.',
            ]);
        } finally {
            $admin->delete();
        }
    }
}
