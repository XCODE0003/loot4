<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            // Wizard step grouping (Step-by-step layout): fields with the same
            // number share a step; null = the field gets its own step.
            $table->unsignedTinyInteger('step')->nullable()->after('options_columns');
        });
    }

    public function down(): void
    {
        Schema::table('product_form_fields', function (Blueprint $table): void {
            $table->dropColumn('step');
        });
    }
};
