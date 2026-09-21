<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use RuntimeException;

final class PaymentController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    public function show(Order $order)
    {
        $this->assertOrderBelongsToThisSession($order);

        return $this->paymentView($order);
    }

    public function charge(Order $order)
    {
        $this->assertOrderBelongsToThisSession($order);

        try {
            $snap = $this->midtrans->createSnapToken($order);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $order->update(['midtrans_token' => $snap['token']]);

        return $this->paymentView($order, [
            'token' => $snap['token'],
            'redirect_url' => $snap['redirect_url'],
            'order_id' => $order->order_id,
            'gross_amount' => (int) $order->total,
        ]);
    }

    /**
     * Halaman status pesanan — SENGAJA hanya baca.
     *
     * Tidak ada perubahan status di sini. Status pembayaran HANYA berubah lewat
     * POST /webhook/midtrans setelah signature notifikasi terverifikasi.
     * Lihat tests/Feature/CheckoutSuccessIsReadOnlyTest.php.
     */
    public function status(Order $order)
    {
        session()->forget(['cart', 'order_type', 'current_order_id']);

        return view('checkout.success', ['order' => $order->fresh()]);
    }

    /**
     * Tanpa penjagaan ini, siapa pun yang menebak order_id bisa melihat
     * nama, nomor HP, dan nomor meja pelanggan lain — dan membuat token
     * pembayaran atas nama mereka.
     *
     * Trade-off yang disadari: pelanggan yang berpindah browser/clear cookie
     * kehilangan akses ke halaman pembayarannya. Alternatif yang lebih kuat
     * adalah signed URL, direncanakan pada Fase 2.
     */
    private function assertOrderBelongsToThisSession(Order $order): void
    {
        abort_unless(
            session('current_order_id') === $order->order_id,
            403,
            'Pesanan ini tidak terkait dengan sesi Anda.'
        );
    }

    /**
     * @param  array<string, mixed>|null  $midtrans
     */
    private function paymentView(Order $order, ?array $midtrans = null)
    {
        return view('payment', [
            'order' => $order,
            'cart' => $order->items,
            'total' => $order->total,
            'midtrans' => $midtrans,
        ]);
    }
}
