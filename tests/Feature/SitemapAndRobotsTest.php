<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapAndRobotsTest extends TestCase
{
    public function test_sitemap_is_xml_and_excludes_private_routes(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertDontSee('/checkout')
            ->assertDontSee('/account/')
            ->assertDontSee('/admin/');
    }

    public function test_robots_file_declares_private_areas_and_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /checkout', $robots);
        $this->assertStringContainsString('Disallow: /account/', $robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Sitemap: /sitemap.xml', $robots);
    }

    public function test_transactional_and_authentication_pages_are_not_indexable(): void
    {
        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('name="robots" content="index,follow"', false);
    }
}
