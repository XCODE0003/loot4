<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            // Customer
            $table->string('email');
            $table->string('ip', 45)->nullable();
            $table->string('country', 2)->nullable();

            // Statuses
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('delivery_status')->default('pending');

            // Money
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('discount_code')->nullable();

            // Attribution / Traffic source
            $table->string('source')->nullable();
            $table->string('medium')->nullable();
            $table->string('campaign')->nullable();
            $table->string('content')->nullable();
            $table->string('term')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('ttclid')->nullable();
            $table->string('landing_page')->nullable();
            $table->timestamp('first_visit_at')->nullable();

            // Dynamic data / notes
            $table->json('form_data')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
