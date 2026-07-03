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
            ->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/orders')
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_order_view_page_renders_all_blocks(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/orders/'.$order->getKey())
            ->assertOk()
            ->assertSee($order->email)
            ->assertSee('Attribution / Traffic Source')
            ->assertSee('Customer Information');
    }

    public function test_customer_order_history_sums_paid_orders_by_email(): void
    {
        $email = 'repeat@buyer.com';
        // Two paid orders (100.00 + 50.50) + one pending that must NOT count toward spend.
        $viewed = Order::factory()->create(['email' => $email, 'payment_status' => PaymentStatus::Paid, 'total' => 100.00]);
        Order::factory()->create(['email' => $email, 'payment_status' => PaymentStatus::Paid, 'total' => 50.50]);
        Order::factory()->create(['email' => $email, 'payment_status' => PaymentStatus::Pending, 'total' => 999.00]);
        // A different customer's order must be excluded entirely.
        Order::factory()->create(['email' => 'someone@else.com', 'payment_status' => PaymentStatus::Paid, 'total' => 777.00]);

        $this->actingAs($this->admin())
            ->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/orders/'.$viewed->getKey())
            ->assertOk()
            ->assertSee('Customer order history')
            ->assertSee('150.50'); // paid total only — the 999.00 pending order is excluded
    }

    public function test_order_view_shows_billing_country_name(): void
    {
        $order = Order::factory()->create([
            'country' => 'DE',
            'delivery_status' => DeliveryStatus::Pending,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($this->admin())
            ->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/orders/'.$order->getKey())
            ->assertOk()
            ->assertSee('Germany'); // DE resolved to its full country name in Billing & Delivery
    }

    public function test_order_edit_page_renders_with_relation_managers(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/orders/'.$order->getKey().'/edit')
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
