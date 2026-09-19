<?php

namespace App\Services;

use App\Models\Product;

/**
 * Satu-satunya tempat isi keranjang dibaca dan diubah.
 *
 * Sebelumnya perhitungan total diulang di empat tempat di CoffeeController
 * (cart, chooseType, checkoutForm, storeCheckout) plus sekali lagi di
 * navbar. Semua duplikasi itu kini bermuara ke kelas ini.
 */
final class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    /**
     * Item sebagai list (bukan map ber-key product id) — bentuk yang disimpan
     * ke kolom orders.items.
     *
     * @return array<int, array<string, mixed>>
     */
    public function itemsAsList(): array
    {
        return array_values($this->items());
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function count(): int
    {
        return (int) array_sum(array_map(
            fn (array $item): int => (int) $item['qty'],
            $this->items()
        ));
    }

    /**
     * Total belanja dalam satuan simpan (×100 — lihat migrasi products).
     */
    public function total(): int
    {
        return (int) array_sum(array_map(
            fn (array $item): int => (int) $item['price'] * (int) $item['qty'],
            $this->items()
        ));
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->items();

        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] = (int) $cart[$product->id]['qty'] + $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'variant' => $product->variant,
                'price' => $product->price,
                'image' => $product->image,
                'qty' => $quantity,
            ];
        }

        session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->items();

        if (! isset($cart[$productId])) {
            return;
        }

        unset($cart[$productId]);

        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
