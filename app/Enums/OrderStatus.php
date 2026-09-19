<?php

namespace App\Enums;

/**
 * Status pesanan.
 *
 * Ditulis sebagai konstanta string (belum PHP enum) karena beberapa view
 * merender nilainya langsung sebagai bagian dari nama kelas CSS,
 * mis. `badge-{{ $order->status }}` di admin/orders/show.blade.php.
 * Konversi ke backed enum dilakukan saat view admin direfaktor (Fase 2).
 */
final class OrderStatus
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const PROCESSING = 'processing';

    public const DONE = 'done';

    public const CANCELLED = 'cancelled';

    public const EXPIRED = 'expired';

    public const REFUNDED = 'refunded';

    /**
     * Transisi status yang diizinkan.
     *
     * Status tanpa tujuan transisi berarti terminal (tidak bisa berubah lagi).
     *
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        self::PENDING => [self::PAID, self::CANCELLED, self::EXPIRED],
        self::PAID => [self::PROCESSING, self::REFUNDED, self::CANCELLED],
        self::PROCESSING => [self::DONE, self::CANCELLED],
        self::DONE => [self::REFUNDED],
        self::CANCELLED => [],
        self::EXPIRED => [],
        self::REFUNDED => [],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::PROCESSING,
            self::DONE,
            self::CANCELLED,
            self::EXPIRED,
            self::REFUNDED,
        ];
    }

    /**
     * Apakah transisi dari $from ke $to diizinkan?
     *
     * Transisi ke status yang sama SELALU false. Inilah yang membuat
     * pemrosesan notifikasi Midtrans yang datang berulang menjadi idempotent:
     * notifikasi kedua berstatus sama dengan status sekarang, jadi tidak
     * ada perubahan yang diterapkan.
     */
    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }
}
