<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Supplies the admin sidebar with per-section record counts.
 *
 * Each entry exposes:
 *   - total:   full number of records in that section
 *   - pending: number of records needing admin attention (0 when none)
 *
 * Results are cached briefly so that navigating between admin pages does not
 * re-run the aggregate queries on every request.
 */
class AdminSidebarComposer
{
    /** Seconds to cache the computed counts. */
    private const CACHE_TTL = 60;

    public const CACHE_KEY = 'admin.sidebar.counts';

    public function compose(View $view): void
    {
        $view->with('sidebarCounts', Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => $this->counts()
        ));
    }

    /**
     * @return array<string, array{total:int, pending:int}>
     */
    private function counts(): array
    {
        return [
            'users'    => $this->entry('users'),
            'orders'   => $this->entry('orders', fn ($q) => $q->whereIn('status', ['pending', 'processing'])),
            'vendors'  => $this->entry('vendor_users', fn ($q) => $q->where('status', 'pending')),
            'products' => $this->entry('products_data', fn ($q) => $q->where('acceptance_status', 'pending')),
            'devices'  => $this->entry('device_access_tokens'),
            'coupons'  => $this->entry('coupons'),
            'refunds'  => $this->entry('refund_requests', fn ($q) => $q->where('status', 'pending')),
            'reviews'  => $this->entry('product_reviews', fn ($q) => $q->where('approved', false)),
            'requests' => $this->entry('category_brand_requests', fn ($q) => $q->where('status', 'pending')),
        ];
    }

    /**
     * Build a single count entry, tolerating tables/columns that may not exist
     * yet so a missing table can never break the whole admin layout.
     */
    private function entry(string $table, ?callable $pendingFilter = null): array
    {
        try {
            if (! Schema::hasTable($table)) {
                return ['total' => 0, 'pending' => 0];
            }

            $total = (int) DB::table($table)->count();

            $pending = 0;
            if ($pendingFilter) {
                $pending = (int) $pendingFilter(DB::table($table))->count();
            }

            return ['total' => $total, 'pending' => $pending];
        } catch (\Throwable $e) {
            report($e);

            return ['total' => 0, 'pending' => 0];
        }
    }
}
