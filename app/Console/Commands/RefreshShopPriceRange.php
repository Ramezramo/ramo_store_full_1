<?php

namespace App\Console\Commands;

use App\Services\ShopPriceRangeService;
use Illuminate\Console\Command;

class RefreshShopPriceRange extends Command
{
    protected $signature = 'shop:refresh-price-range';

    protected $description = 'Recalculate the cached storefront product price bounds';

    public function handle(ShopPriceRangeService $priceRanges): int
    {
        $priceRanges->refreshGlobal();
        $this->info('Storefront price range refreshed.');

        return self::SUCCESS;
    }
}
