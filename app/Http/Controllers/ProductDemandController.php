<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDemand;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductDemandController extends Controller
{
    /**
     * Display a listing of demands.
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('nama_produk', 'asc')->get();
        $productId = $request->input('product_id');

        $demands = ProductDemand::with('product')
            ->when($productId, function ($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->orderBy('product_id', 'asc')
            ->orderBy('periode', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('demands.index', compact('demands', 'products', 'productId'));
    }

    /**
     * Show the form for creating a new demand.
     */
    public function create()
    {
        $products = Product::orderBy('nama_produk', 'asc')->get();
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return view('demands.create', compact('products', 'months'));
    }

    /**
     * Store a newly created demand in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2035',
            'jumlah_permintaan' => 'required|integer|min:0',
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak valid.',
            'month.required' => 'Bulan wajib dipilih.',
            'year.required' => 'Tahun wajib diisi.',
            'jumlah_permintaan.required' => 'Jumlah permintaan wajib diisi.',
            'jumlah_permintaan.integer' => 'Jumlah permintaan harus berupa angka bulat.',
            'jumlah_permintaan.min' => 'Jumlah permintaan tidak boleh kurang dari 0.',
        ]);

        $periode = Carbon::createFromDate($request->year, $request->month, 1)->format('Y-m-d');

        // Check if unique for product and month
        $exists = ProductDemand::where('product_id', $request->product_id)
            ->whereDate('periode', $periode)
            ->exists();

        if ($exists) {
            return back()->withErrors(['periode' => 'Data permintaan untuk produk dan periode bulan tersebut sudah terdaftar di sistem.'])->withInput();
        }

        ProductDemand::create([
            'product_id' => $request->product_id,
            'periode' => $periode,
            'jumlah_permintaan' => $request->jumlah_permintaan,
        ]);

        return redirect()->route('demands.index')
            ->with('success', 'Data permintaan bulanan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified demand.
     */
    public function edit($id)
    {
        $demand = ProductDemand::findOrFail($id);
        $products = Product::orderBy('nama_produk', 'asc')->get();
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $currentMonth = $demand->periode->month;
        $currentYear = $demand->periode->year;

        return view('demands.edit', compact('demand', 'products', 'months', 'currentMonth', 'currentYear'));
    }

    /**
     * Update the specified demand in storage.
     */
    public function update(Request $request, $id)
    {
        $demand = ProductDemand::findOrFail($id);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2035',
            'jumlah_permintaan' => 'required|integer|min:0',
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak valid.',
            'month.required' => 'Bulan wajib dipilih.',
            'year.required' => 'Tahun wajib diisi.',
            'jumlah_permintaan.required' => 'Jumlah permintaan wajib diisi.',
            'jumlah_permintaan.integer' => 'Jumlah permintaan harus berupa angka bulat.',
            'jumlah_permintaan.min' => 'Jumlah permintaan tidak boleh kurang dari 0.',
        ]);

        $periode = Carbon::createFromDate($request->year, $request->month, 1)->format('Y-m-d');

        // Check if unique for product and month, excluding current record
        $exists = ProductDemand::where('product_id', $request->product_id)
            ->whereDate('periode', $periode)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['periode' => 'Data permintaan untuk produk dan periode bulan tersebut sudah terdaftar di sistem.'])->withInput();
        }

        $demand->update([
            'product_id' => $request->product_id,
            'periode' => $periode,
            'jumlah_permintaan' => $request->jumlah_permintaan,
        ]);

        return redirect()->route('demands.index')
            ->with('success', 'Data permintaan bulanan berhasil diperbarui.');
    }

    /**
     * Remove the specified demand from storage.
     */
    public function destroy($id)
    {
        $demand = ProductDemand::findOrFail($id);
        $demand->delete();

        return redirect()->route('demands.index')
            ->with('success', 'Data permintaan bulanan berhasil dihapus.');
    }
}
