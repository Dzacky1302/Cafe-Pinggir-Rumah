<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\MidtransService;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Penerima notifikasi (webhook) Midtrans.
 *
 * Inilah SATU-SATUNYA jalur yang boleh mengubah status pembayaran pesanan.
 * Halaman sukses tidak lagi dipercaya, karena halaman itu hanya bukti bahwa
 * pelanggan kembali ke situs — bukan bukti uang sudah diterima.
 */
final class MidtransWebhookController extends Controller
{
    public function handle(
        Request $request,
        MidtransService $midtrans,
        OrderStatusService $statuses,
    ): JsonResponse {
        $payload = $request->json()->all() ?: (json_decode((string) $request->getContent(), true) ?: []);

        $signatureValid = $midtrans->verifySignature($payload);

        // Selalu dicatat — termasuk notifikasi ber-signature tidak valid,
        // karena justru itulah yang perlu terlihat saat investigasi.
        PaymentEvent::create([
            'order_id' => (string) ($payload['order_id'] ?? ''),
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_status' => (string) ($payload['transaction_status'] ?? 'unknown'),
            'fraud_status' => $payload['fraud_status'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'gross_amount' => (string) ($payload['gross_amount'] ?? ''),
            'signature_valid' => $signatureValid,
            'payload' => $payload,
        ]);

        if (! $signatureValid) {
            Log::warning('Notifikasi Midtrans ditolak: signature tidak valid.', [
                'order_id' => $payload['order_id'] ?? null,
                'transaction_status' => $payload['transaction_status'] ?? null,
            ]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $order = Order::query()->where('order_id', (string) ($payload['order_id'] ?? ''))->first();

        if ($order === null) {
            Log::warning('Notifikasi Midtrans untuk pesanan yang tidak dikenal.', [
                'order_id' => $payload['order_id'] ?? null,
            ]);

            // Dijawab 200 supaya Midtrans berhenti mengirim ulang — pengiriman
            // ulang tidak akan membuat pesanan itu ada.
            return response()->json(['message' => 'order not found, ignored']);
        }

        if (! $midtrans->grossAmountMatches($order, (string) ($payload['gross_amount'] ?? ''))) {
            Log::warning('Notifikasi Midtrans ditolak: nominal tidak cocok.', [
                'order_id' => $order->order_id,
                'gross_amount' => $payload['gross_amount'] ?? null,
                'expected' => intdiv((int) $order->total, 100),
            ]);

            return response()->json(['message' => 'amount mismatch'], 422);
        }

        $changed = $statuses->applyMidtransStatus($order, $payload);

        return response()->json([
            'message' => $changed ? 'order updated' : 'no change',
            'order_id' => $order->order_id,
        ]);
    }
}
