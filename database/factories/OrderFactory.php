<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = [
            [
                'id' => 1,
                'name' => 'Espresso (Single)',
                'variant' => 'single',
                'price' => 18000,
                'image' => null,
                'qty' => 2,
            ],
        ];

        return [
            'order_id' => $this->generateOrderId(),
            'order_type' => 'take_away',
            'customer_name' => fake()->name(),
            'phone' => '081234567890',
            'table_number' => null,
            'person_count' => null,
            'pickup_time' => null,
            'notes' => null,
            'items' => $items,
            'total' => static::totalOf($items),
            'status' => OrderStatus::PENDING,
            'payment_method' => null,
            'midtrans_token' => null,
        ];
    }

    /**
     * Pesanan dine in: wajib nomor meja, tanpa nomor HP.
     */
    public function dineIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'dine_in',
            'table_number' => 'A1',
            'phone' => null,
            'pickup_time' => null,
        ]);
    }

    /**
     * Pesanan take away: wajib nomor HP.
     */
    public function takeAway(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'take_away',
            'table_number' => null,
            'phone' => '081234567890',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    /**
     * Pesanan yang sudah terbayar.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
            'payment_method' => 'Midtrans',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
        ]);
    }

    /**
     * Hitung total dari daftar item (harga dalam rupiah langsung).
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function totalOf(array $items): int
    {
        return (int) collect($items)
            ->sum(fn (array $item) => $item['price'] * $item['qty']);
    }

    /**
     * Mengikuti pola yang dipakai CheckoutService saat membuat pesanan.
     */
    private function generateOrderId(): string
    {
        return 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
    }
}
