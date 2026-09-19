<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Akses ke halaman pembayaran.
 *
 * Sebelumnya siapa pun yang menebak order_id bisa membuka halaman pembayaran
 * pesanan orang lain (nama, nomor HP, nomor meja) dan membuat token Snap
 * atas nama mereka.
 */
class PaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_payment_page_requires_the_order_to_belong_to_the_session(): void
    {
        $order = Order::factory()->pending()->create();

        $this->get(route('checkout.payment', $order->order_id))->assertStatus(403);
    }

    public function test_the_payment_page_is_visible_for_the_session_that_created_the_order(): void
    {
        $order = Order::factory()->takeAway()->pending()->create();

        $this->withSession(['current_order_id' => $order->order_id])
            ->get(route('checkout.payment', $order->order_id))
            ->assertOk()
            ->assertSee($order->order_id);
    }

    public function test_charging_someone_elses_order_is_refused(): void
    {
        $order = Order::factory()->pending()->create();

        $this->post(route('checkout.charge', $order->order_id))->assertStatus(403);
    }

    public function test_the_status_page_stays_read_only_without_a_session(): void
    {
        $order = Order::factory()->pending()->create();

        $this->get(route('checkout.success', $order->order_id))->assertOk();

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
        $this->assertNull($order->fresh()->payment_method);
    }
}