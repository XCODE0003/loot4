<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_overview_renders_with_stats_and_recent_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/account')->assertOk()->assertInertia(
            fn (Assert $page) => $page
                ->component('loot4/account/Index')
                ->has('stats')
                ->has('recentOrders', 2),
        );
    }

    public function test_orders_list_shows_only_own_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Order::factory()->count(2)->create(['user_id' => $other->id]);

        $this->actingAs($user)->get('/account/orders')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('loot4/account/Orders')->has('orders', 3),
        );
    }

    public function test_cannot_view_another_users_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($user)->get("/account/orders/{$order->id}")->assertForbidden();
    }

    public function test_can_view_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->actingAs($user)->get("/account/orders/{$order->id}")->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('loot4/account/OrderShow')
                ->where('order.number', $order->order_number)
                ->has('order.items', 1),
        );
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/account/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('NewPass123!', $user->refresh()->password));
    }

    public function test_checkout_links_order_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['slug' => 'pp', 'status' => ProductStatus::Active, 'price' => 10]);

        $this->actingAs($user)->post('/checkout', [
            'email' => $user->email,
            'items' => [['slug' => 'pp', 'qty' => 1]],
        ])->assertRedirect();

        $this->assertSame($user->id, Order::latest('id')->first()->user_id);
    }
}
