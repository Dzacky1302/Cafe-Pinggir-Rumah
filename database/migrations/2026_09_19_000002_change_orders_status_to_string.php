<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengubah kolom `status` dari enum terbatas menjadi string.
     *
     * Alasan: pemrosesan notifikasi Midtrans membutuhkan nilai tambahan
     * ('expired', 'refunded') yang tidak ada pada enum lama, sementara
     * `enum` berarti setiap status baru memerlukan migrasi lagi dan
     * perilakunya berbeda antar driver. Keabsahan nilai sekarang dijaga
     * satu tempat oleh App\Enums\OrderStatus.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'processing', 'done', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};