<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    private function makeOrder(): Order
    {
        $order = Order::factory()->create([
            'delivery_status' => DeliveryStatus::Pending,
            'payment_status' => PaymentStatus::Paid,
        ]);
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);
        Payment::factory()->create(['order_id' => $order->id]);

        return $order;
    }

    public function test_orders_list_page_renders(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_order_view_page_renders_all_blocks(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get('/admin/orders/'.$order->getKey())
            ->assertOk()
            ->assertSee($order->email)
            ->assertSee('Attribution / Traffic Source')
            ->assertSee('Customer Information');
    }

    public function test_order_edit_page_renders_with_relation_managers(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get('/admin/orders/'.$order->getKey().'/edit')
            ->assertOk();
    }

    public function test_mark_delivered_bulk_action_updates_status(): void
    {
        $admin = $this->admin();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $order = $this->makeOrder();

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->callTableBulkAction('markDelivered', [$order]);

        $this->assertSame(DeliveryStatus::Delivered, $order->refresh()->delivery_status);
    }

    public function test_refund_bulk_action_updates_order_and_payments(): void
    {
        $admin = $this->admin();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $order = $this->makeOrder();

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->callTableBulkAction('refund', [$order]);

        $order->refresh();
        $this->assertSame(PaymentStatus::Refunded, $order->payment_status);
        $this->assertTrue($order->payments()->where('status', '!=', PaymentStatus::Refunded->value)->doesntExist());
    }
}
