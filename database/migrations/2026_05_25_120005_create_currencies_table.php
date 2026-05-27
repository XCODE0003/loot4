<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('code', 3)->unique();
            $table->string('symbol', 8)->nullable();
            $table->decimal('exchange_rate', 16, 6)->default(1);
            $table->boolean('auto_update')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
