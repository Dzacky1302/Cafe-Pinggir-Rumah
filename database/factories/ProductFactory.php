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
     * Harga default Rp 25.000 — disimpan langsung sebagai integer rupiah.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'variant' => 'regular',
            'price' => 25000,
            'image' => null,
            'status' => 'active',
        ];
    }

    /**
     * Set varian + harga dalam Rupiah (disimpan apa adanya, tanpa ×100).
     */
    public function variant(string $variant, int $rupiah): static
    {
        return $this->state(fn (array $attributes) => [
            'variant' => $variant,
            'price' => $rupiah,
        ]);
    }
}
