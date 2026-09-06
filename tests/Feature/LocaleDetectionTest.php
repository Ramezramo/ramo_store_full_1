<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_first_visit_from_an_arab_country_uses_arabic(): void
    {
        $this->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'trusted_edge')
            ->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_arabic_accept_language_is_used_before_first_html_when_country_header_is_missing(): void
    {
        $this->withHeader('Accept-Language', 'ar-EG,ar;q=0.9,en;q=0.8')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'accept_language')
            ->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_first_visit_from_a_non_arab_country_uses_english(): void
    {
        $this->withHeader('CF-IPCountry', 'GB')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'trusted_edge')
            ->assertSee('<html lang="en" dir="ltr">', false);
    }

    public function test_public_server_ip_is_resolved_before_the_first_html_render(): void
    {
        Cache::forget('server_locale_country.'.hash('sha256', '198.51.100.20'));
        Http::fake([
            'https://ipwho.is/198.51.100.20' => Http::response([
                'success' => true,
                'country_code' => 'EG',
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'server_ip')
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertDontSee('locale-pending', false)
            ->assertDontSee('Loading RamoStore', false);
    }

    public function test_trusted_forwarded_egyptian_ip_is_used_before_the_first_html_render(): void
    {
        Cache::forget('server_locale_country.'.hash('sha256', '41.32.0.1'));
        Http::fake([
            'https://ipwho.is/41.32.0.1' => Http::response([
                'success' => true,
                'country_code' => 'EG',
            ]),
        ]);

        $this->withHeader('X-Forwarded-For', '41.32.0.1')
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'server_ip')
            ->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_missing_or_private_server_ip_uses_english_without_a_client_locale_flow(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'accept_language')
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertDontSee('locale-pending', false)
            ->assertDontSee('api.country.is', false)
            ->assertDontSee('ipwho.is', false)
            ->assertDontSee('/language/auto-country', false)
            ->assertDontSee('/language/auto-locale', false);
    }

    public function test_an_old_automatic_english_session_is_repaired_by_an_arab_country_header(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'fallback_pending'])
            ->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'trusted_edge')
            ->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_a_manual_locale_choice_is_not_overwritten_by_country_detection(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'manual'])
            ->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'manual');
    }

    public function test_client_locale_endpoints_are_not_exposed_anymore(): void
    {
        $this->postJson('/language/auto-country', ['country' => 'EG'])->assertStatus(405);
        $this->postJson('/language/auto-locale', ['locale' => 'ar'])->assertStatus(405);
    }
}
