<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom tambahan untuk mencatat hasil pembayaran yang SUDAH terverifikasi
     * dari notifikasi Midtrans (bukan dari halaman sukses).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // ID transaksi Midtrans — untuk rekonsiliasi & audit.
            $table->string('midtrans_transaction_id')->nullable();

            // Jenis pembayaran spesifik: bank_transfer, gopay, qris, credit_card.
            // Berbeda dari `payment_method` yang menyimpan nama gateway.
            $table->string('payment_type')->nullable();

            // accept | challenge | deny — menentukan paid / tetap pending / cancelled.
            $table->string('fraud_status')->nullable();

            // Dasar perhitungan revenue (akrual), bukan created_at.
            $table->timestamp('paid_at')->nullable();

            $table->timestamp('expired_at')->nullable();

            $table->index('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['midtrans_transaction_id']);
            $table->dropColumn([
                'midtrans_transaction_id',
                'payment_type',
                'fraud_status',
                'paid_at',
                'expired_at',
            ]);
        });
    }
};