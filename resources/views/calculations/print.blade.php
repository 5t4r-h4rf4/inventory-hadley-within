<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perhitungan - {{ $calculation->product->nama_produk }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fff;
            color: #1e293b;
            padding: 40px;
        }

        .report-header {
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-logo {
            font-weight: 800;
            font-size: 24px;
            color: #6366f1;
            letter-spacing: 0.5px;
        }

        .report-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .table-summary th {
            background-color: #f8fafc !important;
            color: #475569;
            font-weight: 600;
        }

        .metric-badge {
            padding: 8px 12px;
            background-color: #f1f5f9;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
        }

        .metric-badge-primary {
            background-color: #e0e7ff;
            color: #4f46e5;
        }

        .metric-badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .chart-container {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        /* Print Specific Styles */
        @media print {
            body {
                padding: 20px 0;
            }
            .no-print {
                display: none !important;
            }
            .chart-container {
                border: none;
            }
        }
    </style>
</head>
<body>
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

    <!-- Header Report -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-6">
                <div class="company-logo">PT. PLASTIK INDONESIA MANUFAKTUR</div>
                <div class="text-muted" style="font-size: 12px; line-height: 1.5;">
                    Jl. Industri Plastik Blok A No. 12, Kawasan Industri Jababeka<br>
                    Cikarang, Bekasi, Jawa Barat - Indonesia
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="report-title">Laporan Analisis Persediaan</div>
                <div class="text-muted" style="font-size: 13px;">
                    Metode Hadley-Within Periodic Review (R,s,S)
                </div>
                <div class="text-muted" style="font-size: 12px; mt-2;">
                    Tanggal Cetak: {{ date('d F Y, H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Print Action Banner -->
    <div class="alert alert-info no-print d-flex justify-content-between align-items-center mb-4" style="border-radius: 10px;">
        <div>
            <strong>Mode Cetak Laporan.</strong> Browser Anda akan membuka dialog pencetakan secara otomatis. Anda juga dapat memilih printer "Save as PDF" untuk menyimpan salinan dokumen digital.
        </div>
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4">Cetak Sekarang</button>
    </div>

    <!-- Info Section -->
    <div class="row mb-4">
        <div class="col-6">
            <h5 class="fw-bold text-dark border-bottom pb-2">Informasi Produk</h5>
            <table class="table table-sm table-borderless" style="font-size: 14px;">
                <tr>
                    <td class="text-muted" style="width: 150px;">Nama Produk</td>
                    <td>: <strong>{{ $calculation->product->nama_produk }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Kode Produk</td>
                    <td>: <span class="fw-semibold">{{ $calculation->product->kode_produk }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Harga per Bal (v)</td>
                    <td>: <strong>Rp {{ number_format($calculation->item_price, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <h5 class="fw-bold text-dark border-bottom pb-2">Detail Transaksi Perhitungan</h5>
            <table class="table table-sm table-borderless" style="font-size: 14px;">
                <tr>
                    <td class="text-muted" style="width: 150px;">ID Perhitungan</td>
                    <td>: #HW-CALC-{{ str_pad($calculation->id, 5, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Input</td>
                    <td>: {{ $calculation->created_at->translatedFormat('d F Y, H:i') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Kebijakan</td>
                    <td>: <span class="text-success fw-bold">AKTIF (R,s,S)</span></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Table of Input parameters -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold text-dark border-bottom pb-2">1. Parameter Input Variabel</h5>
            <table class="table table-bordered table-summary" style="font-size: 13.5px;">
                <thead>
                    <tr>
                        <th>Variabel</th>
                        <th>Keterangan Parameter</th>
                        <th class="text-end">Nilai Parameter</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>D</strong></td>
                        <td>Permintaan Rata-rata per Periode</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->d, 2, ',', '.') }} unit</td>
                    </tr>
                    <tr>
                        <td><strong>Mulai</strong></td>
                        <td>Tanggal Awal Tinjauan</td>
                        <td class="text-end fw-semibold">{{ $calculation->start_date ? $calculation->start_date->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Akhir</strong></td>
                        <td>Tanggal Akhir Tinjauan</td>
                        <td class="text-end fw-semibold">{{ $calculation->end_date ? $calculation->end_date->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>R</strong></td>
                        <td>Review Period (Waktu Tinjauan Stok)</td>
                        <td class="text-end fw-semibold text-primary">
                            {{ round($calculation->r_period * 365) }} hari 
                            <small class="text-muted">(&approx; {{ number_format($calculation->r_period, 4, ',', '.') }} tahun)</small>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>L</strong></td>
                        <td>Lead Time (Waktu Tunggu Pengiriman)</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->lead_time, 4, ',', '.') }} tahun</td>
                    </tr>
                    <tr>
                        <td><strong>A</strong></td>
                        <td>Biaya Pemesanan per Transaksi (Fixed)</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($calculation->ordering_cost, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>h</strong></td>
                        <td>Biaya Penyimpanan per Bal (Fixed)</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($calculation->holding_cost, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>p</strong></td>
                        <td>Biaya Kekurangan Persediaan per Bal (Fixed)</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($calculation->shortage_cost, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>σ</strong></td>
                        <td>Standar Deviasi Permintaan</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->sigma, 4, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table of Output parameters -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold text-dark border-bottom pb-2">2. Hasil Perhitungan & Kebijakan Stok</h5>
            <table class="table table-bordered table-summary" style="font-size: 13.5px;">
                <thead>
                    <tr>
                        <th>Hasil Variabel</th>
                        <th>Keterangan Perhitungan Matematika</th>
                        <th class="text-end">Hasil Analisis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>XR</strong></td>
                        <td>Demand selama Review Period (D * R)</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->xr, 4, ',', '.') }} unit</td>
                    </tr>
                    <tr>
                        <td><strong>XR+L</strong></td>
                        <td>Demand selama Lead Time + Review (D * (R + L))</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->xr_l, 4, ',', '.') }} unit</td>
                    </tr>
                    <tr>
                        <td><strong>z</strong></td>
                        <td>Faktor nilai z (Qp / sqrt(σ_R_L * Cu))</td>
                        <td class="text-end fw-semibold">{{ number_format($calculation->z_value, 4, ',', '.') }}</td>
                    </tr>
                    <tr class="table-success" style="background-color: #f0fdf4;">
                        <td><strong>s (Reorder Point)</strong></td>
                        <td><strong>Titik Pemesanan Kembali - s (min(Sp, So) jika pu &ge; k, else Sp)</strong></td>
                        <td class="text-end text-success fw-bold"><span class="metric-badge metric-badge-success">{{ number_format($calculation->reorder_point, 2, ',', '.') }} unit</span></td>
                    </tr>
                    <tr class="table-primary" style="background-color: #e0e7ff;">
                        <td><strong>Qp</strong></td>
                        <td><strong>Jumlah Pemesanan Optimal (Ekonomis)</strong></td>
                        <td class="text-end text-primary fw-bold"><span class="metric-badge metric-badge-primary">{{ number_format($calculation->qp, 2, ',', '.') }} unit</span></td>
                    </tr>
                    <tr class="table-warning" style="background-color: #fef3c7;">
                        <td><strong>S (Max Inventory)</strong></td>
                        <td><strong>Tingkat Persediaan Maksimum (Sp + Qp)</strong></td>
                        <td class="text-end fw-bold text-dark"><span class="metric-badge bg-warning-subtle text-warning-emphasis">{{ number_format($calculation->max_inventory, 2, ',', '.') }} unit</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Visual Print Section -->
    <div class="chart-container">
        <h5 class="fw-bold text-dark mb-3">Grafik Model Kebijakan Hadley-Within (R,s,S)</h5>
        <div style="position: relative; height: 180px; width: 100%;">
            <canvas id="printPolicyChart"></canvas>
        </div>
    </div>

    <!-- Detailed Iteration Table Print Section -->
    <div class="mt-4" style="page-break-inside: avoid;">
        <h5 class="fw-bold text-dark border-bottom pb-2">3. Tabel Iterasi Penentuan Kebijakan Persediaan Optimal (Hadley-Whitin)</h5>
        <table class="table table-bordered align-middle text-center" style="font-size: 10px;">
            <thead class="table-light text-secondary">
                <tr>
                    <th>Iterasi</th>
                    <th>T<sub>0</sub> (Thn)</th>
                    <th>&alpha;</th>
                    <th>Z<sub>&alpha;</sub></th>
                    <th>f(Z<sub>&alpha;</sub>)</th>
                    <th>&psi;(Z<sub>&alpha;</sub>)</th>
                    <th>R (ROP)</th>
                    <th>N (Kekurangan)</th>
                    <th>Ekspektasi Stockout (N/T<sub>0</sub>)</th>
                    <th>Ob</th>
                    <th>Op</th>
                    <th>Os</th>
                    <th>Ok</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($iterations as $it)
                    @php
                        $rowClass = '';
                        $badgeClass = 'bg-secondary';
                        $statusLabel = 'LANJUT';
                        
                        if ($it['status'] === 'OPTIMAL') {
                            $rowClass = 'table-success fw-bold text-success';
                            $badgeClass = 'bg-success';
                            $statusLabel = 'OPTIMAL';
                        } elseif ($it['status'] === 'TERAKHIR') {
                            $rowClass = 'table-warning';
                            $badgeClass = 'bg-warning text-dark';
                            $statusLabel = 'TERAKHIR';
                        }
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>It {{ $it['no'] }}</td>
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

    <!-- Detailed Calculation Steps Print Section -->
    <div class="mt-4" style="page-break-inside: avoid;">
        <h5 class="fw-bold text-dark border-bottom pb-2">4. Rincian Langkah Perhitungan Matematika</h5>
        <table class="table table-bordered align-middle" style="font-size: 12px;">
            <thead class="table-light text-secondary">
                <tr>
                    <th style="width: 80px;" class="text-center">Langkah</th>
                    <th style="width: 250px;">Formula & Deskripsi</th>
                    <th>Substitusi Nilai & Perhitungan</th>
                    <th style="width: 130px;" class="text-end">Hasil Akhir</th>
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

    <!-- Footer Signatures -->
    <div class="row mt-5 pt-4" style="page-break-inside: avoid;">
        <div class="col-4 text-center">
            <div style="font-size: 13px;">Dibuat Oleh,</div>
            <div style="height: 80px;"></div>
            <div class="fw-bold text-dark" style="font-size: 14px; text-decoration: underline;">{{ Auth::user()->name }}</div>
            <div class="text-muted" style="font-size: 12px;">Admin Gudang</div>
        </div>
        <div class="col-4"></div>
        <div class="col-4 text-center">
            <div style="font-size: 13px;">Disetujui Oleh,</div>
            <div style="height: 80px;"></div>
            <div class="fw-bold text-dark" style="font-size: 14px; text-decoration: underline;">...................................</div>
            <div class="text-muted" style="font-size: 12px;">Kepala Bagian Produksi</div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const rop = {{ $calculation->reorder_point }};
            const max = {{ $calculation->max_inventory }};
            const qp = {{ $calculation->qp }};

            new Chart(document.getElementById('printPolicyChart'), {
                type: 'bar',
                data: {
                    labels: ['Reorder Point (s)', 'Batch Pemesanan (Qp)', 'Maksimum Persediaan (S)'],
                    datasets: [{
                        label: 'Jumlah Unit',
                        data: [rop, qp, max],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(124, 58, 237, 0.7)'
                        ],
                        borderColor: [
                            '#10b981',
                            '#6366f1',
                            '#7c3aed'
                        ],
                        borderWidth: 1.5
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

            // Delayed print triggering to allow Chart.js to render
            setTimeout(function() {
                window.print();
            }, 1000);
        });
    </script>
</body>
</html>
