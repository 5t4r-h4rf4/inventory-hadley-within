@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Analisis')
@section('header_subtitle', 'Ringkasan data persediaan dan parameter Hadley-Within')

@section('content')
<!-- Row 1: Metrics Widgets -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2">
            <div class="card-body">
                <div class="stat-card">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Total Produk</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800" style="font-size: 28px; font-weight: 800;">{{ $totalProducts }}</div>
                    </div>
                    <div class="stat-icon bg-light-primary" style="background-color: #e0e7ff; color: #6366f1;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2">
            <div class="card-body">
                <div class="stat-card">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Total Perhitungan</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800" style="font-size: 28px; font-weight: 800;">{{ $totalCalculations }}</div>
                    </div>
                    <div class="stat-icon" style="background-color: #d1fae5; color: #10b981;">
                        <i class="bi bi-calculator"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2">
            <div class="card-body">
                <div class="stat-card">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Produk Termahal</div>
                        <div class="mb-0 text-gray-800 text-truncate" style="font-size: 14px; font-weight: 700; max-width: 140px;">
                            {{ $expensiveProduct ? $expensiveProduct->nama_produk : '-' }}
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Rp {{ $expensiveProduct ? number_format($expensiveProduct->harga_produk, 0, ',', '.') : '0' }}</div>
                    </div>
                    <div class="stat-icon" style="background-color: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 py-2">
            <div class="card-body">
                <div class="stat-card">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Produk Termurah</div>
                        <div class="mb-0 text-gray-800 text-truncate" style="font-size: 14px; font-weight: 700; max-width: 140px;">
                            {{ $cheapestProduct ? $cheapestProduct->nama_produk : '-' }}
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Rp {{ $cheapestProduct ? number_format($cheapestProduct->harga_produk, 0, ',', '.') : '0' }}</div>
                    </div>
                    <div class="stat-icon" style="background-color: #fee2e2; color: #ef4444;">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Latest Calculation Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Ringkasan Perhitungan Terakhir</span>
                @if ($latestCalculation)
                    <a href="{{ route('calculations.show', $latestCalculation->id) }}" class="btn btn-sm btn-primary py-1 px-3" style="font-size: 13px;">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if ($latestCalculation)
                    <div class="row align-items-center">
                        <div class="col-lg-4 mb-4 mb-lg-0 border-end border-light">
                            <div class="text-muted mb-1" style="font-size: 12px;">Produk yang Dihitung</div>
                            <h4 class="text-primary fw-bold mb-1">{{ $latestCalculation->product->nama_produk }}</h4>
                            <div class="text-muted mb-3" style="font-size: 13px;">Kode: <strong>{{ $latestCalculation->product->kode_produk }}</strong> | Harga: <strong>Rp {{ number_format($latestCalculation->item_price, 0, ',', '.') }}</strong></div>
                            
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted mb-1" style="font-size: 11px;">Waktu Perhitungan</div>
                                <div class="fw-semibold text-dark" style="font-size: 13px;">
                                    <i class="bi bi-calendar3 me-1"></i> {{ $latestCalculation->created_at->translatedFormat('d F Y, H:i') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="row text-center">
                                <div class="col-sm-3 col-6 mb-3">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Demand XR (R)</div>
                                        <div class="fw-bold text-dark" style="font-size: 16px;">{{ number_format($latestCalculation->xr, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6 mb-3">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Demand XR+L (R+L)</div>
                                        <div class="fw-bold text-dark" style="font-size: 16px;">{{ number_format($latestCalculation->xr_l, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6 mb-3">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Order Optimal (Qp)</div>
                                        <div class="fw-bold text-primary" style="font-size: 16px;">{{ number_format($latestCalculation->qp, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6 mb-3">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Nilai z</div>
                                        <div class="fw-bold text-dark" style="font-size: 16px;">{{ number_format($latestCalculation->z_value, 4, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Safety Limit (Sp)</div>
                                        <div class="fw-bold text-dark" style="font-size: 16px;">{{ number_format($latestCalculation->sp, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="p-2 border border-light rounded-3 bg-light-subtle">
                                        <div class="text-muted mb-1" style="font-size: 11px;">Limit So</div>
                                        <div class="fw-bold text-dark" style="font-size: 16px;">{{ number_format($latestCalculation->so, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="p-2 border border-success-subtle rounded-3" style="background-color: #f0fdf4;">
                                        <div class="text-success mb-1" style="font-size: 11px; font-weight: 600;">Reorder Point (s)</div>
                                        <div class="fw-bold text-success" style="font-size: 16px;">{{ number_format($latestCalculation->reorder_point, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="p-2 border border-primary-subtle rounded-3" style="background-color: #f5f3ff;">
                                        <div class="text-primary mb-1" style="font-size: 11px; font-weight: 600;">Max Inventory (S)</div>
                                        <div class="fw-bold text-primary" style="font-size: 16px;">{{ number_format($latestCalculation->max_inventory, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                        Belum ada riwayat perhitungan. Silakan masuk ke menu <a href="{{ route('calculations.create') }}">Kalkulator</a> untuk memulai perhitungan pertama Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Graphs (1 & 2) -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Grafik Harga Produk (Rp per Bal)</div>
            <div class="card-body">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="priceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Grafik Jumlah Pemesanan Optimal (Qp) per Produk (10 Terakhir)</div>
            <div class="card-body">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="qpChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Graphs (3 & 4) -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Grafik Reorder Point (s) per Produk (10 Terakhir)</div>
            <div class="card-body">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="ropChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Grafik Maximum Inventory (S) per Produk (10 Terakhir)</div>
            <div class="card-body">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="maxChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 5: Graph (5) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Grafik Riwayat Perhitungan (Volume per Bulan)</div>
            <div class="card-body">
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="historyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Register charts after DOM loaded
    document.addEventListener("DOMContentLoaded", function () {
        
        // Data from backend
        const priceLabels = {!! json_encode($priceLabels) !!};
        const priceValues = {!! json_encode($priceValues) !!};

        const recentLabels = {!! json_encode($recentLabels) !!};
        const recentQp = {!! json_encode($recentQp) !!};
        const recentRop = {!! json_encode($recentRop) !!};
        const recentMax = {!! json_encode($recentMaxInv) !!};

        const historyLabels = {!! json_encode($historyLabels) !!};
        const historyValues = {!! json_encode($historyValues) !!};

        // Theme Colors
        const primaryColor = '#6366f1';
        const primaryHover = '#4f46e5';
        const successColor = '#10b981';
        const warningColor = '#f59e0b';
        const dangerColor = '#ef4444';

        // 1. Price Chart (Bar Chart)
        new Chart(document.getElementById('priceChart'), {
            type: 'bar',
            data: {
                labels: priceLabels,
                datasets: [{
                    label: 'Harga Produk (Rp)',
                    data: priceValues,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: primaryColor,
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // 2. Qp Chart (Bar/Line Chart)
        new Chart(document.getElementById('qpChart'), {
            type: 'line',
            data: {
                labels: recentLabels,
                datasets: [{
                    label: 'Pemesanan Optimal (Qp)',
                    data: recentQp,
                    borderColor: successColor,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: successColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 3. Rop Chart (Bar Chart)
        new Chart(document.getElementById('ropChart'), {
            type: 'bar',
            data: {
                labels: recentLabels,
                datasets: [{
                    label: 'Reorder Point (s)',
                    data: recentRop,
                    backgroundColor: 'rgba(245, 158, 11, 0.7)',
                    borderColor: warningColor,
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 4. Max Inventory Chart (Bar Chart)
        new Chart(document.getElementById('maxChart'), {
            type: 'bar',
            data: {
                labels: recentLabels,
                datasets: [{
                    label: 'Maximum Inventory (S)',
                    data: recentMax,
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: dangerColor,
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 5. Calculations History Trend (Line Chart)
        new Chart(document.getElementById('historyChart'), {
            type: 'line',
            data: {
                labels: historyLabels.length > 0 ? historyLabels : ['Belum Ada Data'],
                datasets: [{
                    label: 'Volume Perhitungan',
                    data: historyValues.length > 0 ? historyValues : [0],
                    borderColor: primaryColor,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: primaryColor,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endsection
