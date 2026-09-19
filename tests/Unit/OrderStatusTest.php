<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

/**
 * Peta transisi status — murni, tanpa database.
 */
class OrderStatusTest extends TestCase
{
    public function test_pending_can_move_to_paid_cancelled_or_expired(): void
    {
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PENDING, OrderStatus::PAID));
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PENDING, OrderStatus::CANCELLED));
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PENDING, OrderStatus::EXPIRED));
    }

    public function test_paid_cannot_be_downgraded_back_to_pending(): void
    {
        $this->assertFalse(OrderStatus::canTransition(OrderStatus::PAID, OrderStatus::PENDING));
    }

    public function test_transition_to_the_same_status_is_never_allowed(): void
    {
        foreach (OrderStatus::all() as $status) {
            $this->assertFalse(
                OrderStatus::canTransition($status, $status),
                "Transisi {$status} -> {$status} harus ditolak; inilah gerbang idempotency webhook."
            );
        }
    }

    public function test_terminal_statuses_have_no_outgoing_transition(): void
    {
        $terminal = [OrderStatus::CANCELLED, OrderStatus::EXPIRED, OrderStatus::REFUNDED];

        foreach ($terminal as $from) {
            foreach (OrderStatus::all() as $to) {
                $this->assertFalse(
                    OrderStatus::canTransition($from, $to),
                    "Status {$from} seharusnya terminal."
                );
            }
        }
    }

    public function test_done_can_only_be_refunded(): void
    {
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::DONE, OrderStatus::REFUNDED));
        $this->assertFalse(OrderStatus::canTransition(OrderStatus::DONE, OrderStatus::PAID));
        $this->assertFalse(OrderStatus::canTransition(OrderStatus::DONE, OrderStatus::PENDING));
    }

    public function test_paid_can_be_processed_refunded_or_cancelled(): void
    {
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PAID, OrderStatus::PROCESSING));
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PAID, OrderStatus::REFUNDED));
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PAID, OrderStatus::CANCELLED));
    }

    public function test_processing_can_be_done_or_cancelled(): void
    {
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PROCESSING, OrderStatus::DONE));
        $this->assertTrue(OrderStatus::canTransition(OrderStatus::PROCESSING, OrderStatus::CANCELLED));
    }

    public function test_an_unknown_status_has_no_transitions(): void
    {
        $this->assertFalse(OrderStatus::canTransition('status_yang_tidak_ada', OrderStatus::PAID));
    }

    public function test_all_lists_every_status_exactly_once(): void
    {
        $this->assertCount(7, OrderStatus::all());
        $this->assertSame(OrderStatus::all(), array_values(array_unique(OrderStatus::all())));
    }
}
