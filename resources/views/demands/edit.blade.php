@extends('layouts.admin')

@section('title', 'Edit Data Permintaan')
@section('header_title', 'Edit Data Permintaan')
@section('header_subtitle', 'Perbarui data permintaan produk bulanan')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Form Edit Data Permintaan</span>
                <a href="{{ route('demands.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                
                @if($errors->has('periode'))
                    <div class="alert alert-danger py-2 border-0 bg-danger-subtle text-danger-emphasis" style="font-size: 13.5px; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first('periode') }}
                    </div>
                @endif

                <form action="{{ route('demands.update', $demand->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold text-dark">Pilih Produk</label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $demand->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->kode_produk }} - {{ $product->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="month" class="form-label fw-semibold text-dark">Pilih Bulan</label>
                            <select name="month" id="month" class="form-select @error('month') is-invalid @enderror" required>
                                <option value="">-- Pilih Bulan --</option>
                                @foreach($months as $val => $name)
                                    <option value="{{ $val }}" {{ old('month', $currentMonth) == $val ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="year" class="form-label fw-semibold text-dark">Masukkan Tahun</label>
                            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror" min="2020" max="2035" value="{{ old('year', $currentYear) }}" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="jumlah_permintaan" class="form-label fw-semibold text-dark">Jumlah Permintaan (Unit)</label>
                        <input type="number" min="0" name="jumlah_permintaan" id="jumlah_permintaan" class="form-control @error('jumlah_permintaan') is-invalid @enderror" value="{{ old('jumlah_permintaan', $demand->jumlah_permintaan) }}" required>
                        @error('jumlah_permintaan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('demands.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
