<?php

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class StorefrontPaginationTest extends TestCase
{
    public function test_pagination_marks_the_current_page_and_preserves_sort_on_first_middle_and_last_pages(): void
    {
        foreach ([1 => [1, 2], 5 => [4, 5, 6], 10 => [9, 10]] as $page => $expectedPages) {
            $xpath = $this->renderPagination($page);
            $nav = $xpath->query('//nav')->item(0);
            $this->assertInstanceOf(DOMElement::class, $nav);
            $this->assertContains('pagination-wrap', explode(' ', $nav->getAttribute('class')));
            $this->assertNotSame('', $nav->getAttribute('aria-label'));
            $controls = $xpath->query('./a|./span', $nav);
            $this->assertLessThanOrEqual(5, $controls->length);

            $numbers = [];
            foreach ($controls as $control) {
                if (ctype_digit(trim($control->textContent))) {
                    $numbers[] = (int) trim($control->textContent);
                }
            }
            $this->assertSame($expectedPages, $numbers);
            $current = $xpath->query('./span[@aria-current="page"]', $nav);
            $this->assertSame(1, $current->length);
            $this->assertSame((string) $page, trim($current->item(0)->textContent));
            $this->assertContains('active-page', explode(' ', $current->item(0)->getAttribute('class')));

            foreach ([$controls->item(0), $controls->item($controls->length - 1)] as $control) {
                $this->assertNotSame('', $control->getAttribute('aria-label'));
            }
            $previous = $controls->item(0);
            $next = $controls->item($controls->length - 1);
            $this->assertSame($page === 1 ? 'span' : 'a', $previous->tagName);
            $this->assertSame($page === 10 ? 'span' : 'a', $next->tagName);
            if ($page === 1) $this->assertSame('true', $previous->getAttribute('aria-disabled'));
            if ($page === 10) $this->assertSame('true', $next->getAttribute('aria-disabled'));

            foreach ($xpath->query('./a', $nav) as $link) {
                parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
                $this->assertSame('price_asc', $query['sort']);
                $this->assertGreaterThanOrEqual(1, (int) $query['page']);
                $this->assertLessThanOrEqual(10, (int) $query['page']);
            }
        }
    }

    public function test_pagination_accessibility_labels_follow_the_selected_language(): void
    {
        $english = $this->renderPagination(5, 'en');
        $arabic = $this->renderPagination(5, 'ar');
        foreach (['//nav', '//nav/*[1]', '//nav/*[last()]'] as $selector) {
            $englishLabel = $english->query($selector)->item(0)->getAttribute('aria-label');
            $arabicLabel = $arabic->query($selector)->item(0)->getAttribute('aria-label');
            $this->assertNotSame($englishLabel, $arabicLabel);
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $arabicLabel);
        }
    }

    public function test_a_single_page_does_not_render_page_controls(): void
    {
        $xpath = $this->renderPagination(1, 'en', 12);
        $this->assertSame(0, $xpath->query('//nav')->length);
    }

    private function renderPagination(int $page, string $locale = 'en', int $total = 120): DOMXPath
    {
        session(['locale' => $locale]);
        $paginator = new LengthAwarePaginator(range(1, 12), $total, 12, $page, [
            'path' => 'http://localhost/vendor/455',
        ]);
        $paginator->appends(['sort' => 'price_asc']);
        $html = (string) $paginator->links('web.partials.pagination');
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($dom);
    }
}
