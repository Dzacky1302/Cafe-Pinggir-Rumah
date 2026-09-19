<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Efek notifikasi Midtrans pada database.
 *
 * Yang diuji di sini adalah perubahan status pesanan — bukan pemanggilan HTTP
 * ke Midtrans (itu tanggung jawab lapisan webhook).
 */
class OrderStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderStatusService::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function settlement(array $overrides = []): array
    {
        return array_merge([
            'order_id' => 'ORD-TEST',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'tx-123',
            'settlement_time' => '2026-09-19 10:30:00',
        ], $overrides);
    }

    public function test_settlement_with_accepted_fraud_status_marks_the_order_paid(): void
    {
        $order = Order::factory()->pending()->create();

        $changed = $this->service->applyMidtransStatus($order, $this->settlement());

        $order->refresh();

        $this->assertTrue($changed);
        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertSame('Midtrans', $order->payment_method);
        $this->assertSame('bank_transfer', $order->payment_type);
        $this->assertSame('accept', $order->fraud_status);
        $this->assertSame('tx-123', $order->midtrans_transaction_id);
        $this->assertSame('2026-09-19 10:30:00', $order->paid_at->format('Y-m-d H:i:s'));
    }

    public function test_paid_at_falls_back_to_now_when_midtrans_sends_no_time(): void
    {
        $order = Order::factory()->pending()->create();

        $this->service->applyMidtransStatus($order, $this->settlement(['settlement_time' => null]));

        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_fraud_status_challenge_keeps_the_order_pending(): void
    {
        $order = Order::factory()->pending()->create();

        $changed = $this->service->applyMidtransStatus($order, $this->settlement([
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
        ]));

        $order->refresh();

        $this->assertFalse($changed);
        $this->assertSame(OrderStatus::PENDING, $order->status);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->payment_method);
    }

    public function test_capture_with_denied_fraud_status_cancels_the_order(): void
    {
        $order = Order::factory()->pending()->create();

        $this->service->applyMidtransStatus($order, $this->settlement([
            'transaction_status' => 'capture',
            'fraud_status' => 'deny',
        ]));

        $this->assertSame(OrderStatus::CANCELLED, $order->fresh()->status);
    }

    public function test_expire_marks_the_order_expired_and_records_when(): void
    {
        $order = Order::factory()->pending()->create();

        $changed = $this->service->applyMidtransStatus($order, ['transaction_status' => 'expire']);

        $order->refresh();

        $this->assertTrue($changed);
        $this->assertSame(OrderStatus::EXPIRED, $order->status);
        $this->assertNotNull($order->expired_at);
    }

    public function test_cancel_marks_the_order_cancelled(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertTrue($this->service->applyMidtransStatus($order, [
            'transaction_status' => 'cancel',
        ]));

        $this->assertSame(OrderStatus::CANCELLED, $order->fresh()->status);
    }

    public function test_refund_of_a_paid_order_marks_it_refunded(): void
    {
        $order = Order::factory()->paid()->create();

        $this->assertTrue($this->service->applyMidtransStatus($order, [
            'transaction_status' => 'refund',
        ]));

        $this->assertSame(OrderStatus::REFUNDED, $order->fresh()->status);
    }
}
