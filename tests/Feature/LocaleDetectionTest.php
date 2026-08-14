<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The browser integration sends the CSRF token. These feature tests
        // isolate the locale-transition behavior behind that web protection.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_first_visit_from_an_arab_country_uses_arabic(): void
    {
        $this->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'trusted_edge');
    }

    public function test_first_visit_from_a_non_arab_country_uses_english(): void
    {
        $this->withHeader('CF-IPCountry', 'GB')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'trusted_edge');
    }

    public function test_missing_edge_country_header_marks_the_session_for_a_client_country_lookup(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'fallback_pending');
    }

    public function test_pending_client_country_lookup_switches_egypt_to_arabic(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'fallback_pending'])
            ->postJson(route('language.auto-country'), ['country' => 'EG'])
            ->assertOk()
            ->assertJson(['updated' => true])
            ->assertSessionHas('locale', 'ar')
            ->assertSessionHas('locale_source', 'client_ip');
    }

    public function test_client_country_lookup_preserves_english_for_non_arab_countries(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'fallback_pending'])
            ->postJson(route('language.auto-country'), ['country' => 'GB'])
            ->assertOk()
            ->assertJson(['updated' => false])
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'client_ip');
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

    public function test_client_country_lookup_cannot_overwrite_a_manual_locale_choice(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'manual'])
            ->postJson(route('language.auto-country'), ['country' => 'EG'])
            ->assertOk()
            ->assertJson(['updated' => false])
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'manual');
    }

    public function test_client_country_lookup_rejects_an_invalid_country_code(): void
    {
        $this->withSession(['locale' => 'en', 'locale_source' => 'fallback_pending'])
            ->postJson(route('language.auto-country'), ['country' => 'EG; DROP TABLE users'])
            ->assertUnprocessable()
            ->assertSessionHas('locale', 'en')
            ->assertSessionHas('locale_source', 'fallback_pending');
    }
}
