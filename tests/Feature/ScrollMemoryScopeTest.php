<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScrollMemoryScopeTest extends TestCase
{
    public function test_scroll_memory_is_scoped_to_home_and_shop_paths(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString("location.pathname === '/' || location.pathname === '/shop'", $layout);
        $this->assertStringContainsString('if (!scrollMemoryEnabled) return;', $layout);
        $this->assertStringContainsString("const KEY_PREFIX = 'ramo_scroll::';", $layout);
    }
}
