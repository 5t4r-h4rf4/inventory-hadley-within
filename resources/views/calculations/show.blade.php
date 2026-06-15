@extends('layouts.admin')

@section('title', 'Detail Perhitungan')
@section('header_title', 'Detail Perhitungan')
@section('header_subtitle', 'Analisis hasil kalkulasi metode Hadley-Within (R,s,S)')

@section('content')
@php
    $optimalRow = collect($iterations)->firstWhere('status', 'OPTIMAL') ?? [
        'no' => '-',
        'total' => $calculation->ob + $calculation->op + $calculation->os + $calculation->ok,
        'n' => 0,
        't0' => $calculation->r_period,
        'r' => $calculation->reorder_point,
        'alpha' => 0,
        'za' => $calculation->z_value,
        'f_za' => 0,
        'ekspektasi_stockout' => 0,
        'op' => $calculation->ordering_cost / max(0.0001, $calculation->r_period),
        'os' => 0,
        'ok' => 0
    ];
@endphp
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('calculations.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('calculations.print', $calculation->id) }}" target="_blank" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-printer-fill"></i>
                    <span>Cetak Laporan / PDF</span>
                </a>
                <form action="{{ route('calculations.destroy', $calculation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data perhitungan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2">
                        <i class="bi bi-trash3-fill"></i>
                        <span>Hapus Riwayat</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Input parameters -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">Parameter Input (Variabel)</div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0" style="font-size: 14px;">
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary" style="width: 200px;">Produk</td>
                            <td class="fw-bold text-dark">{{ $calculation->product->nama_produk }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Kode Produk</td>
                            <td><span class="badge bg-light text-dark border">{{ $calculation->product->kode_produk }}</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Permintaan Rata-rata (D)</td>
                            <td class="fw-medium">{{ number_format($calculation->d, 4, ',', '.') }} unit/periode</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Periode Awal</td>
                            <td class="fw-medium text-dark">{{ $calculation->start_date ? $calculation->start_date->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Periode Akhir</td>
                            <td class="fw-medium text-dark">{{ $calculation->end_date ? $calculation->end_date->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Review Period (R)</td>
                            <td class="fw-semibold text-primary">
                                {{ round($calculation->r_period * 365) }} hari 
                                <small class="text-muted">(&approx; {{ number_format($calculation->r_period, 4, ',', '.') }} tahun)</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Lead Time (L)</td>
                            <td class="fw-medium">{{ number_format($calculation->lead_time, 4, ',', '.') }} periode</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Harga Produk (v)</td>
                            <td class="fw-bold text-primary">Rp {{ number_format($calculation->item_price, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Biaya Pemesanan (A)</td>
                            <td class="fw-bold">Rp {{ number_format($calculation->ordering_cost, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Biaya Penyimpanan (h)</td>
                            <td class="fw-bold text-secondary">Rp {{ number_format($calculation->holding_cost, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Biaya Kekurangan (p)</td>
                            <td class="fw-bold text-danger">Rp {{ number_format($calculation->shortage_cost, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">Standar Deviasi (σ)</td>
                            <td class="fw-medium">{{ number_format($calculation->sigma, 4, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Calculation results -->
    <div class="col-lg-7 mb-4">
        <div class="card mb-4">
            <div class="card-header bg-light-primary text-primary" style="background-color: #f5f3ff;">Hasil Analisis Hadley-Within</div>
            <div class="card-body">
                <div class="row g-3">
                    
                    <!-- Intermediate parameters -->
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted mb-1" style="font-size: 11px;">Demand Review Period (XR)</div>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($calculation->xr, 4, ',', '.') }}</h5>
                            <small class="text-muted" style="font-size: 10px;">Rumus: D * R</small>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted mb-1" style="font-size: 11px;">Demand Protection Period (XR+L)</div>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($calculation->xr_l, 4, ',', '.') }}</h5>
                            <small class="text-muted" style="font-size: 10px;">Rumus: D * (R + L)</small>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted mb-1" style="font-size: 11px;">Nilai z</div>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($calculation->z_value, 4, ',', '.') }}</h5>
                            <small class="text-muted" style="font-size: 10px;">Rumus: Qp / sqrt(σ<sub>R+L</sub> * Cu)</small>
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="p-3 border border-success-subtle rounded-3" style="background-color: #f0fdf4;">
                            <div class="text-success fw-bold mb-1" style="font-size: 11px;">Reorder Point (s)</div>
                            <h5 class="fw-bold text-success mb-0">{{ number_format($calculation->reorder_point, 4, ',', '.') }}</h5>
                            <small class="text-success" style="font-size: 10px;">Metode: min(Sp, So) jika pu &ge; k, else Sp</small>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted mb-1" style="font-size: 11px;">Ekspektasi Kekurangan (N)</div>
                            <h5 class="fw-bold text-dark mb-0">{{ number_format($optimalRow['n'], 4, ',', '.') }} unit</h5>
                            <small class="text-muted" style="font-size: 10px;">Rumus: &sigma; * &radic;(T + L) * [f(Z_&alpha;) - (Z_&alpha; * &psi;(Z_&alpha;))]</small>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="p-3 border border-danger-subtle rounded-3" style="background-color: #fef2f2;">
                            <div class="text-danger fw-bold mb-1" style="font-size: 11px;">Ekspektasi Stockout (N / T<sub>0</sub>)</div>
                            <h5 class="fw-bold text-danger mb-0">{{ number_format($optimalRow['ekspektasi_stockout'], 4, ',', '.') }} unit/tahun</h5>
                            <small class="text-danger" style="font-size: 10px;">Rata-rata ekspektasi kekurangan per tahun</small>
                        </div>
                    </div>

                    <!-- Core Policy parameters -->
                    <div class="col-md-6">
                        <div class="p-3 border border-primary-subtle rounded-3" style="background-color: #e0e7ff;">
                            <div class="text-primary fw-bold mb-1" style="font-size: 12px;">Pemesanan Optimal (Qp)</div>
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($calculation->qp, 2, ',', '.') }}</h3>
                            <small class="text-muted" style="font-size: 10px;">Ukuran batch pemesanan ekonomis</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border border-indigo-subtle rounded-3" style="background-color: #f5f3ff;">
                            <div class="text-indigo fw-bold mb-1" style="color: #6366f1; font-size: 12px;">Maximum Inventory Level (S)</div>
                            <h3 class="fw-bold mb-0" style="color: #6366f1;">{{ number_format($calculation->max_inventory, 2, ',', '.') }}</h3>
                            <small class="text-muted" style="font-size: 10px;">Rumus: Sp + Qp</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Visual Policy Chart -->
        <div class="card">
            <div class="card-header bg-light">Visualisasi Kebijakan Persediaan (R,s,S)</div>
            <div class="card-body">
                <div style="position: relative; height: 180px; width: 100%;">
                    <canvas id="policyChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card animate-fade-in">
            <div class="card-header bg-light-primary text-primary" style="background-color: #f5f3ff;">Tabel Iterasi Penentuan Kebijakan Persediaan Optimal (Hadley-Whitin)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 text-center" style="font-size: 12.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Iterasi</th>
                                <th>T<sub>0</sub> (Tahun)</th>
                                <th>&alpha;</th>
                                <th>Z<sub>&alpha;</sub></th>
                                <th>f(Z<sub>&alpha;</sub>)</th>
                                <th>&psi;(Z<sub>&alpha;</sub>)</th>
                                <th>R (ROP)</th>
                                <th>N (Ekspektasi Kekurangan)</th>
                                <th>Ekspektasi Stockout (N/T<sub>0</sub>)</th>
                                <th>Ob (Pemesanan)</th>
                                <th>Op (Pesan)</th>
                                <th>Os (Simpan)</th>
                                <th>Ok (Kekurangan)</th>
                                <th>Total Biaya</th>
                                <th>Status Iterasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($iterations as $it)
                                @php
                                    $rowClass = '';
                                    $badgeClass = 'bg-secondary';
                                    $statusLabel = 'LANJUT ITERASI BERIKUTNYA';
                                    
                                    if ($it['status'] === 'OPTIMAL') {
                                        $rowClass = 'table-success fw-bold text-success';
                                        $badgeClass = 'bg-success';
                                        $statusLabel = 'OPTIMAL';
                                    } elseif ($it['status'] === 'TERAKHIR') {
                                        $rowClass = 'table-warning';
                                        $badgeClass = 'bg-warning text-dark';
                                        $statusLabel = 'AMBIL NILAI TERAKHIR';
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>Iterasi {{ $it['no'] }}</td>
                                    <td>{{ number_format($it['t0'], 3, ',', '.') }}</td>
                                    <td>{{ number_format($it['alpha'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($it['za'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($it['f_za'], 4, ',', '.') }}</td>
                                    <td>{{ number_format($it['psi_za'], 3, ',', '.') }}</td>
                                    <td>{{ number_format($it['r'], 3, ',', '.') }}</td>
                                    <td>{{ number_format($it['n'], 2, ',', '.') }}</td>
                                    <td class="fw-semibold text-danger">{{ number_format($it['ekspektasi_stockout'], 2, ',', '.') }} unit/thn</td>
                                    <td>Rp {{ number_format($it['ob'], 0, ',', '.') }}</td>
                                    <td class="{{ $it['op'] < 0 ? 'text-danger' : '' }}">
                                        @if(is_infinite($it['op']))
                                            &infin;
                                        @else
                                            {{ $it['op'] < 0 ? '-' : '' }}Rp {{ number_format(abs($it['op']), 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="{{ $it['os'] < 0 ? 'text-danger' : '' }}">
                                        {{ $it['os'] < 0 ? '-' : '' }}Rp {{ number_format(abs($it['os']), 0, ',', '.') }}
                                    </td>
                                    <td class="{{ $it['ok'] < 0 ? 'text-danger' : '' }}">
                                        @if(is_infinite($it['ok']))
                                            &infin;
                                        @else
                                            {{ $it['ok'] < 0 ? '-' : '' }}Rp {{ number_format(abs($it['ok']), 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_infinite($it['total']))
                                            &infin;
                                        @else
                                            Rp {{ number_format($it['total'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card animate-fade-in">
            <div class="card-header bg-light-primary text-primary" style="background-color: #f5f3ff;">Rincian Langkah Perhitungan Matematika</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th style="width: 100px;" class="text-center">Langkah</th>
                                <th style="width: 320px;">Formula & Deskripsi</th>
                                <th>Substitusi Nilai & Perhitungan</th>
                                <th style="width: 150px;" class="text-end">Hasil Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Step 1 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 1</td>
                                <td>
                                    <strong>Interval Peninjauan Awal (T<sub>0</sub>)</strong><br>
                                    <small class="text-muted">Formula: &radic;((2 * A) / (D * h))</small>
                                </td>
                                <td>
                                    A = Rp {{ number_format($calculation->ordering_cost, 2, ',', '.') }}<br>
                                    D = {{ number_format($calculation->d, 4, ',', '.') }} unit/tahun<br>
                                    h = Rp {{ number_format($calculation->holding_cost, 2, ',', '.') }}<br>
                                    T<sub>0</sub> = &radic;((2 &times; {{ $calculation->ordering_cost }}) / ({{ $calculation->d }} &times; {{ $calculation->holding_cost }}))
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format(sqrt((2 * $calculation->ordering_cost) / ($calculation->d * $calculation->holding_cost)), 4, ',', '.') }} tahun</td>
                            </tr>
                            <!-- Step 2 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 2</td>
                                <td>
                                    <strong>Nilai Alpha (&alpha;)</strong><br>
                                    <small class="text-muted">Formula: (T * h) / Cu</small>
                                </td>
                                <td>
                                    T<sup>*</sup> = {{ number_format($optimalRow['t0'], 4, ',', '.') }} tahun<br>
                                    h = Rp {{ number_format($calculation->holding_cost, 2, ',', '.') }}<br>
                                    Cu = Rp {{ number_format($calculation->shortage_cost, 2, ',', '.') }}<br>
                                    &alpha; = ({{ number_format($optimalRow['t0'], 4, ',', '.') }} &times; {{ $calculation->holding_cost }}) / {{ $calculation->shortage_cost }}
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($optimalRow['alpha'], 4, ',', '.') }}</td>
                            </tr>
                            <!-- Step 3 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 3</td>
                                <td>
                                    <strong>Tingkat Persediaan Maksimum (R)</strong><br>
                                    <small class="text-muted">Formula: D * (T + L) + (Z_&alpha; * &radic;(T + L))</small>
                                </td>
                                <td>
                                    D = {{ number_format($calculation->d, 4, ',', '.') }} unit/tahun<br>
                                    T<sup>*</sup> = {{ number_format($optimalRow['t0'], 4, ',', '.') }} tahun<br>
                                    L = {{ number_format($calculation->lead_time, 4, ',', '.') }} tahun<br>
                                    Z<sub>&alpha;</sub> = {{ number_format($optimalRow['za'], 4, ',', '.') }}<br>
                                    R = {{ number_format($calculation->d, 4, ',', '.') }} &times; ({{ number_format($optimalRow['t0'], 4, ',', '.') }} + {{ $calculation->lead_time }}) + ({{ number_format($optimalRow['za'], 4, ',', '.') }} &times; &radic;({{ number_format($optimalRow['t0'], 4, ',', '.') }} + {{ $calculation->lead_time }}))
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($optimalRow['r'], 4, ',', '.') }} unit</td>
                            </tr>
                            <!-- Step 4 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 4</td>
                                <td>
                                    <strong>Ekspektasi Kekurangan Persediaan (N)</strong><br>
                                    <small class="text-muted">Formula: S_dev * &radic;(T + L) * [f(Z_&alpha;) - (Z_&alpha; * &psi;(Z_&alpha;))]</small>
                                </td>
                                <td>
                                    S_dev (&sigma;) = {{ number_format($calculation->sigma, 4, ',', '.') }}<br>
                                    f(Z<sub>&alpha;</sub>) = {{ number_format($optimalRow['f_za'], 4, ',', '.') }} (PDF)<br>
                                    &psi;(Z<sub>&alpha;</sub>) = {{ number_format($optimalRow['psi_za'], 4, ',', '.') }} (CDF)<br>
                                    N = {{ number_format($calculation->sigma, 4, ',', '.') }} &times; &radic;({{ number_format($optimalRow['t0'], 4, ',', '.') }} + {{ $calculation->lead_time }}) &times; ({{ number_format($optimalRow['f_za'], 4, ',', '.') }} - ({{ number_format($optimalRow['za'], 4, ',', '.') }} &times; {{ number_format($optimalRow['psi_za'], 4, ',', '.') }}))
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($optimalRow['n'], 4, ',', '.') }} unit</td>
                            </tr>
                            <!-- Step 5 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 5</td>
                                <td>
                                    <strong>Parameter Pemesanan Optimal (Qp & Sp)</strong><br>
                                    <small class="text-muted">Formula Qp & Sp Hadley-Within</small>
                                </td>
                                <td>
                                    XR = {{ number_format($optimalRow['xr'], 4, ',', '.') }} | XR+L = {{ number_format($optimalRow['xr_l'], 4, ',', '.') }}<br>
                                    &sigma;<sub>R+L</sub> = {{ number_format($calculation->sigma * sqrt($optimalRow['r'] + $calculation->lead_time), 4, ',', '.') }}<br>
                                    Q<sub>p</sub> = 1.3 &times; XR<sup>0.494</sup> &times; (A/h)<sup>0.506</sup> &times; (1 + &sigma;<sub>R+L</sub><sup>2</sup>/XR<sup>2</sup>)<sup>0.116</sup> = <strong>{{ number_format($optimalRow['qp'], 4, ',', '.') }}</strong><br>
                                    z = Q<sub>p</sub> / &radic;(&sigma;<sub>R+L</sub> &times; Cu) = <strong>{{ number_format($optimalRow['z'], 4, ',', '.') }}</strong><br>
                                    S<sub>p</sub> = 0.973 &times; XR+L + &sigma;<sub>R+L</sub> &times; (0.183/z + 1.063 - 2.192 &times; z)
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    Q<sub>p</sub> = {{ number_format($optimalRow['qp'], 2, ',', '.') }}<br>
                                    S<sub>p</sub> = {{ number_format($optimalRow['sp'], 2, ',', '.') }}
                                </td>
                            </tr>
                            <!-- Step 6 -->
                            <tr>
                                <td class="fw-bold text-center text-muted">Langkah 6</td>
                                <td>
                                    <strong>Menghitung Nilai So</strong><br>
                                    <small class="text-muted">Formula: So = XR+L + Z_&alpha; * &sigma;<sub>R+L</sub></small>
                                </td>
                                <td>
                                    S<sub>o</sub> = {{ number_format($optimalRow['xr_l'], 4, ',', '.') }} + {{ number_format($optimalRow['za'], 4, ',', '.') }} &times; {{ number_format($calculation->sigma * sqrt($optimalRow['r'] + $calculation->lead_time), 4, ',', '.') }} = <strong>{{ number_format($optimalRow['so'], 4, ',', '.') }}</strong><br>
                                    k = h / (Cu + h) = {{ number_format($calculation->holding_cost / ($calculation->shortage_cost + $calculation->holding_cost), 4, ',', '.') }}<br>
                                    pu = 1 - &alpha; = {{ number_format(1.0 - $optimalRow['alpha'], 4, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    S<sub>o</sub> = {{ number_format($optimalRow['so'], 2, ',', '.') }}<br>
                                    pu &ge; k: {{ (1.0 - $optimalRow['alpha']) >= ($calculation->holding_cost / ($calculation->shortage_cost + $calculation->holding_cost)) ? 'YA' : 'TIDAK' }}
                                </td>
                            </tr>
                            <!-- Step 7 -->
                            <tr class="table-success" style="background-color: #f0fdf4;">
                                <td class="fw-bold text-center text-success">Langkah 7</td>
                                <td>
                                    <strong>Kebijakan Persediaan Akhir (s & S)</strong><br>
                                    <small class="text-success">s = min(Sp, So) jika pu &ge; k, else Sp | S = Sp + Qp</small>
                                </td>
                                <td>
                                    s = {{ (1.0 - $optimalRow['alpha']) >= ($calculation->holding_cost / ($calculation->shortage_cost + $calculation->holding_cost)) ? 'min(' . number_format($optimalRow['sp'], 2, ',', '.') . ', ' . number_format($optimalRow['so'], 2, ',', '.') . ')' : 'S_p = ' . number_format($optimalRow['sp'], 2, ',', '.') }}<br>
                                    S = S<sub>p</sub> + Q<sub>p</sub> = {{ number_format($optimalRow['sp'], 4, ',', '.') }} + {{ number_format($optimalRow['qp'], 4, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    s = {{ number_format($optimalRow['s'], 2, ',', '.') }} unit<br>
                                    S = {{ number_format($optimalRow['S'], 2, ',', '.') }} unit
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rop = {{ $calculation->reorder_point }};
        const max = {{ $calculation->max_inventory }};
        const qp = {{ $calculation->qp }};

        new Chart(document.getElementById('policyChart'), {
            type: 'bar',
            data: {
                labels: ['Reorder Point (s)', 'Batch Pemesanan (Qp)', 'Maksimum Persediaan (S)'],
                datasets: [{
                    label: 'Jumlah Unit',
                    data: [rop, qp, max],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)',  // Green
                        'rgba(99, 102, 241, 0.7)',   // Indigo
                        'rgba(124, 58, 237, 0.7)'    // Violet
                    ],
                    borderColor: [
                        '#10b981',
                        '#6366f1',
                        '#7c3aed'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endsection
