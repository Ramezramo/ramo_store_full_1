<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderNotificationInboxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_inbox_orders_both_notification_types_by_the_actual_time(): void
    {
        $customer = User::factory()->create();
        $order = DB::table('orders')->insertGetId(['customer_id' => $customer->id]);
        $placed = DB::table('order_notifications')->where('order_id', $order)->where('event', 'placed');
        $this->assertEqualsWithDelta(now()->timestamp, Carbon::parse($placed->value('created_at'))->timestamp, 10);
        $placed->update(['created_at' => '2026-11-20 10:03:00+02:00']);

        $application = DB::table('vendor_applications')->insertGetId(['user_id' => $customer->id]);
        DB::table('vendor_application_notifications')->insert([
            'user_id' => $customer->id,
            'application_id' => $application,
            'status' => 'approved',
            'created_at' => '2026-11-20 08:02:00',
        ]);

        $this->actingAs($customer)->withSession(['locale' => 'en'])->get(route('account.notifications'))
            ->assertOk()
            ->assertSeeInOrder([
                'Your order has been placed', '20 Nov 2026, 10:03 AM',
                'Your vendor application was approved', '20 Nov 2026, 10:02 AM',
            ])
            ->assertSee('data-notifications-read-all', false);
        $this->getJson(route('account.notifications.unread'))->assertJson(['unread' => 2]);
    }

    public function test_marking_all_as_read_includes_older_pages_and_only_the_signed_in_account(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        DB::table('orders')->insert(array_fill(0, 21, ['customer_id' => $customer->id]));
        DB::table('orders')->insert(['customer_id' => $other->id]);
        $application = DB::table('vendor_applications')->insertGetId(['user_id' => $customer->id]);
        DB::table('vendor_application_notifications')->insert([
            'user_id' => $customer->id,
            'application_id' => $application,
            'status' => 'approved',
            'created_at' => now(),
        ]);

        $this->actingAs($customer)->getJson(route('account.notifications.unread'))->assertJson(['unread' => 22]);
        $this->postJson(route('account.notifications.read-all'))->assertOk()->assertJson(['unread' => 0]);
        $this->getJson(route('account.notifications.unread'))->assertJson(['unread' => 0]);
        $this->get(route('account.notifications', ['page' => 2]))
            ->assertOk()->assertDontSee('order-inbox-row is-unread');
        $this->post(route('account.notifications.read-all'))->assertRedirect(route('account.notifications'));
        $this->actingAs($other)->getJson(route('account.notifications.unread'))->assertJson(['unread' => 1]);
    }
}
