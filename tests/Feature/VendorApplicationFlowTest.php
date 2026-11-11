<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorApplication;
use App\Models\VendorUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorApplicationFlowTest extends TestCase
{
    public function test_missing_account_details_are_required_and_validation_input_is_encrypted(): void
    {
        DB::beginTransaction();
        try {
            $customer = User::create([
                'name' => 'User 1234', 'first_name' => 'User', 'last_name' => '',
                'registration_method' => 'phone_otp', 'phone' => '01012345678',
                'password' => 'password',
            ]);
            $this->actingAs($customer)->get(route('vendor.register'))->assertOk()
                ->assertSee('name="email"', false)->assertSee('name="first_name"', false)
                ->assertDontSee('name="phone"', false);
            $response = $this->post(route('vendor.register.submit'), [
                'action' => 'submit', 'address' => 'Sensitive residence',
            ])->assertSessionHasErrors(['email', 'first_name', 'last_name', 'identity_document']);
            $encryptedInput = session('vendor_application_input');
            $this->assertIsString($encryptedInput);
            $this->assertStringNotContainsString('Sensitive residence', $encryptedInput);
            $this->get(route('vendor.register'))->assertOk()->assertSee('Sensitive residence');
            $this->assertDatabaseMissing('vendor_applications', ['user_id' => $customer->id]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_existing_seller_can_only_be_linked_with_their_own_credentials(): void
    {
        DB::beginTransaction();
        try {
            $suffix = uniqid();
            $customer = User::create(['name' => 'Customer Name', 'email' => "customer-{$suffix}@example.test", 'password' => 'password']);
            $vendor = new VendorUser();
            $vendor->first_name = 'Seller';
            $vendor->last_name = 'Name';
            $vendor->email = "seller-{$suffix}@example.test";
            $vendor->phone = '019'.str_pad((string) $customer->id, 8, '0', STR_PAD_LEFT);
            $vendor->password = 'seller-password';
            $vendor->shop_name = 'Existing shop';
            $vendor->shop_address = 'Shop';
            $vendor->status = 'approved';
            $vendor->auth_token = '0';
            $vendor->holder_name = '';
            $vendor->bank_name = 'not set';
            $vendor->branch = 'not set';
            $vendor->save();

            $this->actingAs($customer)->post(route('vendor.marketplace.link-existing'), [
                'seller_email' => $vendor->email, 'seller_password' => 'wrong',
            ])->assertSessionHasErrors('seller_email');
            $this->assertDatabaseMissing('vendor_applications', ['user_id' => $customer->id, 'vendor_id' => $vendor->id]);
            $this->post(route('vendor.marketplace.link-existing'), [
                'seller_email' => $vendor->email, 'seller_password' => 'seller-password',
            ])->assertSessionHasNoErrors()->assertRedirect(route('vendor.register'));
            $this->assertDatabaseHas('vendor_applications', ['user_id' => $customer->id, 'vendor_id' => $vendor->id, 'status' => 'approved']);
            $this->post(route('vendor.marketplace.switch'))->assertRedirect(route('vendor.dashboard'));
            $this->get(route('vendor.dashboard'))->assertOk()->assertSee('Existing shop');
            DB::table('vendor_users')->where('id', $vendor->id)->update(['status' => 'blocked']);
            $this->get(route('vendor.products'))->assertRedirect(route('vendor.register'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_make_id_optional_and_customer_can_resubmit_after_rejection(): void
    {
        DB::beginTransaction();
        try {
            $suffix = uniqid();
            $admin = User::create(['name' => 'Admin', 'email' => "admin-{$suffix}@example.test", 'password' => 'password']);
            DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
            $admin->refresh();
            $customer = User::create([
                'name' => 'Existing Customer', 'first_name' => 'Existing', 'last_name' => 'Customer',
                'email' => "customer-{$suffix}@example.test", 'phone' => '01012345678', 'password' => 'password',
            ]);
            $this->actingAs($admin)->post(route('admin.vendor-applications.settings'), ['require_identity' => '0'])->assertRedirect();
            $this->actingAs($customer)->post(route('vendor.register.submit'), ['action' => 'submit', 'address' => 'Home address'])
                ->assertSessionHasNoErrors()->assertRedirect();
            $application = VendorApplication::where('user_id', $customer->id)->firstOrFail();
            $this->assertNull($application->identity_document);
            $reasonId = DB::table('vendor_rejection_reasons')->where('active', true)->orderBy('id')->value('id');
            $reason = DB::table('vendor_rejection_reasons')->where('id', $reasonId)->value('label');
            $this->actingAs($admin)->get(route('admin.vendor-applications'))->assertOk()->assertSee($reason)->assertSee('Consider later');
            $this->actingAs($admin)->patch(route('admin.vendor-applications.review', $application->id), ['decision' => 'hold'])->assertRedirect();
            $this->assertSame('on_hold', $application->fresh()->status);
            $this->actingAs($customer)->get(route('vendor.register'))->assertSee('Your application is on hold');
            $this->post(route('vendor.register.submit'), ['action' => 'submit', 'address' => 'Another address'])->assertRedirect();
            $this->assertSame(1, $application->fresh()->submission_count);
            $this->actingAs($admin)->patch(route('admin.vendor-applications.review', $application->id), [
                'decision' => 'reject', 'reason_id' => $reasonId, 'add_flag' => '1',
            ])->assertRedirect();
            $this->actingAs($customer)->get(route('vendor.register'))->assertSee($reason);
            $notice = DB::table('vendor_application_notifications')->where('application_id', $application->id)->where('status', 'rejected')->first();
            $this->post(route('account.notifications.vendor.open', $notice->id))->assertRedirect(route('vendor.register'));
            $this->assertNotNull(DB::table('vendor_application_notifications')->where('id', $notice->id)->value('read_at'));
            $this->post(route('vendor.register.submit'), ['action' => 'submit', 'address' => 'Corrected address'])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame('pending', $application->fresh()->status);
            $this->assertSame('Corrected address', $application->fresh()->personal_data['address']);
            $this->assertSame(2, $application->fresh()->submission_count);
            $this->actingAs($admin)->get(route('admin.users.flags', $customer->id))->assertSee($reason);
            $this->patch(route('admin.vendor-applications.review', $application->id), [
                'decision' => 'reject', 'reason_id' => $reasonId,
            ])->assertRedirect();
            $this->actingAs($customer)->get(route('vendor.register'))->assertSee('Application limit reached');
            $this->post(route('vendor.register.submit'), ['action' => 'submit', 'address' => 'Third attempt'])->assertRedirect();
            $this->assertSame(2, $application->fresh()->submission_count);
            $this->assertSame(1, DB::table('user_flags')->where('user_id', $customer->id)->count());
            $this->assertSame(3, DB::table('vendor_application_reviews')->where('application_id', $application->id)->count());
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_manage_reasons_and_private_account_flags(): void
    {
        DB::beginTransaction();
        try {
            $suffix = uniqid();
            $admin = User::create(['name' => 'Admin', 'email' => "admin-{$suffix}@example.test", 'password' => 'password']);
            DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
            $admin->refresh();
            $customer = User::create(['name' => 'Customer', 'email' => "customer-{$suffix}@example.test", 'password' => 'password']);
            $this->actingAs($customer)->get(route('admin.users.flags', $customer->id))->assertForbidden();
            $this->post(route('admin.users.flags.store', $customer->id), ['reason' => 'Unauthorized'])->assertForbidden();
            $this->actingAs($admin)->post(route('admin.vendor-applications.reasons.add'), ['label' => 'Needs a clearer document'])->assertRedirect();
            $reason = DB::table('vendor_rejection_reasons')->where('label', 'Needs a clearer document')->first();
            $this->patch(route('admin.vendor-applications.reasons.toggle', $reason->id))->assertRedirect();
            $this->assertFalse((bool) DB::table('vendor_rejection_reasons')->where('id', $reason->id)->value('active'));
            $this->post(route('admin.users.flags.store', $customer->id), ['reason' => 'Private review note'])->assertRedirect();
            $flag = DB::table('user_flags')->where('user_id', $customer->id)->first();
            $this->assertNotNull($flag);
            $this->assertStringNotContainsString('Private review note', $flag->reason);
            $this->get(route('admin.users.flags', $customer->id))->assertOk()->assertSee('Private review note');
            $this->patch(route('admin.users.flags.resolve', [$customer->id, $flag->id]))->assertRedirect();
            $this->assertNotNull(DB::table('user_flags')->where('id', $flag->id)->value('resolved_at'));
        } finally {
            DB::rollBack();
        }
    }

    public function test_customer_applies_privately_then_creates_and_accesses_a_marketplace(): void
    {
        Storage::fake('public');
        DB::beginTransaction();
        try {
            DB::table('app_configs')->where('config_key', 'vendor_application_settings')->delete();
            $suffix = uniqid();
            $customer = User::create([
                'name' => 'Existing Name', 'first_name' => 'Existing', 'last_name' => 'Name',
                'email' => "vendor-{$suffix}@example.test", 'phone' => '01012345678',
                'password' => 'customer-password',
            ]);
            $admin = User::create(['name' => 'Admin', 'email' => "admin-{$suffix}@example.test", 'password' => 'admin-password']);
            DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
            $admin->refresh();
            $category = DB::table('categories2')->insertGetId(['name' => 'Vendor test', 'slug' => "vendor-test-{$suffix}", 'parent' => 0]);
            $identity = UploadedFile::fake()->image('identity.jpg', 20, 20);
            $originalBytes = file_get_contents($identity->getRealPath());

            $this->actingAs($customer)->get(route('vendor.register'))->assertOk()
                ->assertDontSee('name="email"', false)->assertDontSee('name="first_name"', false);
            $this->post(route('vendor.register.submit'), ['action' => 'submit', 'address' => 'Private residence'])
                ->assertSessionHasErrors('identity_document')
                ->assertSessionHas('vendor_application_input');
            $this->post(route('vendor.register.submit'), [
                'action' => 'submit', 'address' => 'Private residence', 'identity_document' => $identity,
            ])->assertRedirect(route('vendor.register'));
            $application = VendorApplication::where('user_id', $customer->id)->firstOrFail();
            $this->assertSame('pending', $application->status);
            $row = DB::table('vendor_applications')->where('id', $application->id)->first();
            $this->assertStringNotContainsString('Private residence', $row->personal_data);
            $this->assertStringNotContainsString(base64_encode($originalBytes), $row->identity_document);
            $this->assertSame($originalBytes, base64_decode($application->identity_document));
            $this->get(route('admin.vendor-applications.identity', $application->id))->assertForbidden();

            $this->actingAs($admin)->get(route('admin.vendor-applications'))->assertOk()
                ->assertSee('Private residence')->assertSee('I reviewed this ID');
            $this->actingAs($admin)->get(route('admin.vendor-applications.identity', $application->id))
                ->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
            $this->patch(route('admin.vendor-applications.review', $application->id), ['decision' => 'approve'])
                ->assertSessionHasErrors('identity_verified');
            $this->patch(route('admin.vendor-applications.review', $application->id), ['decision' => 'approve', 'identity_verified' => '1'])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame('approved', $application->fresh()->status);
            $this->actingAs($customer)->get(route('account.notifications.unread'))->assertJson(['unread' => 1]);
            $this->get(route('vendor.register'))->assertOk()->assertSee('Give your marketplace a name');
            $this->post(route('vendor.marketplace.save'), [
                'action' => 'save', 'shop_name' => 'My test store',
            ])->assertRedirect(route('account.hub'));
            $this->assertSame('My test store', $application->fresh()->marketplace_draft['shop_name']);
            $this->post(route('vendor.marketplace.save'), [
                'action' => 'create', 'shop_name' => 'My test store', 'category_id' => $category,
                'shop_address' => 'Shop address', 'shop_phone' => '011'.str_pad((string) $customer->id, 8, '0', STR_PAD_LEFT),
            ])->assertSessionHasNoErrors()->assertRedirect(route('vendor.dashboard'));
            $vendorId = $application->fresh()->vendor_id;
            $this->assertNotNull($vendorId);
            $this->assertDatabaseHas('vendor_users', ['id' => $vendorId, 'store_category_id' => $category, 'status' => 'approved']);
            $this->get(route('vendor.dashboard'))->assertOk()->assertSee('My test store');
        } finally {
            DB::rollBack();
        }
    }
}
