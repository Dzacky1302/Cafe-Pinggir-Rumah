<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::factory()->variant('single', 18000)->create();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function sessionWithCart(Product $product, int $qty = 1): array
    {
        return [
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variant' => $product->variant,
                    'price' => $product->price,
                    'image' => $product->image,
                    'qty' => $qty,
                ],
            ],
        ];
    }

    public function test_choose_type_redirects_to_cart_when_cart_is_empty(): void
    {
        $response = $this->get(route('checkout.type'));

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_form_rejects_an_unknown_order_type(): void
    {
        $product = $this->product();

        $response = $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.form'), ['order_type' => 'delivery']);

        $response->assertSessionHasErrors('order_type');
    }

    public function test_checkout_form_shows_the_order_type_it_received(): void
    {
        $product = $this->product();

        $response = $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.form'), ['order_type' => 'dine_in']);

        $response->assertOk();
        $response->assertViewHas('order_type', 'dine_in');
        $response->assertViewHas('total', $product->price);
    }

    public function test_dine_in_checkout_requires_a_table_number(): void
    {
        $product = $this->product();

        $response = $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.store'), [
                'order_type' => 'dine_in',
                'customer_name' => 'Budi Santoso',
            ]);

        $response->assertSessionHasErrors('table_number');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_take_away_checkout_requires_phone_and_pickup_time(): void
    {
        $product = $this->product();

        $response = $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.store'), [
                'order_type' => 'take_away',
                'customer_name' => 'Budi Santoso',
            ]);

        $response->assertSessionHasErrors(['phone', 'pickup_time']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_dine_in_checkout_creates_a_pending_order_with_the_right_total(): void
    {
        $product = $this->product();

        $response = $this->withSession($this->sessionWithCart($product, 2))
            ->post(route('checkout.store'), [
                'order_type' => 'dine_in',
                'customer_name' => 'Budi Santoso',
                'table_number' => 'A5',
                'notes' => 'Es sedikit',
            ]);

        $order = Order::sole();

        $this->assertSame('dine_in', $order->order_type);
        $this->assertSame('A5', $order->table_number);
        $this->assertNull($order->phone);
        $this->assertSame(36000 * 100, $order->total);
        $this->assertSame(OrderStatus::PENDING, $order->status);
        $this->assertCount(1, $order->items);
        $this->assertSame(2, $order->items[0]['qty']);

        $response->assertRedirect(route('checkout.payment', $order->order_id));
    }

    public function test_take_away_checkout_with_immediate_pickup_stores_a_null_pickup_time(): void
    {
        $product = $this->product();

        $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.store'), [
                'order_type' => 'take_away',
                'customer_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'pickup_time' => 'now',
            ])
            ->assertRedirect();

        $order = Order::sole();

        $this->assertSame('take_away', $order->order_type);
        $this->assertSame('081234567890', $order->phone);
        $this->assertNull($order->table_number);
        $this->assertNull($order->pickup_time);
    }

    public function test_take_away_checkout_keeps_a_scheduled_pickup_time(): void
    {
        $product = $this->product();

        $this->withSession($this->sessionWithCart($product))
            ->post(route('checkout.store'), [
                'order_type' => 'take_away',
                'customer_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'pickup_time' => '12:00',
            ])
            ->assertRedirect();

        $this->assertSame('12:00', Order::sole()->pickup_time->format('H:i'));
    }

    public function test_checkout_store_redirects_to_cart_when_cart_is_empty(): void
    {
        $response = $this->post(route('checkout.store'), [
            'order_type' => 'dine_in',
            'customer_name' => 'Budi Santoso',
            'table_number' => 'A1',
        ]);

        $response->assertRedirect(route('cart'));
        $this->assertDatabaseCount('orders', 0);
    }
}
