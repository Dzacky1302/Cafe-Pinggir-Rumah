<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Harga default Rp 25.000, disimpan sebagai integer ×100
     * (lihat migrasi create_products_table: "price" in cents).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'variant' => 'regular',
            'price' => 25000 * 100,
            'image' => null,
        ];
    }

    /**
     * Set varian + harga dalam Rupiah; konversi ke satuan simpan ditangani factory.
     */
    public function variant(string $variant, int $rupiah): static
    {
        return $this->state(fn (array $attributes) => [
            'variant' => $variant,
            'price' => $rupiah * 100,
        ]);
    }
}
