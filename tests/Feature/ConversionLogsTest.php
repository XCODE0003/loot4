<?php

namespace Tests\Feature;

use App\Enums\ConversionStatus;
use App\Filament\Resources\ConversionLogs\Pages\ListConversionLogs;
use App\Jobs\SendConversionEvent;
use App\Models\ConversionLog;
use App\Models\User;
use App\Services\Conversions\ConversionDispatcher;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class ConversionLogsTest extends TestCase
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

    public function test_job_marks_event_sent_on_success(): void
    {
        $log = ConversionLog::factory()->create([
            'status' => ConversionStatus::Pending,
            'response_payload' => null,
            'sent_at' => null,
        ]);

        (new SendConversionEvent($log->id))->handle(app(ConversionDispatcher::class));

        $log->refresh();
        $this->assertSame(ConversionStatus::Sent, $log->status);
        $this->assertNotNull($log->response_payload);
        $this->assertNotNull($log->sent_at);
    }

    public function test_job_marks_event_failed_on_exception(): void
    {
        $this->app->instance(ConversionDispatcher::class, new class implements ConversionDispatcher
        {
            public function send(ConversionLog $log): array
            {
                throw new RuntimeException('platform rejected');
            }
        });

        $log = ConversionLog::factory()->create(['status' => ConversionStatus::Pending]);

        try {
            (new SendConversionEvent($log->id))->handle(app(ConversionDispatcher::class));
        } catch (RuntimeException) {
            // rethrown so the queue can retry; status update still persists.
        }

        $log->refresh();
        $this->assertSame(ConversionStatus::Failed, $log->status);
        $this->assertSame('platform rejected', $log->reason);
    }

    public function test_retry_action_requeues_event(): void
    {
        $admin = $this->admin();
        Queue::fake();
        $log = ConversionLog::factory()->create(['status' => ConversionStatus::Failed]);

        Livewire::actingAs($admin)
            ->test(ListConversionLogs::class)
            ->callTableAction('retry', $log);

        Queue::assertPushed(SendConversionEvent::class);
        $this->assertSame(ConversionStatus::Pending, $log->refresh()->status);
    }

    public function test_resource_pages_render(): void
    {
        $admin = $this->admin();
        $log = ConversionLog::factory()->create();

        $this->actingAs($admin)->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/conversion-logs')->assertOk();
        $this->actingAs($admin)->get('/asdgkzxcnjngjasdajsnjzcxnc/admin/conversion-logs/'.$log->getKey())->assertOk()->assertSee('Request payload');
    }
}
