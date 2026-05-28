<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Enums\StorageUnitStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\Quotes\Pages\ListQuotes;
use App\Filament\Resources\StorageUnits\Pages\ListStorageUnits;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Quote;
use App\Models\StorageUnit;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsResourcesTest extends TestCase
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

    public function test_all_phase4_list_and_create_pages_render(): void
    {
        $admin = $this->admin();
        Currency::factory()->create();
        Coupon::factory()->create();
        StorageUnit::factory()->create();
        Ticket::factory()->create();
        Quote::factory()->create();

        foreach (['currencies', 'coupons', 'storage-units', 'users', 'tickets', 'quotes'] as $slug) {
            $this->actingAs($admin)->get("/asdgkzxcnjngjasdajsnjzcxnc/admin/{$slug}")->assertOk();
            $this->actingAs($admin)->get("/asdgkzxcnjngjasdajsnjzcxnc/admin/{$slug}/create")->assertOk();
        }
    }

    public function test_storage_import_creates_units(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();

        Livewire::actingAs($admin)
            ->test(ListStorageUnits::class)
            ->callAction('import', data: [
                'product_id' => $product->id,
                'type' => 'key',
                'lines' => "KEY-1\nKEY-2\n\nKEY-3",
            ]);

        $this->assertSame(3, StorageUnit::where('product_id', $product->id)->count());
        $this->assertSame(StorageUnitStatus::Available, StorageUnit::first()->status);
    }

    public function test_user_ban_action_sets_status(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['status' => UserStatus::Active]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('toggleBan', $user);

        $this->assertSame(UserStatus::Banned, $user->refresh()->status);
    }

    public function test_quote_approve_action_sets_status(): void
    {
        $admin = $this->admin();
        $quote = Quote::factory()->create(['status' => QuoteStatus::New]);

        Livewire::actingAs($admin)
            ->test(ListQuotes::class)
            ->callTableAction('approve', $quote);

        $this->assertSame(QuoteStatus::Approved, $quote->refresh()->status);
    }
}
