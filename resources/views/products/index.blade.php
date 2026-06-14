@extends('layouts.admin')

@section('title', 'Master Produk')
@section('header_title', 'Master Produk')
@section('header_subtitle', 'Kelola data produk plastik dan harga per bal')

@section('content')
<div class="row">
    <!-- Search, Import, and Actions Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <!-- Search Bar -->
                    <form action="{{ route('products.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 480px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari berdasarkan kode atau nama produk..." value="{{ $search }}">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary">Cari</button>
                        @if($search)
                            <a href="{{ route('products.index') }}" class="btn btn-outline-light text-dark">Reset</a>
                        @endif
                    </form>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah Produk</span>
                        </a>
                        <a href="{{ route('products.export') }}" class="btn btn-success d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel"></i>
                            <span>Export Excel</span>
                        </a>
                        <button class="btn btn-outline-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImport" aria-expanded="false" aria-controls="collapseImport">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Import Excel</span>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Import Section -->
                <div class="collapse mt-4" id="collapseImport">
                    <div class="p-4 bg-light rounded-3 border">
                        <h5 class="fw-bold text-dark mb-2">Import Data Produk</h5>
                        <p class="text-muted" style="font-size: 13px;">Unggah berkas CSV Anda untuk memperbarui atau mengimpor data produk secara masal. Unduh template file terlebih dahulu untuk menyesuaikan struktur kolom data Anda.</p>
                        
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-3">
                            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 flex-grow-1" style="max-width: 600px;">
                                @csrf
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" name="file" accept=".csv" required>
                                </div>
                                <button type="submit" class="btn btn-primary px-4">Unggah & Proses</button>
                            </form>
                            
                            <a href="{{ route('products.template') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-download"></i>
                                <span>Unduh Template CSV</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Product Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">Daftar Produk Plastik</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 60px;">No</th>
                                <th style="width: 120px;">Kode Produk</th>
                                <th>Nama Produk</th>
                                <th style="width: 150px;">Harga (per Bal)</th>
                                <th class="text-center" style="width: 110px;">Barang Masuk</th>
                                <th class="text-center" style="width: 110px;">Barang Keluar</th>
                                <th class="text-center" style="width: 110px;">Stok</th>
                                <th class="text-center" style="width: 140px;">Reorder Point (s)</th>
                                <th class="text-center pe-4" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $index => $product)
                                <tr>
                                    <td class="ps-4 fw-medium text-muted">{{ $products->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 12px; border-radius: 6px;">
                                            {{ $product->kode_produk }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $product->nama_produk }}</td>
                                    <td class="fw-bold text-dark">Rp {{ number_format($product->harga_produk, 0, ',', '.') }}</td>
                                    <td class="text-center font-monospace">{{ number_format($product->barang_masuk, 0, ',', '.') }}</td>
                                    <td class="text-center font-monospace">{{ number_format($product->barang_keluar, 0, ',', '.') }}</td>
                                    <td class="text-center font-monospace">
                                        @if(!is_null($product->reorder_point))
                                            @if($product->stok <= $product->reorder_point)
                                                <span class="badge bg-danger text-white px-2 py-1.5 fw-bold" style="font-size: 12.5px; border-radius: 6px;" title="Stok di bawah atau sama dengan Reorder Point!">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ number_format($product->stok, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success text-white px-2 py-1.5 fw-bold" style="font-size: 12.5px; border-radius: 6px;">
                                                    {{ number_format($product->stok, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-dark border px-2 py-1.5 fw-semibold" style="font-size: 12.5px; border-radius: 6px;">
                                                {{ number_format($product->stok, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace">
                                        @if(!is_null($product->reorder_point))
                                            <span class="fw-bold text-primary">{{ number_format($product->reorder_point, 2, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted" style="font-size: 12px;">Belum Dihitung</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDemands{{ $product->id }}" title="Lihat Permintaan">
                                                <i class="bi bi-graph-up"></i>
                                            </button>
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Produk">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->nama_produk }}? Semua riwayat perhitungan produk ini juga akan ikut terhapus secara permanen.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Produk">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Demands -->
                                        <div class="modal fade text-start" id="modalDemands{{ $product->id }}" tabindex="-1" aria-labelledby="modalDemandsLabel{{ $product->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                                    <div class="modal-header border-0 bg-light py-3 px-4">
                                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalDemandsLabel{{ $product->id }}">
                                                            <i class="bi bi-box-seam-fill text-primary"></i>
                                                            <span>Data Permintaan - {{ $product->nama_produk }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="row">
                                                            <!-- Table column -->
                                                            <div class="col-md-6 mb-4 mb-md-0 border-end">
                                                                <h6 class="fw-bold mb-3 text-secondary text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Rincian Permintaan Bulanan</h6>
                                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                                    <table class="table table-hover table-sm text-center align-middle" style="font-size: 13px;">
                                                                        <thead class="table-light text-secondary">
                                                                            <tr>
                                                                                <th>Periode</th>
                                                                                <th>Jumlah Permintaan</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @forelse($product->demands->sortBy('periode') as $dem)
                                                                                <tr>
                                                                                    <td class="text-muted">{{ $dem->periode->translatedFormat('F Y') }}</td>
                                                                                    <td class="fw-bold text-dark">{{ $dem->jumlah_permintaan }} unit</td>
                                                                                </tr>
                                                                            @empty
                                                                                <tr>
                                                                                    <td colspan="2" class="text-muted py-4">Belum ada data permintaan historis.</td>
                                                                                </tr>
                                                                            @endforelse
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Statistics column -->
                                                            <div class="col-md-6 ps-md-4">
                                                                <h6 class="fw-bold mb-3 text-secondary text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Statistik Permintaan Terkalkulasi</h6>
                                                                @php $stats = $product->getDemandStats(); @endphp
                                                                
                                                                <div class="row g-2 mb-3">
                                                                    <div class="col-6">
                                                                        <div class="p-2 border rounded-3 bg-light">
                                                                            <div class="text-muted mb-0.5" style="font-size: 10px;">Rata-rata Bulanan</div>
                                                                            <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ number_format($stats['avg_monthly'], 2, ',', '.') }} unit</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="p-2 border rounded-3 bg-light">
                                                                            <div class="text-muted mb-0.5" style="font-size: 10px;">Deviasi Bulanan</div>
                                                                            <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ number_format($stats['std_monthly'], 2, ',', '.') }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="p-3 border rounded-3 mb-2" style="background-color: #f5f3ff; border-color: #e9e3ff !important;">
                                                                    <div class="text-primary fw-semibold mb-1" style="font-size: 11px;">Rata-rata Tahunan (D)</div>
                                                                    <h4 class="fw-bold text-primary mb-0" style="font-size: 20px;">{{ number_format($stats['avg_yearly'], 2, ',', '.') }} unit/tahun</h4>
                                                                    <small class="text-muted" style="font-size: 10px;">Rumus: Rerata Bulanan * 12</small>
                                                                </div>
                                                                
                                                                <div class="p-3 border rounded-3 mb-3" style="background-color: #e0e7ff; border-color: #d1d8ff !important;">
                                                                    <div class="text-indigo fw-semibold mb-1" style="color: #4f46e5; font-size: 11px;">Standar Deviasi Tahunan (σ)</div>
                                                                    <h4 class="fw-bold mb-0" style="color: #4f46e5; font-size: 20px;">{{ number_format($stats['std_yearly'], 2, ',', '.') }}</h4>
                                                                    <small class="text-muted" style="font-size: 10px;">Rumus: Deviasi Bulanan * &radic;12</small>
                                                                </div>
                                                                
                                                                <div class="alert alert-secondary border-0 p-2 mb-0" style="background-color: #f8fafc; color: #64748b; font-size: 10.5px; border-radius: 8px;">
                                                                    <i class="bi bi-info-circle-fill me-1 text-primary"></i>
                                                                    Parameter <strong>D</strong> (Tahunan) dan <strong>&sigma;</strong> (Tahunan) di atas akan dimasukkan secara otomatis ke form kalkulator saat produk ini dipilih.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 bg-light py-2 px-4">
                                                        <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-box2 fs-2 d-block mb-2 text-secondary"></i>
                                        Tidak ada data produk ditemukan. Silakan tambahkan produk baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($products->hasPages())
                <div class="card-footer bg-white border-top border-light d-flex justify-content-end py-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
