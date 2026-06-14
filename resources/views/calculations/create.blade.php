@extends('layouts.admin')

@section('title', 'Kalkulator Hadley-Within')
@section('header_title', 'Kalkulator Hadley-Within')
@section('header_subtitle', 'Tentukan kebijakan persediaan optimal (R,s,S) untuk produk pilihan')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <span>Form Input Parameter Hadley-Within (R,s,S)</span>
            </div>
            <div class="card-body">
                <form action="{{ route('calculations.store') }}" method="POST" id="calculator-form">
                    @csrf
                    
                    <h5 class="fw-bold text-primary mb-3">1. Informasi Produk</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="product_id" class="form-label fw-semibold text-dark">Pilih Produk</label>
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" onchange="updateProductDetails()" required>
                                <option value="">-- Pilih Produk Master --</option>
                                @foreach($products as $product)
                                    @php $stats = $product->getDemandStats(); @endphp
                                    <option value="{{ $product->id }}" 
                                            data-name="{{ $product->nama_produk }}" 
                                            data-price="{{ $product->harga_produk }}"
                                            data-d-yearly="{{ $stats['avg_yearly'] }}"
                                            data-sigma-yearly="{{ $stats['std_yearly'] }}"
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->kode_produk }} - {{ $product->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-dark">Nama Produk</label>
                            <input type="text" id="nama_produk_display" class="form-control bg-light" placeholder="Otomatis terisi..." readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-dark">Harga Produk per Bal (v) - Otomatis</label>
                            <input type="text" id="v_display" class="form-control bg-light fw-bold text-primary" placeholder="Rp 0..." readonly>
                            <div class="form-text">Nilai v diambil dari database master produk.</div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    <h5 class="fw-bold text-primary mb-3">2. Parameter Permintaan & Periode Waktu</h5>
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label for="d" class="form-label fw-semibold text-dark">Permintaan Rata-rata (D)</label>
                            <input type="number" step="0.0001" min="0.0001" name="d" id="d" class="form-control @error('d') is-invalid @enderror" value="{{ old('d') }}" placeholder="Masukkan nilai D..." required>
                            @error('d')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unit per periode waktu (misal: bal per tahun).</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label fw-semibold text-dark">Tanggal Mulai Periode</label>
                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" onchange="calculateRPeriodDays()" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Awal tinjauan stok.</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label fw-semibold text-dark">Tanggal Akhir Periode</label>
                            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" onchange="calculateRPeriodDays()" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Akhir tinjauan stok.</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold text-dark">Lead Time (L) - Fixed</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="0.0190" readonly>
                            <div class="form-text">Waktu tunggu pengiriman konstan (7 hari).</div>
                        </div>

                        <div class="col-12 mt-1">
                            <div class="alert alert-secondary py-2 border-0 bg-light text-dark d-none" id="r_period_info_alert" style="font-size: 13px;">
                                <i class="bi bi-info-circle me-1 text-primary"></i> 
                                Review Period (R) dihitung secara otomatis: <strong id="r_period_days_text">0</strong> hari 
                                (&approx; <strong id="r_period_years_text">0.0000</strong> tahun)
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    <h5 class="fw-bold text-primary mb-3">3. Parameter Biaya (Fixed / Konstan)</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-dark">Biaya Pemesanan per Transaksi (A)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control bg-light fw-bold text-secondary" value="157.947,92" readonly>
                            </div>
                            <div class="form-text">Biaya pesan konstan per transaksi.</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-dark">Biaya Penyimpanan per Bal (h)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control bg-light fw-bold text-secondary" value="27.545,96" readonly>
                            </div>
                            <div class="form-text">Biaya simpan konstan per bal per tahun.</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-dark">Biaya Kekurangan Persediaan (p)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" id="p_display" class="form-control bg-light fw-bold text-danger" placeholder="Otomatis..." readonly>
                            </div>
                            <div class="form-text">Dihitung otomatis: 10% dari harga barang (v).</div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    <h5 class="fw-bold text-primary mb-3">4. Parameter Ketidakpastian</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="sigma" class="form-label fw-semibold text-dark">Standar Deviasi Permintaan (σ)</label>
                            <input type="number" step="0.0001" min="0.0001" name="sigma" id="sigma" class="form-control @error('sigma') is-invalid @enderror" value="{{ old('sigma') }}" placeholder="Masukkan nilai σ..." required>
                            @error('sigma')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Variabilitas permintaan selama R+L.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-outline-secondary px-4" style="border-radius: 10px;" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary px-5 d-flex align-items-center gap-2">
                            <i class="bi bi-cpu-fill"></i>
                            <span>Hitung & Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateProductDetails() {
        const select = document.getElementById('product_id');
        const selectedOption = select.options[select.selectedIndex];
        
        const nameDisplay = document.getElementById('nama_produk_display');
        const priceDisplay = document.getElementById('v_display');
        const pDisplay = document.getElementById('p_display');
        const dInput = document.getElementById('d');
        const sigmaInput = document.getElementById('sigma');
        
        if (selectedOption && selectedOption.value !== "") {
            const name = selectedOption.getAttribute('data-name');
            const price = parseFloat(selectedOption.getAttribute('data-price'));
            const dVal = selectedOption.getAttribute('data-d-yearly');
            const sigmaVal = selectedOption.getAttribute('data-sigma-yearly');
            
            nameDisplay.value = name;
            priceDisplay.value = 'Rp ' + price.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            pDisplay.value = 'Rp ' + (price * 0.10).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            if (dVal && dInput) dInput.value = dVal;
            if (sigmaVal && sigmaInput) sigmaInput.value = sigmaVal;
        } else {
            nameDisplay.value = '';
            priceDisplay.value = '';
            pDisplay.value = '';
            if (dInput) dInput.value = '';
            if (sigmaInput) sigmaInput.value = '';
        }
    }

    function calculateRPeriodDays() {
        const startVal = document.getElementById('start_date').value;
        const endVal = document.getElementById('end_date').value;
        const alertBox = document.getElementById('r_period_info_alert');
        const daysSpan = document.getElementById('r_period_days_text');
        const yearsSpan = document.getElementById('r_period_years_text');

        if (startVal && endVal) {
            const start = new Date(startVal);
            const end = new Date(endVal);
            const diffTime = end - start;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                alertBox.classList.remove('d-none');
                daysSpan.textContent = diffDays;
                yearsSpan.textContent = (diffDays / 365).toFixed(4);
            } else {
                alertBox.classList.add('d-none');
            }
        } else {
            alertBox.classList.add('d-none');
        }
    }

    function resetForm() {
        setTimeout(function() {
            updateProductDetails();
            calculateRPeriodDays();
        }, 50);
    }

    // Run once on load to populate if old input exists
    document.addEventListener("DOMContentLoaded", function () {
        updateProductDetails();
        calculateRPeriodDays();
    });
</script>
@endsection
