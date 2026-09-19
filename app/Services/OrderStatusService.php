<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Menerapkan hasil notifikasi Midtrans ke status pesanan.
 *
 * Kelas ini SENGAJA tidak melakukan pemanggilan HTTP dan tidak memverifikasi
 * signature — itu tanggung jawab lapisan webhook. Pemisahan ini membuat logika
 * transisi status bisa diuji tanpa meniru jaringan.
 */
final class OrderStatusService
{
    /**
     * Menerapkan satu notifikasi Midtrans ke sebuah pesanan.
     *
     * Idempotent: memanggil method ini dua kali dengan notifikasi yang sama
     * hanya mengubah status sekali. Ini penting karena Midtrans mengirim ulang
     * notifikasi yang belum dijawab sukses.
     *
     * @param  array<string, mixed>  $payload  isi notifikasi yang signature-nya SUDAH terverifikasi
     * @return bool true bila status berubah, false bila tidak ada perubahan
     */
    public function applyMidtransStatus(Order $order, array $payload): bool
    {
        $target = $this->resolveStatus(
            $payload['transaction_status'] ?? null,
            $payload['fraud_status'] ?? null,
        );

        if ($target === null) {
            return false;
        }

        return DB::transaction(function () use ($order, $payload, $target): bool {
            // Kunci baris: notifikasi bisa datang beruntun. Tanpa lock, dua
            // request bisa membaca status lama yang sama lalu sama-sama menulis.
            // (Di SQLite lock ini diabaikan driver, di MySQL/Postgres aktif.)
            $fresh = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->first();

            if ($fresh === null) {
                return false;
            }

            // Gerbang idempotency: transisi ke status yang sama, atau ke status
            // yang tidak diizinkan dari status sekarang, diabaikan sepenuhnya.
            if (! OrderStatus::canTransition($fresh->status, $target)) {
                return false;
            }

            $fresh->forceFill($this->attributesFor($fresh, $target, $payload))->save();

            return true;
        });
    }

    /**
     * Memetakan pasangan (transaction_status, fraud_status) Midtrans
     * ke status pesanan.
     *
     * Mengembalikan null bila notifikasi tidak dikenali, supaya notifikasi
     * tak terduga tidak pernah mengubah data.
     */
    public function resolveStatus(?string $transactionStatus, ?string $fraudStatus): ?string
    {
        return match ($transactionStatus) {
            // capture/settlement hanya dianggap lunas bila FDS menyatakan aman.
            // 'challenge' menahan pesanan tetap pending untuk ditinjau manual.
            // 'deny' berarti pembayaran ditolak.
            'capture', 'settlement' => match ($fraudStatus) {
                'deny' => OrderStatus::CANCELLED,
                'challenge' => OrderStatus::PENDING,
                default => OrderStatus::PAID,
            },
            'pending' => OrderStatus::PENDING,
            'deny', 'cancel', 'failure' => OrderStatus::CANCELLED,
            'expire' => OrderStatus::EXPIRED,
            'refund', 'partial_refund' => OrderStatus::REFUNDED,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function attributesFor(Order $order, string $target, array $payload): array
    {
        $attributes = ['status' => $target];

        if ($target === OrderStatus::PAID) {
            // payment_method = nama gateway, payment_type = cara bayar spesifik
            // (bank_transfer, gopay, qris, credit_card). Keduanya dibutuhkan
            // untuk analitik pembayaran di laporan.
            $attributes['payment_method'] = 'Midtrans';
            $attributes['payment_type'] = $payload['payment_type'] ?? null;
            $attributes['fraud_status'] = $payload['fraud_status'] ?? null;
            $attributes['midtrans_transaction_id'] = $payload['transaction_id'] ?? null;
            $attributes['paid_at'] = $order->paid_at ?? $this->timeFrom($payload);
        }

        if ($target === OrderStatus::EXPIRED) {
            $attributes['expired_at'] = now();
        }

        if ($target === OrderStatus::CANCELLED) {
            $attributes['fraud_status'] = $payload['fraud_status'] ?? null;
        }

        return $attributes;
    }

    /**
     * Waktu pembayaran diambil dari Midtrans bila tersedia, agar laporan
     * revenue memakai waktu transaksi sebenarnya, bukan waktu server kita.
     *
     * @param  array<string, mixed>  $payload
     */
    private function timeFrom(array $payload): Carbon
    {
        $value = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;

        return $value ? Carbon::parse($value) : now();
    }
}
