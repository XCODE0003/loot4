<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Set when the abandoned-cart reminder email has been sent, so an
            // unpaid order is never reminded twice.
            $table->timestamp('abandoned_reminded_at')->nullable()->after('total');
        });

        // Suppress retroactive reminders: mark every pre-existing unpaid order as
        // already reminded so only NEW abandonments (after this deploy) get an
        // email. Protects sender reputation from a first-run backlog blast.
        DB::table('orders')
            ->where('payment_status', PaymentStatus::Pending->value)
            ->whereNull('abandoned_reminded_at')
            ->update(['abandoned_reminded_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('abandoned_reminded_at');
        });
    }
};
