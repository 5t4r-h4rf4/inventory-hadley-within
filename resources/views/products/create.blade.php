@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('header_title', 'Tambah Produk')
@section('header_subtitle', 'Masukkan data produk plastik baru ke sistem')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Form Tambah Produk Baru</span>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="kode_produk" class="form-label fw-semibold text-dark">Kode Produk</label>
                        <input type="text" name="kode_produk" id="kode_produk" class="form-control @error('kode_produk') is-invalid @enderror" value="{{ old('kode_produk') }}" placeholder="Contoh: PRD-007" required>
                        @error('kode_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Masukkan kode unik produk (maksimal 50 karakter).</div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_produk" class="form-label fw-semibold text-dark">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror" value="{{ old('nama_produk') }}" placeholder="Contoh: PIRING MAKAN RB" required>
                        @error('nama_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Masukkan nama produk plastik secara lengkap.</div>
                    </div>

                    <div class="mb-3">
                        <label for="harga_produk" class="form-label fw-semibold text-dark">Harga Produk per Bal (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" step="0.01" min="0" name="harga_produk" id="harga_produk" class="form-control @error('harga_produk') is-invalid @enderror" value="{{ old('harga_produk') }}" placeholder="Contoh: 350000" required>
                            @error('harga_produk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Masukkan harga produk per bal (angka tanpa titik pemisah). Harga ini digunakan sebagai parameter v (harga produk) pada perhitungan.</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="barang_masuk" class="form-label fw-semibold text-dark">Barang Masuk</label>
                            <input type="number" min="0" name="barang_masuk" id="barang_masuk" class="form-control @error('barang_masuk') is-invalid @enderror" value="{{ old('barang_masuk', 0) }}" placeholder="Contoh: 0" required>
                            @error('barang_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Jumlah barang masuk ke gudang.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="barang_keluar" class="form-label fw-semibold text-dark">Barang Keluar</label>
                            <input type="number" min="0" name="barang_keluar" id="barang_keluar" class="form-control @error('barang_keluar') is-invalid @enderror" value="{{ old('barang_keluar', 0) }}" placeholder="Contoh: 0" required>
                            @error('barang_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Jumlah barang keluar/terjual dari gudang.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">Reset</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
