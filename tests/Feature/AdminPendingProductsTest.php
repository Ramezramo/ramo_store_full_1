<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPendingProductsTest extends TestCase
{
    public function test_pending_section_filters_products_and_retains_review_actions(): void
    {
        DB::beginTransaction();
        try {
            $suffix = uniqid();
            $admin = User::create(['name' => 'Admin', 'email' => "pending-admin-{$suffix}@example.test", 'password' => 'password']);
            DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
            $admin->refresh();
            $base = [
                'search_text' => "Review product {$suffix}", 'status' => 'draft',
                'minimum_order_qty' => 1, 'created_at' => now(), 'updated_at' => now(),
            ];
            $pendingId = DB::table('products_data')->insertGetId($base + [
                'name' => "Review product {$suffix} pending", 'slug' => "pending-{$suffix}", 'acceptance_status' => 'pending',
            ]);
            DB::table('products_data')->insert($base + [
                'name' => "Review product {$suffix} approved", 'slug' => "approved-{$suffix}", 'acceptance_status' => 'approved',
            ]);

            $this->actingAs($admin)->get(route('admin.products', ['acceptance' => 'pending', 'search' => $suffix]))
                ->assertOk()->assertSee('Pending products')->assertSee("Review product {$suffix} pending")
                ->assertDontSee("Review product {$suffix} approved")
                ->assertSee(route('admin.products.reject', $pendingId))
                ->assertSee('form="bulk-products-form"', false);
            $this->patch(route('admin.products.reject', $pendingId))->assertRedirect();
            $this->get(route('admin.products', ['acceptance' => 'pending', 'search' => $suffix]))
                ->assertOk()->assertSee('No pending products found.');
        } finally {
            DB::rollBack();
        }
    }
}
