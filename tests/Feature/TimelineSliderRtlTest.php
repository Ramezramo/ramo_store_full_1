<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimelineSliderRtlTest extends TestCase
{
    public function test_arabic_homepage_uses_rtl_content_with_direction_independent_slider_track(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('.tl-slides{display:flex;direction:ltr;transition:transform .5s cubic-bezier(.4,0,.2,1)}', false)
            ->assertSee('.tl-slide{min-width:100%;position:relative;direction:rtl}', false)
            ->assertSee('.tl-banner-slider{max-height:none !important;touch-action:pan-y;user-select:none;-webkit-user-select:none}', false)
            ->assertSee('@media(max-width:640px)', false)
            ->assertSee('.tl-banner-slider .tl-arrow { display:none !important; }', false)
            ->assertSee("slider.addEventListener('touchstart'", false)
            ->assertSee("if (deltaX < 0) slideNext(id);", false)
            ->assertSee("else slidePrev(id);", false);
    }
}
