<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileAuthNavigationTest extends TestCase
{
    public function test_customer_login_keeps_mobile_navigation_but_otp_verification_suppresses_it(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('class="mobile-auth-screen"', false)
            ->assertSee('id="mob-nav"', false);

        $this->get(route('auth.otp-verify'))
            ->assertOk()
            ->assertSee('class="mobile-auth-screen"', false)
            ->assertSee('body.mobile-auth-screen #mob-nav{display:none !important}', false);
    }

    public function test_storefront_pages_keep_the_standard_mobile_navigation_behavior(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('class="mobile-auth-screen"', false)
            ->assertSee('id="mob-nav"', false);
    }
}
