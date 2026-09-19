<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sisi keamanan webhook Midtrans.
 *
 * Ini yang menutup celah "melunasi pesanan tanpa membayar": status pesanan
 * hanya boleh berubah dari notifikasi yang signature-nya valid.
 */
class MidtransWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-TESTKEY';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.midtrans.server_key' => self::SERVER_KEY]);
    }

    private function grossOf(Order $order): string
    {
        return number_format((int) $order->total / 100, 2, '.', '');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function notification(Order $order, array $overrides = []): array
    {
        $payload = array_merge([
            'order_id' => $order->order_id,
            'status_code' => '200',
            'gross_amount' => $this->grossOf($order),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ], $overrides);

        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].self::SERVER_KEY);

        return $payload;
    }

    public function test_an_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $order = Order::factory()->pending()->create();

        $payload = $this->notification($order);
        $payload['signature_key'] = str_repeat('a', 128);

        $this->postJson(route('webhook.midtrans'), $payload)->assertStatus(403);

        $order->refresh();

        $this->assertSame(OrderStatus::PENDING, $order->status);
        $this->assertNull($order->paid_at);

        $event = PaymentEvent::query()->where('order_id', $order->order_id)->firstOrFail();
        $this->assertFalse($event->signature_valid);
    }

    public function test_an_unsigned_attempt_to_mark_an_order_paid_is_rejected(): void
    {
        $order = Order::factory()->pending()->create();

        // Meniru siapa pun yang menemukan endpoint ini dan mencoba
        // "melunasi" pesanan tanpa mengetahui server key.
        $this->postJson(route('webhook.midtrans'), [
            'order_id' => $order->order_id,
            'status_code' => '200',
            'gross_amount' => $this->grossOf($order),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ])->assertStatus(403);

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_validly_signed_notification_for_a_different_amount_is_refused(): void
    {
        $order = Order::factory()->pending()->create();

        // Signature dihitung atas nominal palsu, jadi lolos verifikasi signature —
        // pertahanan lapis kedua (pencocokan nominal) yang menolaknya.
        $this->postJson(route('webhook.midtrans'), $this->notification($order, ['gross_amount' => '1.00']))
            ->assertStatus(422);

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_notification_for_an_unknown_order_is_acknowledged_but_ignored(): void
    {
        $order = Order::factory()->pending()->create();

        $payload = $this->notification($order, ['order_id' => 'ORD-TIDAK-DIKENAL']);

        $this->postJson(route('webhook.midtrans'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'order not found, ignored']);

        $this->assertDatabaseHas('payment_events', ['order_id' => 'ORD-TIDAK-DIKENAL']);
        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_request_without_a_body_is_rejected(): void
    {
        $this->postJson(route('webhook.midtrans'), [])->assertStatus(403);
    }

    public function test_the_webhook_route_is_registered(): void
    {
        $this->assertTrue(route('webhook.midtrans') !== null);
        $this->assertSame('POST', request()->create(route('webhook.midtrans'), 'POST')->getMethod());
    }
}