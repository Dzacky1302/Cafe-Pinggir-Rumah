<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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

    /**
     * Koneksi gagal ke Midtrans (cURL error 28 / timeout) harus tampil sebagai
     * flash error yang ramah di halaman pembayaran — bukan halaman exception
     * mentah. ConnectionException extends HttpClientException extends Exception,
     * sehingga TIDAK tertangkap oleh catch (RuntimeException) yang sudah ada.
     */
    public function test_a_midtrans_connection_failure_shows_a_friendly_flash_instead_of_an_exception_page(): void
    {
        config(['services.midtrans.server_key' => 'SB-Midtest-key']);

        Http::fake(function (): never {
            throw new ConnectionException(
                'cURL error 28: Failed to connect to app.sandbox.midtrans.com port 443 after 10003 ms: Timeout was reached'
            );
        });

        $order = Order::factory()->pending()->create();

        $this->withSession(['current_order_id' => $order->order_id])
            ->post(route('checkout.charge', $order->order_id))
            ->assertRedirect()
            ->assertSessionHas('error', function (string $message): bool {
                return str_contains($message, 'Koneksi ke server pembayaran gagal');
            });
    }
}
