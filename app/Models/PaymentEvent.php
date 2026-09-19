<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan mentah setiap notifikasi Midtrans yang diterima.
 *
 * Append-only dan sengaja terpisah dari status pesanan, karena notifikasi
 * yang sama bisa datang berkali-kali (Midtrans mengirim ulang bila belum
 * dijawab sukses) dan notifikasi untuk pesanan tak dikenal tetap harus
 * tersimpan sebagai bukti audit.
 */
class PaymentEvent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'transaction_id',
        'transaction_status',
        'fraud_status',
        'payment_type',
        'gross_amount',
        'signature_valid',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'payload' => 'array',
        ];
    }
}
