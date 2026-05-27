<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\FormsRelationManager;
use App\Models\Game;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogResourcesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        return $admin;
    }

    public function test_games_pages_render(): void
    {
        $game = Game::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/games')->assertOk()->assertSee($game->name);
        $this->actingAs($admin)->get('/admin/games/create')->assertOk();
        $this->actingAs($admin)->get('/admin/games/'.$game->getKey().'/edit')->assertOk()->assertSee('SEO');
    }

    public function test_products_pages_render(): void
    {
        $product = Product::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee($product->name);
        $this->actingAs($admin)->get('/admin/products/create')->assertOk()->assertSee('Pricing');
        $this->actingAs($admin)
            ->get('/admin/products/'.$product->getKey().'/edit')
            ->assertOk()
            ->assertSee('Associations');
    }

    public function test_dynamic_form_builder_relation_manager_mounts(): void
    {
        $admin = $this->admin();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $product = Product::factory()->create();

        Livewire::actingAs($admin)
            ->test(FormsRelationManager::class, [
                'ownerRecord' => $product,
                'pageClass' => EditProduct::class,
            ])
            ->assertOk()
            ->assertSee('Dynamic forms');
    }

    public function test_game_status_can_be_toggled_via_model(): void
    {
        $game = Game::factory()->create(['status' => \App\Enums\GameStatus::Active]);
        $game->update(['status' => \App\Enums\GameStatus::Inactive]);

        $this->assertSame(\App\Enums\GameStatus::Inactive, $game->refresh()->status);
    }
}
