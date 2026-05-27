<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_form_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('key');
            $table->string('type')->default('select');
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->decimal('extra_price', 12, 2)->default(0);
            $table->string('tooltip')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_form_fields');
    }
};
