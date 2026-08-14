<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileAuthNavigationTest extends TestCase
{
    public function test_customer_authentication_routes_suppress_the_mobile_navigation(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('class="mobile-auth-screen"', false)
            ->assertSee('body.mobile-auth-screen #mob-nav{display:none !important}', false);

        $this->get(route('auth.otp-verify'))
            ->assertOk()
            ->assertSee('class="mobile-auth-screen"', false);
    }

    public function test_storefront_pages_keep_the_standard_mobile_navigation_behavior(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('class="mobile-auth-screen"', false)
            ->assertSee('id="mob-nav"', false);
    }
}
