<div class="midtrans-box">
    <h3><i class="fa-solid fa-circle-check"></i> Transaksi Berhasil Dibuat</h3>
    <p><strong>Order ID:</strong> {{ $midtrans['order_id'] ?? '-' }}</p>
    <p><strong>Total:</strong> Rp {{ number_format($midtrans['gross_amount'] ?? 0,0,',','.') }}</p>

    @if(!empty($midtrans['redirect_url']))
        <p style="margin-top:.75rem">
            <a class="btn small" href="{{ $midtrans['redirect_url'] }}" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Halaman Midtrans
            </a>
        </p>
    @endif

    @if(!empty($midtrans['token']))
        <p style="margin-top:.5rem;font-size:.85rem;color:var(--text-muted)">
            <i class="fa-solid fa-hourglass-half"></i> Popup pembayaran akan muncul otomatis…
        </p>
        @php $midtransService = app(\App\Services\MidtransService::class); @endphp
        <script src="{{ $midtransService->snapScriptUrl() }}"
                data-client-key="{{ $midtransService->clientKey() }}"></script>
        <script>
            (function(){
                const snapToken = @json($midtrans['token']);
                const successUrl = @json(route('checkout.success', $order->order_id));
                function go(){ window.location.href = successUrl; }
                if (snapToken) {
                    window.addEventListener('load', function(){
                        if (!window.snap) { alert('Snap.js belum termuat.'); return; }
                        window.snap.pay(snapToken, {
                            onSuccess: function(r){ console.log(r); go(); },
                            onPending: function(r){ console.log(r); go(); },
                            onError:   function(r){ console.log(r); alert('Terjadi kesalahan pembayaran.'); },
                            onClose:   function(){ console.log('popup closed'); }
                        });
                    });
                }
            })();
        </script>
    @endif
</div>
