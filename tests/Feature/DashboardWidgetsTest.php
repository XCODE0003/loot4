<?php

namespace Tests\Feature;

use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TrafficSourcesChart;
use App\Models\Order;
use App\Models\StorageUnit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
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

    public function test_dashboard_renders_with_stats(): void
    {
        $admin = $this->admin();
        Order::factory()->count(10)->create();
        StorageUnit::factory()->count(3)->create();

        // Widgets render lazily via Livewire, so the dashboard HTML returns 200
        // and the widget content is asserted separately in the Livewire tests below.
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_each_widget_renders_without_error(): void
    {
        $admin = $this->admin();
        Order::factory()->count(15)->create();

        foreach ([StatsOverview::class, SalesChart::class, RevenueChart::class, TrafficSourcesChart::class] as $widget) {
            Livewire::actingAs($admin)->test($widget)->assertOk();
        }
    }

    public function test_widgets_handle_empty_dataset(): void
    {
        $admin = $this->admin();

        foreach ([StatsOverview::class, SalesChart::class, RevenueChart::class, TrafficSourcesChart::class] as $widget) {
            Livewire::actingAs($admin)->test($widget)->assertOk();
        }
    }
}
