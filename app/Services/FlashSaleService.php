<?php

namespace App\Services;

class FlashSaleService
{
    public static function getActive(string $lang = 'en'): ?object
    {
        $sections = TimelineLayoutService::sections($lang);
        $nowMs    = (int) (microtime(true) * 1000);

        foreach ($sections as $sec) {
            if (($sec['layout'] ?? '') !== 'flash') continue;
            if ($sec['hidden'] ?? false) continue;

            $endTime = (int) ($sec['endTime'] ?? 0);
            if ($endTime > 0 && $endTime <= $nowMs) continue;

            return (object) [
                'discount'         => (float) ($sec['discount'] ?? 20),
                'applyTo'          => $sec['applyTo'] ?? 'all',
                'targetCategories' => array_map('intval', (array) ($sec['targetCategories'] ?? [])),
                'targetProductIds' => array_map('intval', (array) ($sec['targetProductIds'] ?? [])),
                'endTime'          => $endTime,
            ];
        }

        return null;
    }
}
