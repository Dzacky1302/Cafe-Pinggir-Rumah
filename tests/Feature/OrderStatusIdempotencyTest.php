<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Idempotency pemrosesan notifikasi.
 *
 * Midtrans mengirim ulang notifikasi yang belum dijawab sukses, jadi
 * notifikasi yang sama bisa diterima berkali-kali dan TIDAK boleh
 * mengubah data lebih dari sekali.
 */
class OrderStatusIdempotencyTest extends TestCase
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
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'tx-123',
            'settlement_time' => '2026-09-19 10:30:00',
        ], $overrides);
    }

    public function test_a_repeated_settlement_notification_is_a_no_op(): void
    {
        $order = Order::factory()->pending()->create();
        $payload = $this->settlement();

        $this->assertTrue($this->service->applyMidtransStatus($order, $payload));
        $this->assertFalse($this->service->applyMidtransStatus($order, $payload));
        $this->assertFalse($this->service->applyMidtransStatus($order->fresh(), $payload));

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_idempotency_keeps_the_original_payment_time(): void
    {
        $order = Order::factory()->pending()->create();

        $this->service->applyMidtransStatus($order, $this->settlement());
        $firstPaidAt = $order->fresh()->paid_at->format('Y-m-d H:i:s');

        $this->service->applyMidtransStatus($order->fresh(), $this->settlement([
            'settlement_time' => '2026-09-20 23:59:00',
        ]));

        $this->assertSame($firstPaidAt, $order->fresh()->paid_at->format('Y-m-d H:i:s'));
    }

    public function test_an_unknown_transaction_status_changes_nothing(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertFalse($this->service->applyMidtransStatus($order, [
            'transaction_status' => 'status_yang_belum_pernah_ada',
        ]));

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_pending_notification_never_overwrites_a_paid_order(): void
    {
        $order = Order::factory()->paid()->create();

        $this->assertFalse($this->service->applyMidtransStatus($order, [
            'transaction_status' => 'pending',
        ]));

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_a_settlement_after_expiry_does_not_revive_the_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::EXPIRED]);

        $this->assertFalse($this->service->applyMidtransStatus($order, $this->settlement()));

        $this->assertSame(OrderStatus::EXPIRED, $order->fresh()->status);
    }

    public function test_a_cancelled_order_can_never_become_paid(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::CANCELLED]);

        $this->assertFalse($this->service->applyMidtransStatus($order, $this->settlement()));

        $this->assertSame(OrderStatus::CANCELLED, $order->fresh()->status);
    }

    public function test_an_empty_payload_changes_nothing(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertFalse($this->service->applyMidtransStatus($order, []));

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
    }
}
