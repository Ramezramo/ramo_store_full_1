<?php

namespace Tests\Feature;

use App\Models\HomepageHero;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageHeroTest extends TestCase
{
    use DatabaseTransactions;

    private function signIn(bool $admin): void
    {
        $user = User::create([
            'name' => 'Hero test', 'email' => uniqid('hero-').'@example.test', 'password' => 'temporary-test-password',
        ]);
        if ($admin) {
            $user->role = json_encode(['admin']);
            $user->save();
        }
        $this->actingAs($user);
    }

    public function test_home_renders_only_the_selected_media_and_replacements_remove_old_files(): void
    {
        $this->signIn(true);
        $disk = Storage::fake('public');
        $save = route('admin.homepage-hero.update');
        $this->get(route('admin.homepage-hero'))->assertOk()->assertSee('Save hero');
        $image = ['enabled' => '1', 'media_type' => 'image', 'headline' => 'Hero feature test', 'button_label' => 'Browse', 'button_link' => '/shop'];
        $this->put($save, $image + ['media' => UploadedFile::fake()->image('banner.jpg')])->assertSessionHasNoErrors();
        $hero = HomepageHero::active();
        $oldImage = $hero->media_path;
        $disk->assertExists($oldImage);
        $this->get('/')->assertOk()->assertSee('<ramo-home-hero', false)->assertSee($hero->mediaUrl())->assertSee('Hero feature test')
            ->assertDontSee('<video class="home-hero-media"', false);
        $this->put($save, $image)->assertSessionHasNoErrors();
        $this->assertSame($oldImage, $hero->fresh()->media_path);

        $video = ['enabled' => '1', 'media_type' => 'video'];
        $this->put($save, $video)->assertSessionHasErrors('media');
        $disk->assertExists($oldImage);
        $this->put($save, $video + [
            'media' => UploadedFile::fake()->create('banner.mp4', 100, 'video/mp4'),
            'poster' => UploadedFile::fake()->image('still.png'),
        ])->assertSessionHasNoErrors();
        $hero->refresh();
        $oldPoster = $hero->poster_path;
        $disk->assertMissing($oldImage);
        $this->get('/')->assertOk()->assertSee('<video class="home-hero-media"', false)
            ->assertSee($hero->mediaUrl())->assertSee($hero->posterUrl())->assertDontSee('<img class="home-hero-media"', false);
        $this->put($save, $video + ['remove_poster' => '1'])->assertSessionHasNoErrors();
        $disk->assertMissing($oldPoster);
        $this->put($save, ['enabled' => '0', 'media_type' => 'video'])->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertDontSee('<ramo-home-hero', false);
        $disk->delete($hero->media_path);
        $this->put($save, $video)->assertSessionHasErrors('media');
        $hero->update(['enabled' => true]);
        $this->get('/')->assertOk()->assertDontSee('<ramo-home-hero', false);
    }

    public function test_invalid_uploads_and_links_are_rejected(): void
    {
        $this->signIn(true);
        $save = route('admin.homepage-hero.update');
        foreach ([
            ['image', UploadedFile::fake()->create('text.txt', 1, 'text/plain')],
            ['image', UploadedFile::fake()->image('big.jpg')->size(5121)],
            ['video', UploadedFile::fake()->image('picture.png')],
            ['video', UploadedFile::fake()->create('big.webm', 20481, 'video/webm')],
        ] as [$type, $file]) {
            $this->put($save, ['enabled' => '1', 'media_type' => $type, 'media' => $file])->assertSessionHasErrors('media');
        }
        $this->put($save, ['enabled' => '0', 'media_type' => 'video', 'button_link' => 'javascript:void(0)',
            'poster' => UploadedFile::fake()->image('big.jpg')->size(5121)])->assertSessionHasErrors(['poster', 'button_link']);
    }

    public function test_shoppers_cannot_manage_the_banner(): void
    {
        $this->signIn(false);
        $this->get(route('admin.homepage-hero'))->assertForbidden();
        $this->put(route('admin.homepage-hero.update'))->assertForbidden();
    }
}
