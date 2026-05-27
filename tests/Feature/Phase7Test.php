<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Filament\Pages\Settings;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class Phase7Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $admin;
    }

    // --- Audit logs ---

    public function test_updating_an_order_records_activity_with_causer(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $order = Order::factory()->create(['status' => OrderStatus::Pending]);
        $order->update(['status' => OrderStatus::Completed]);

        $activity = Activity::query()
            ->where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($admin->id, $activity->causer_id);
    }

    public function test_audit_log_resource_renders(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        Order::factory()->create()->update(['status' => OrderStatus::Completed]);

        $this->get(ActivityResource::getUrl('index'))->assertOk();
    }

    // --- REST API ---

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_api_login_issues_token_and_endpoints_work(): void
    {
        $user = User::factory()->create();

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('data.token');
        $this->assertNotEmpty($token);

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/products', $headers)->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->getJson('/api/orders', $headers)->assertOk();
        $this->getJson('/api/analytics', $headers)
            ->assertOk()
            ->assertJsonStructure(['data' => ['revenue', 'sales', 'conversion', 'average_order_value']]);
        $this->getJson('/api/user', $headers)->assertOk()->assertJsonPath('data.email', $user->email);
    }

    // --- Settings ---

    public function test_settings_page_saves_values(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Settings::class)
            ->set('data.site_name', 'Loot4you')
            ->set('data.payment_stripe', true)
            ->call('save');

        $this->assertSame('Loot4you', Setting::get('site_name'));
        $this->assertTrue(Setting::get('payment_stripe'));
    }

    // --- Import / Export actions present ---

    public function test_orders_export_and_products_import_actions_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ListOrders::class)->assertActionExists('export');
        Livewire::actingAs($admin)->test(ListProducts::class)->assertActionExists('import');
    }
}
