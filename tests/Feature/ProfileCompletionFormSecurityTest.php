<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ProfileCompletionFormSecurityTest extends TestCase
{
    public function test_profile_completion_form_uses_a_same_origin_relative_post_action(): void
    {
        Config::set('app.url', 'http://127.0.0.1:5000');

        $this->withSession([
            'locale' => 'ar',
            'otp_temp_token' => 'temporary-profile-token',
            'otp_temp_phone' => '+201234567890',
        ])->get(route('auth.complete-profile'))
            ->assertOk()
            ->assertSee('action="/auth/complete-profile"', false)
            ->assertDontSee('action="http://127.0.0.1:5000/auth/complete-profile"', false);
    }
}
