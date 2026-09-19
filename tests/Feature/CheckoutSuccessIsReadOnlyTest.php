<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk celah integritas pembayaran.
 *
 * CoffeeController::success() menandai pesanan 'pending' menjadi 'paid'
 * hanya karena ada request GET ke /checkout/success/{order} — tanpa
 * verifikasi apa pun dari Midtrans. Siapa pun yang mengetahui atau menebak
 * order_id (format ORD-ymd-XXXXX) bisa melunasi pesanan tanpa membayar.
 *
 * Test ini HARUS MERAH sebelum perbaikan dan HIJAU setelahnya.
 */
class CheckoutSuccessIsReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_success_page_does_not_mark_a_pending_order_as_paid(): void
    {
        $order = Order::factory()->pending()->create();

        $this->get(route('checkout.success', $order->order_id))->assertOk();

        $this->assertSame(
            OrderStatus::PENDING,
            $order->fresh()->status,
            'Halaman sukses tidak boleh mengubah status pesanan menjadi paid.'
        );
    }

    public function test_visiting_success_page_repeatedly_does_not_change_a_paid_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PAID]);

        $this->get(route('checkout.success', $order->order_id))->assertOk();
        $this->get(route('checkout.success', $order->order_id))->assertOk();

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_success_page_never_records_a_payment_method_without_a_real_payment(): void
    {
        $order = Order::factory()->pending()->create();

        $this->get(route('checkout.success', $order->order_id))->assertOk();

        $this->assertNull(
            $order->fresh()->payment_method,
            'Metode pembayaran hanya boleh diisi dari notifikasi Midtrans yang terverifikasi.'
        );
    }
}
