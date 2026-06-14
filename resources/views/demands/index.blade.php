@extends('layouts.admin')

@section('title', 'Data Permintaan Bulanan')
@section('header_title', 'Data Permintaan Bulanan')
@section('header_subtitle', 'Kelola riwayat data permintaan bulanan untuk analisis peramalan persediaan')

@section('content')
<div class="row">
    <!-- Filter and Action Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <!-- Product Filter Form -->
                    <form action="{{ route('demands.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 480px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-funnel"></i></span>
                            <select name="product_id" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                                <option value="">-- Semua Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                                        {{ $product->kode_produk }} - {{ $product->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($productId)
                            <a href="{{ route('demands.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </form>

                    <!-- Action Button -->
                    <a href="{{ route('demands.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Data Permintaan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">Daftar Permintaan Produk per Periode</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 80px;">No</th>
                                <th style="width: 180px;">Kode Produk</th>
                                <th>Nama Produk</th>
                                <th style="width: 180px;">Periode Bulan</th>
                                <th style="width: 180px;">Jumlah Permintaan</th>
                                <th class="text-center pe-4" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($demands as $index => $demand)
                                <tr>
                                    <td class="ps-4 fw-medium text-muted">{{ $demands->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 12px; border-radius: 6px;">
                                            {{ $demand->product->kode_produk }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $demand->product->nama_produk }}</td>
                                    <td class="fw-semibold text-dark">{{ $demand->periode->translatedFormat('F Y') }}</td>
                                    <td class="fw-bold text-dark">{{ $demand->jumlah_permintaan }} unit</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('demands.edit', $demand->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Data">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('demands.destroy', $demand->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data permintaan {{ $demand->product->nama_produk }} periode {{ $demand->periode->translatedFormat('F Y') }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-graph-up fs-2 d-block mb-2 text-secondary"></i>
                                        Tidak ada data permintaan ditemukan. Silakan tambahkan data baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($demands->hasPages())
                <div class="card-footer bg-white border-top border-light d-flex justify-content-end py-3">
                    {{ $demands->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
