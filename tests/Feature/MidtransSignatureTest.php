<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\MidtransService;
use Tests\TestCase;

/**
 * Verifikasi signature notifikasi Midtrans.
 *
 * Tidak menyentuh database — murni perhitungan hash.
 */
class MidtransSignatureTest extends TestCase
{
    private const SERVER_KEY = 'SB-Mid-server-TESTKEY';

    private MidtransService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.midtrans.server_key' => self::SERVER_KEY]);

        $this->service = app(MidtransService::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $payload = array_merge([
            'order_id' => 'ORD-260919-ABCDE',
            'status_code' => '200',
            'gross_amount' => '36000.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ], $overrides);

        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].self::SERVER_KEY);

        return $payload;
    }

    public function test_a_correctly_signed_payload_is_accepted(): void
    {
        $this->assertTrue($this->service->verifySignature($this->payload()));
    }

    public function test_gross_amount_is_hashed_as_the_exact_string_midtrans_sends(): void
    {
        $this->assertTrue($this->service->verifySignature($this->payload(['gross_amount' => '36000.00'])));

        // Jebakan utama integrasi ini: Midtrans menandatangani string "36000.00".
        // Bila nilai itu diperlakukan sebagai angka di sisi kita, representasi
        // string-nya berubah menjadi "36000" sehingga hash tidak pernah cocok
        // dan SELURUH notifikasi ditolak. Test ini menjaga nilai tersebut
        // tidak pernah di-cast ke numerik.
        $payload = $this->payload(['gross_amount' => '36000.00']);
        $payload['gross_amount'] = 36000.0;

        $this->assertFalse($this->service->verifySignature($payload));
    }

    public function test_a_tampered_gross_amount_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['gross_amount'] = '1.00';

        $this->assertFalse($this->service->verifySignature($payload));
    }

    public function test_a_tampered_status_code_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['status_code'] = '201';

        $this->assertFalse($this->service->verifySignature($payload));
    }

    public function test_a_tampered_order_id_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['order_id'] = 'ORD-LAIN';

        $this->assertFalse($this->service->verifySignature($payload));
    }

    public function test_a_signature_produced_with_another_server_key_is_rejected(): void
    {
        $payload = $this->payload();

        config(['services.midtrans.server_key' => 'SB-Mid-server-KEY-LAIN']);

        $this->assertFalse($this->service->verifySignature($payload));
    }

    public function test_a_payload_without_a_signature_is_rejected(): void
    {
        $payload = $this->payload();
        unset($payload['signature_key']);

        $this->assertFalse($this->service->verifySignature($payload));
        $this->assertFalse($this->service->verifySignature([]));
        $this->assertFalse($this->service->verifySignature(['signature_key' => '']));
        $this->assertFalse($this->service->verifySignature(['signature_key' => 12345]));
    }

    public function test_the_snap_script_url_follows_the_configured_environment(): void
    {
        config(['services.midtrans.is_production' => false]);
        $this->assertSame('https://app.sandbox.midtrans.com/snap/snap.js', $this->service->snapScriptUrl());

        config(['services.midtrans.is_production' => true]);
        $this->assertSame('https://app.midtrans.com/snap/snap.js', $this->service->snapScriptUrl());
    }

    public function test_gross_amount_matching_converts_rupiah_strings_back_to_the_stored_scale(): void
    {
        $order = new Order(['total' => 36000]);

        $this->assertTrue($this->service->grossAmountMatches($order, '36000.00'));
        $this->assertFalse($this->service->grossAmountMatches($order, '35000.00'));
        $this->assertFalse($this->service->grossAmountMatches($order, 'bukan angka'));
        $this->assertFalse($this->service->grossAmountMatches($order, ''));
    }
}
