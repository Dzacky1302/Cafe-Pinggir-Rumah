<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log mentah setiap notifikasi Midtrans yang diterima.
     *
     * Dibuat append-only dan disimpan TERPISAH dari status order, karena:
     *  - Midtrans mengirim ulang notifikasi yang belum dijawab sukses, sehingga
     *    satu order bisa menerima beberapa notifikasi;
     *  - notifikasi untuk order yang tidak dikenal tetap perlu tercatat;
     *  - inilah bukti audit saat menyelidiki sengketa pembayaran.
     */
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();

            // String, bukan foreign key: notifikasi bisa datang untuk order
            // yang tidak ada di database dan tetap harus tercatat.
            $table->string('order_id')->index();

            $table->string('transaction_id')->nullable()->index();
            $table->string('transaction_status');
            $table->string('fraud_status')->nullable();
            $table->string('payment_type')->nullable();

            // Sengaja disimpan sebagai string apa adanya ("18000.00"),
            // karena signature_key dihitung dari nilai asli Midtrans —
            // konversi ke angka akan merusak verifikasi.
            $table->string('gross_amount');

            $table->boolean('signature_valid')->default(false);
            $table->json('payload');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
