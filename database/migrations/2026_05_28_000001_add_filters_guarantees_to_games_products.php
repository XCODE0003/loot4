<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->json('game_filters')->nullable()->after('landing_settings');
            $table->json('guarantees')->nullable()->after('game_filters');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->json('filter_values')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->dropColumn(['game_filters', 'guarantees']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('filter_values');
        });
    }
};
