<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\ConversionLog;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Game;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quote;
use App\Models\StorageUnit;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // --- Staff accounts (one per role) ---
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@loot4you.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->syncRoles(['Super Admin']);

        foreach (['Admin', 'Manager', 'Support', 'Finance'] as $role) {
            $user = User::firstOrCreate(
                ['email' => strtolower($role).'@team.loot4you.test'],
                [
                    'name' => $role.' User',
                    'password' => Hash::make('password'),
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles([$role]);
        }

        // --- Customers ---
        $customers = User::factory(25)->create(['status' => UserStatus::Active]);

        // --- Currencies ---
        $usd = Currency::firstOrCreate(['code' => 'USD'], [
            'title' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 1, 'is_default' => true, 'last_updated_at' => now(),
        ]);
        Currency::firstOrCreate(['code' => 'EUR'], [
            'title' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.92, 'last_updated_at' => now(),
        ]);
        Currency::firstOrCreate(['code' => 'GBP'], [
            'title' => 'British Pound', 'symbol' => '£', 'exchange_rate' => 0.79, 'last_updated_at' => now(),
        ]);

        // --- Catalog ---
        $gameImages = ['discover_gta.png', 'discover_arc.png', 'discover_forza.png', 'discover_fortnite.png'];
        $productImages = ['game_cashboost.png', 'game_merry.png', 'game_unlock.png', 'product_image.png'];

        $games = Game::factory(8)->create();
        $games->each(fn (Game $game, int $i) => $this->attachImage($game, 'image', $gameImages[$i % count($gameImages)]));

        $categories = Category::factory(6)->create();
        $tags = Tag::factory(10)->create();

        $products = collect();
        for ($i = 0; $i < 30; $i++) {
            $product = Product::factory()->create([
                'game_id' => $games->random()->id,
                'currency_id' => $usd->id,
            ]);
            $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));
            $product->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
            $this->attachImage($product, 'main', $productImages[$i % count($productImages)]);
            $products->push($product);

            // Stock for some products
            if (rand(0, 1)) {
                StorageUnit::factory(rand(1, 4))->create(['product_id' => $product->id]);
            }
        }

        // --- Coupons ---
        Coupon::factory(8)->create();

        // --- Orders with items + payments (last 60 days) ---
        $orders = collect();
        for ($i = 0; $i < 60; $i++) {
            $order = Order::factory()->create([
                'user_id' => rand(0, 1) ? $customers->random()->id : null,
            ]);

            foreach (range(1, rand(1, 3)) as $n) {
                $product = $products->random();
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'status' => $order->delivery_status,
                ]);
            }

            Payment::factory()->create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => $order->payment_status,
            ]);

            $orders->push($order);
        }

        // --- Conversion logs ---
        for ($i = 0; $i < 50; $i++) {
            ConversionLog::factory()->create(['order_id' => $orders->random()->id]);
        }

        // --- Tickets with messages ---
        Ticket::factory(15)
            ->create(['user_id' => fn () => $customers->random()->id])
            ->each(function (Ticket $ticket) use ($customers): void {
                TicketMessage::factory()->count(rand(1, 4))->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $customers->random()->id,
                ]);
            });

        // --- Quotes ---
        Quote::factory(12)->create([
            'user_id' => fn () => rand(0, 1) ? $customers->random()->id : null,
            'product_id' => fn () => rand(0, 1) ? $products->random()->id : null,
        ]);
    }

    /**
     * Copy a bundled storefront image into the model's media collection.
     */
    private function attachImage(object $model, string $collection, string $file): void
    {
        $path = base_path("resources/js/loot4/assets/img/{$file}");

        if (is_file($path)) {
            $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
        }
    }
}
