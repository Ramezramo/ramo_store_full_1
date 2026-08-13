<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    public function test_first_visit_from_an_arab_country_uses_arabic(): void
    {
        $this->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'ar');
    }

    public function test_first_visit_from_a_non_arab_country_uses_english(): void
    {
        $this->withHeader('CF-IPCountry', 'GB')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en');
    }

    public function test_a_manual_locale_choice_is_not_overwritten_by_country_detection(): void
    {
        $this->withSession(['locale' => 'en'])
            ->withHeader('CF-IPCountry', 'EG')
            ->get('/')
            ->assertOk()
            ->assertSessionHas('locale', 'en');
    }
}
