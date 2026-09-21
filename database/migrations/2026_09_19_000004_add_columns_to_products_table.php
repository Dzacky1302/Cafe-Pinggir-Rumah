<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tambah kolom baru
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->string('sku')->nullable()->unique()->after('category_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['sku', 'status']);
        });
    }
};
