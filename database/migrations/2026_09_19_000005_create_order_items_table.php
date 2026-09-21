<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // product_id nullable: produk bisa dihapus setelah order dibuat
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');   // snapshot nama saat order
            $table->string('variant')->nullable();
            $table->integer('price');         // harga per item dalam rupiah (snapshot)
            $table->integer('quantity');
            $table->integer('subtotal');      // price * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
