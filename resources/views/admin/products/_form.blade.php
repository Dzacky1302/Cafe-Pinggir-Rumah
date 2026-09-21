@csrf
@if(($product ?? null))@method('PUT')@endif

@if($errors->any())
    <div class="alert alert-error">
        <div>
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Ada kesalahan:</strong>
            <ul style="margin:.4rem 0 0;padding-left:1.2rem">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div class="form-single">

    {{-- ── SECTION: Gambar Produk ── --}}
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa-solid fa-image"></i> Gambar Produk
        </div>

        {{-- Preview area — klik untuk pilih file --}}
        <div class="img-upload-wrap">
            <div class="img-preview" id="imgPreview" onclick="document.getElementById('imgFileInput').click()">
                @if(!empty($product->image))
                    <img src="{{ $product->image }}" alt="preview">
                @else
                    <div class="img-placeholder">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Klik untuk upload gambar</span>
                        <small>JPG / PNG / WEBP · Maks 2MB</small>
                    </div>
                @endif
                <div class="img-overlay">
                    <i class="fa-solid fa-camera"></i>
                    <span>Ganti Gambar</span>
                </div>
            </div>

            <input type="file"
                   id="imgFileInput"
                   name="image_file"
                   accept="image/*"
                   style="display:none"
                   onchange="previewImg(this)">

            <div class="img-upload-info">
                <i class="fa-solid fa-circle-info"></i>
                <span id="fileNameLabel">Belum ada file dipilih</span>
            </div>
        </div>

        {{-- URL alternatif --}}
        <div class="form-group" style="margin-top:1rem">
            <label>
                <i class="fa-solid fa-link"></i> URL Gambar
                <span class="badge-optional">Opsional</span>
            </label>
            <input type="url"
                   name="image_url"
                   value="{{ old('image_url', ($product->image ?? '')) }}"
                   placeholder="https://images.unsplash.com/...">
            <div class="field-hint">Alternatif jika tidak upload file. Isi salah satu saja.</div>
        </div>
    </div>

    {{-- ── SECTION: Info Produk ── --}}
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa-solid fa-mug-saucer"></i> Info Produk
        </div>

        <div class="form-group">
            <label>Nama Produk <span class="req">*</span></label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $product->name ?? '') }}"
                   placeholder="Contoh: Kopi Susu Aren"
                   required>
        </div>

        <div class="form-row-2">
            <div class="form-group">
                <label>Varian</label>
                <input type="text"
                       name="variant"
                       value="{{ old('variant', $product->variant ?? '') }}"
                       placeholder="hot / iced / regular">
                <div class="field-hint">Kosongkan jika tidak punya varian</div>
            </div>
            <div class="form-group">
                <label>Harga <span class="req">*</span></label>
                <div class="price-input-wrap">
                    <span class="price-prefix">Rp</span>
                    <input type="number"
                           name="price"
                           value="{{ old('price', $product->price ?? '') }}"
                           min="0"
                           step="500"
                           placeholder="25000"
                           required>
                </div>
                <div class="field-hint">Masukkan harga dalam Rupiah. Contoh: <strong>25000</strong> = Rp 25.000</div>
            </div>
        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="form-actions">
        <a href="{{ route('admin.products.index') }}" class="btn secondary">
            <i class="fa-solid fa-xmark"></i> Batal
        </a>
        <button type="submit" class="btn gold">
            <i class="fa-solid fa-check"></i> Simpan Produk
        </button>
    </div>

</div>

@push('scripts')
<script>
function previewImg(input) {
    if (!input.files || !input.files[0]) return;

    const file  = input.files[0];
    const label = document.getElementById('fileNameLabel');
    label.textContent = file.name;

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imgPreview').innerHTML =
            `<img src="${e.target.result}" alt="preview">` +
            `<div class="img-overlay"><i class="fa-solid fa-camera"></i><span>Ganti Gambar</span></div>`;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush

