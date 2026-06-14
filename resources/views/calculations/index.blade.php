@extends('layouts.admin')

@section('title', 'Riwayat Perhitungan')
@section('header_title', 'Riwayat Perhitungan')
@section('header_subtitle', 'Daftar riwayat perhitungan pengendalian persediaan Hadley-Within')

@section('content')
<div class="row">
    <!-- Filter Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">Filter Riwayat</div>
            <div class="card-body">
                <form action="{{ route('calculations.index') }}" method="GET" id="filter-form">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label for="product_id" class="form-label fw-semibold text-secondary" style="font-size: 13px;">Produk</label>
                            <select name="product_id" id="product_id" class="form-select">
                                <option value="">-- Semua Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                                        {{ $product->kode_produk }} - {{ $product->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <label for="start_date" class="form-label fw-semibold text-secondary" style="font-size: 13px;">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label for="end_date" class="form-label fw-semibold text-secondary" style="font-size: 13px;">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>

                        <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100 py-2">Filter</button>
                            @if ($productId || $startDate || $endDate)
                                <a href="{{ route('calculations.index') }}" class="btn btn-outline-secondary w-100 py-2">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- History Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Daftar Riwayat Perhitungan</span>
                <a href="{{ route('calculations.export', ['product_id' => $productId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel (CSV)
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 60px;">No</th>
                                <th style="width: 140px;">Tanggal Input</th>
                                <th>Nama Produk</th>
                                <th style="width: 200px;">Periode Tinjauan</th>
                                <th style="width: 100px;">R (hari)</th>
                                <th style="width: 90px;">Qp</th>
                                <th style="width: 110px;">Reorder Point (s)</th>
                                <th style="width: 110px;">Max Inv (S)</th>
                                <th class="text-center pe-4" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($calculations as $index => $calc)
                                <tr>
                                    <td class="ps-4 fw-medium text-muted">{{ $calculations->firstItem() + $index }}</td>
                                    <td class="text-muted">{{ $calc->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="fw-semibold text-dark">{{ $calc->product->nama_produk }}</td>
                                    <td>
                                        @if($calc->start_date && $calc->end_date)
                                            <span style="font-size: 11.5px;">
                                                {{ $calc->start_date->format('d/m/Y') }} - {{ $calc->end_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ round($calc->r_period * 365) }} hari</span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($calc->qp, 1, ',', '.') }}</td>
                                    <td class="fw-bold text-success">{{ number_format($calc->reorder_point, 1, ',', '.') }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($calc->max_inventory, 1, ',', '.') }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('calculations.show', $calc->id) }}" class="btn btn-sm btn-outline-primary" title="Detail Perhitungan">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <form action="{{ route('calculations.destroy', $calc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Riwayat">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary"></i>
                                        Tidak ada data riwayat perhitungan ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($calculations->hasPages())
                <div class="card-footer bg-white border-top border-light d-flex justify-content-end py-3">
                    {{ $calculations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
