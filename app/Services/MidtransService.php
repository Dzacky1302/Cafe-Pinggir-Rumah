<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Satu-satunya tempat aplikasi berbicara dengan API Midtrans.
 *
 * Memakai HTTP client Laravel (bukan SDK) dengan alasan yang disengaja:
 *  - bisa diuji sepenuhnya dengan Http::fake() tanpa memanggil jaringan;
 *  - tidak memakai state global seperti \Midtrans\Config::$serverKey yang
 *    berbahaya pada aplikasi multi-tenant/async;
 *  - hanya satu endpoint (Snap) yang dibutuhkan, jadi SDK tidak memberi manfaat.
 */
final class MidtransService
{
    public function serverKey(): string
    {
        return trim((string) config('services.midtrans.server_key'));
    }

    public function clientKey(): string
    {
        return (string) config('services.midtrans.client_key');
    }

    public function isProduction(): bool
    {
        return filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);
    }

    public function baseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    /**
     * URL snap.js sesuai lingkungan.
     *
     * Sebelumnya URL sandbox ditulis langsung di partials/midtrans-box.blade.php,
     * sehingga di production popup pembayaran tetap memakai sandbox.
     */
    public function snapScriptUrl(): string
    {
        return $this->baseUrl().'/snap/snap.js';
    }

    /**
     * Membuat Snap token untuk sebuah pesanan.
     *
     * @return array{token: ?string, redirect_url: ?string}
     *
     * @throws RuntimeException bila server key belum diisi atau Midtrans menolak
     */
    public function createSnapToken(Order $order): array
    {
        if ($this->serverKey() === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->asJson()
            ->acceptJson()
            // connectTimeout: Guzzle default hanya 10 detik untuk fase koneksi.
            // Pernah gagal nyata dengan "cURL error 28 ... after 10003 ms" saat
            // jaringan sedang lambat; dinaikkan agar blip singkat tidak langsung
            // melempar pengguna ke halaman error.
            ->connectTimeout(20)
            ->timeout(30)
            ->post($this->baseUrl().'/snap/v1/transactions', $this->payloadFor($order));

        if (! $response->successful()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $data = $response->json();

        return [
            'token' => $data['token'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
        ];
    }

    /**
     * Payload transaksi Snap untuk sebuah pesanan.
     *
     * @return array<string, mixed>
     */
    public function payloadFor(Order $order): array
    {
        $items = [];

        foreach ($order->items ?? [] as $item) {
            $items[] = [
                'id' => (string) $item['id'],
                'price' => (int) $item['price'],
                'quantity' => (int) $item['qty'],
                'name' => Str::limit((string) $item['name'], 50, ''),
            ];
        }

        $parts = explode(' ', trim((string) $order->customer_name), 2);

        return [
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $parts[0] !== '' ? $parts[0] : 'Pelanggan',
                'last_name' => $parts[1] ?? '',
                'phone' => (string) ($order->phone ?? ''),
            ],
        ];
    }

    /**
     * Memverifikasi signature_key notifikasi Midtrans.
     *
     * Rumus resmi: SHA512(order_id + status_code + gross_amount + ServerKey).
     *
     * PENTING: gross_amount dipakai sebagai STRING apa adanya ("18000.00").
     * Mengubahnya ke angka akan mengubah representasinya menjadi "18000"
     * sehingga hash tidak pernah cocok dan seluruh notifikasi ditolak.
     */
    public function verifySignature(array $payload): bool
    {
        $signature = $payload['signature_key'] ?? null;

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash('sha512',
            (string) ($payload['order_id'] ?? '')
            .(string) ($payload['status_code'] ?? '')
            .(string) ($payload['gross_amount'] ?? '')
            .$this->serverKey()
        );

        // hash_equals agar perbandingan tidak bocor lewat waktu eksekusi.
        return hash_equals($expected, $signature);
    }

    /**
     * Pertahanan berlapis: setelah signature valid, pastikan nominal yang
     * dibayar memang sama dengan total pesanan di database.
     *
     * Total pesanan disimpan dalam rupiah (integer).
     */
    public function grossAmountMatches(Order $order, string $grossAmount): bool
    {
        if (! is_numeric($grossAmount)) {
            return false;
        }

        return (int) round((float) $grossAmount) === (int) $order->total;
    }
}
