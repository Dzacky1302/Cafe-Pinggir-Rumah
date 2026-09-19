<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Services\OrderStatusService;
use Tests\TestCase;

/**
 * Pemetaan notifikasi Midtrans -> status pesanan.
 *
 * Daftar transaction_status mengikuti dokumentasi resmi Midtrans:
 * capture, settlement, pending, deny, cancel, expire, failure,
 * refund, partial_refund, authorize.
 */
class MidtransStatusMappingTest extends TestCase
{
    private OrderStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderStatusService::class);
    }

    public function test_successful_transactions_map_to_paid_only_when_fds_is_not_challenging(): void
    {
        $this->assertSame(OrderStatus::PAID, $this->service->resolveStatus('settlement', 'accept'));
        $this->assertSame(OrderStatus::PAID, $this->service->resolveStatus('capture', 'accept'));

        // Tanpa info fraud_status dari FDS, transaksi yang sudah capture/settlement
        // tetap dianggap lunas (fraud_status hanya dikirim FDS pada transaksi kartu).
        $this->assertSame(OrderStatus::PAID, $this->service->resolveStatus('settlement', null));
        $this->assertSame(OrderStatus::PAID, $this->service->resolveStatus('capture', null));
    }

    public function test_challenged_transactions_stay_pending(): void
    {
        $this->assertSame(OrderStatus::PENDING, $this->service->resolveStatus('capture', 'challenge'));
        $this->assertSame(OrderStatus::PENDING, $this->service->resolveStatus('settlement', 'challenge'));
    }

    public function test_denied_transactions_are_cancelled(): void
    {
        $this->assertSame(OrderStatus::CANCELLED, $this->service->resolveStatus('capture', 'deny'));
    }

    public function test_pending_transaction_maps_to_pending(): void
    {
        $this->assertSame(OrderStatus::PENDING, $this->service->resolveStatus('pending', null));
    }

    public function test_failed_transactions_are_cancelled(): void
    {
        foreach (['deny', 'cancel', 'failure'] as $status) {
            $this->assertSame(
                OrderStatus::CANCELLED,
                $this->service->resolveStatus($status, null),
                "transaction_status '{$status}' seharusnya membatalkan pesanan."
            );
        }
    }

    public function test_expire_maps_to_expired(): void
    {
        $this->assertSame(OrderStatus::EXPIRED, $this->service->resolveStatus('expire', null));
    }

    public function test_refunds_map_to_refunded(): void
    {
        $this->assertSame(OrderStatus::REFUNDED, $this->service->resolveStatus('refund', null));
        $this->assertSame(OrderStatus::REFUNDED, $this->service->resolveStatus('partial_refund', null));
    }

    public function test_unknown_or_missing_status_maps_to_null(): void
    {
        $this->assertNull($this->service->resolveStatus(null, null));
        $this->assertNull($this->service->resolveStatus('', null));
        $this->assertNull($this->service->resolveStatus('status_yang_belum_pernah_ada', null));

        // 'authorize' hanya muncul pada fitur pre-authorize kartu yang tidak
        // dipakai di sini, jadi sengaja TIDAK dipetakan ke paid.
        $this->assertNull($this->service->resolveStatus('authorize', null));
    }
}
