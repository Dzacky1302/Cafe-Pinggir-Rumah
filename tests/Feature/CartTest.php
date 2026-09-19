<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Struktur item cart harus identik dengan yang ditulis
     * CoffeeController::addToCart().
     *
     * @return array<string, mixed>
     */
    private function cartItem(Product $product, int $qty = 1): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'variant' => $product->variant,
            'price' => $product->price,
            'image' => $product->image,
            'qty' => $qty,
        ];
    }

    public function test_adding_a_product_stores_it_in_the_session_cart(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('cart.add'), ['product_id' => $product->id]);

        $response->assertRedirect();
        $response->assertSessionHas('cart', function (array $cart) use ($product): bool {
            return count($cart) === 1
                && (int) $cart[$product->id]['id'] === $product->id
                && (int) $cart[$product->id]['qty'] === 1
                && (int) $cart[$product->id]['price'] === $product->price;
        });
    }

    public function test_adding_the_same_product_twice_increments_quantity_instead_of_duplicating(): void
    {
        $product = Product::factory()->create();

        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product)]])
            ->post(route('cart.add'), ['product_id' => $product->id]);

        $response->assertSessionHas('cart', function (array $cart) use ($product): bool {
            return count($cart) === 1 && (int) $cart[$product->id]['qty'] === 2;
        });
    }

    public function test_removing_a_product_drops_it_from_the_cart(): void
    {
        $product = Product::factory()->create();

        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product, 3)]])
            ->post(route('cart.remove', $product->id));

        $response->assertRedirect(route('cart'));
        $response->assertSessionMissing('cart.'.$product->id);
    }

    public function test_cart_page_computes_the_total_from_quantity_times_price(): void
    {
        $product = Product::factory()->variant('regular', 25000)->create();

        $response = $this->withSession(['cart' => [$product->id => $this->cartItem($product, 2)]])
            ->get(route('cart'));

        $response->assertOk();
        $response->assertViewHas('total', 50000 * 100);
        $response->assertSee('50.000', false);
    }

    public function test_cart_page_is_still_reachable_when_the_cart_is_empty(): void
    {
        $response = $this->get(route('cart'));

        $response->assertOk();
        $response->assertViewHas('total', 0);
    }
}
