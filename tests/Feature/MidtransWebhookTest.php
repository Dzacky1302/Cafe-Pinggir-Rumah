<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur normal webhook Midtrans.
 */
class MidtransWebhookTest extends TestCase
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
        return number_format((int) $order->total, 2, '.', '');
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
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'tx-1',
            'settlement_time' => '2026-09-19 10:30:00',
        ], $overrides);

        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].self::SERVER_KEY);

        return $payload;
    }

    public function test_a_signed_settlement_notification_marks_the_order_paid(): void
    {
        $order = Order::factory()->pending()->create();

        $this->postJson(route('webhook.midtrans'), $this->notification($order))
            ->assertOk()
            ->assertJson(['message' => 'order updated']);

        $order->refresh();

        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertSame('bank_transfer', $order->payment_type);
        $this->assertSame('Midtrans', $order->payment_method);
        $this->assertSame('tx-1', $order->midtrans_transaction_id);
        $this->assertNotNull($order->paid_at);

        $event = PaymentEvent::query()->where('order_id', $order->order_id)->firstOrFail();

        $this->assertTrue($event->signature_valid);
        $this->assertSame('settlement', $event->transaction_status);
        $this->assertSame('bank_transfer', $event->payment_type);
        $this->assertTrue($event->payload['fraud_status'] === 'accept');
    }

    public function test_the_order_is_paid_only_once_even_when_the_notification_is_replayed(): void
    {
        $order = Order::factory()->pending()->create();
        $payload = $this->notification($order);

        $this->postJson(route('webhook.midtrans'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'order updated']);

        $firstPaidAt = $order->fresh()->paid_at->format('Y-m-d H:i:s');

        $this->postJson(route('webhook.midtrans'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'no change']);

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
        $this->assertSame($firstPaidAt, $order->fresh()->paid_at->format('Y-m-d H:i:s'));

        // Kedua notifikasi tetap tercatat sebagai bukti audit.
        $this->assertSame(2, PaymentEvent::query()->where('order_id', $order->order_id)->count());
    }

    public function test_a_challenged_capture_keeps_the_order_pending(): void
    {
        $order = Order::factory()->pending()->create();

        $this->postJson(route('webhook.midtrans'), $this->notification($order, [
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]))
            ->assertOk()
            ->assertJson(['message' => 'no change']);

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_an_expire_notification_marks_the_order_expired(): void
    {
        $order = Order::factory()->pending()->create();

        $this->postJson(route('webhook.midtrans'), $this->notification($order, [
            'transaction_status' => 'expire',
        ]))
            ->assertOk()
            ->assertJson(['message' => 'order updated']);

        $this->assertSame(OrderStatus::EXPIRED, $order->fresh()->status);
    }
}
