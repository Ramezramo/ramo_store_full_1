<?php

namespace Tests\Feature;

use Tests\TestCase;

class PolicyPageTest extends TestCase
{
    public function test_all_required_policy_routes_are_publicly_reachable(): void
    {
        foreach ([
            'policy.privacy' => 'Privacy Policy',
            'policy.terms' => 'Terms & Conditions',
            'policy.shipping' => 'Shipping & Delivery Policy',
            'policy.returns' => 'Returns & Exchanges Policy',
            'policy.contact' => 'Contact Us',
            'policy.payment' => 'Payment Information',
        ] as $routeName => $heading) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee($heading)
                ->assertSee('name="robots" content="noindex,follow"', false);
        }
    }

    public function test_policy_pages_render_in_arabic_right_to_left_mode(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('policy.privacy'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('سياسة الخصوصية')
            ->assertSee('كمّل تسوق');
    }
}
