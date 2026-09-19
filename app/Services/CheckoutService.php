<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Membentuk pesanan dari isi keranjang.
 *
 * Dipisahkan dari controller supaya aturan "dine in vs take away"
 * (kolom mana yang wajib, kapan pickup_time boleh kosong) berada di satu
 * tempat dan bisa diuji tanpa HTTP.
 */
final class CheckoutService
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * @param  array<string, mixed>  $data  data yang sudah lolos validasi
     */
    public function placeOrder(string $orderType, array $data): Order
    {
        return Order::create([
            'order_id' => $this->generateOrderId(),
            'order_type' => $orderType,
            'customer_name' => $data['customer_name'],
            'phone' => $data['phone'] ?? null,
            'table_number' => $data['table_number'] ?? null,
            // person_count belum pernah diisi sejak awal — dipertahankan apa
            // adanya sampai UI dine-in diperluas (Fase 2).
            'person_count' => null,
            'pickup_time' => $this->resolvePickupTime($orderType, $data['pickup_time'] ?? null),
            'notes' => $data['notes'] ?? null,
            'items' => $this->cart->itemsAsList(),
            'total' => $this->cart->total(),
            'status' => OrderStatus::PENDING,
        ]);
    }

    /**
     * Mengikuti pola lama: ORD-ymd-XXXXX (lihat riwayat CoffeeController).
     */
    public function generateOrderId(): string
    {
        return 'ORD-'.date('ymd').'-'.strtoupper(Str::random(5));
    }

    /**
     * 'now' berarti pesanan diambil secepatnya, sehingga jam pengambilan
     * sengaja dikosongkan. Dine in tidak punya jam pengambilan sama sekali.
     */
    private function resolvePickupTime(string $orderType, ?string $pickupTime): ?string
    {
        if ($orderType !== 'take_away' || $pickupTime === null || $pickupTime === 'now') {
            return null;
        }

        return $pickupTime;
    }
}