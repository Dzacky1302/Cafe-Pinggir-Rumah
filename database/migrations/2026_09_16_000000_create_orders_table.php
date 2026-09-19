<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->enum('order_type', ['dine_in', 'take_away']);
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->string('table_number')->nullable();
            $table->integer('person_count')->nullable();
            $table->time('pickup_time')->nullable();
            $table->text('notes')->nullable();
            $table->json('items');
            $table->integer('total'); // in cents
            $table->enum('status', ['pending', 'paid', 'processing', 'done', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('midtrans_token')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
