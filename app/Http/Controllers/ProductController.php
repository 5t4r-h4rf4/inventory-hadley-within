<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $products = Product::with('demands')->when($search, function ($query, $search) {
            return $query->where('kode_produk', 'like', "%{$search}%")
                         ->orWhere('nama_produk', 'like', "%{$search}%");
        })
        ->orderBy('kode_produk', 'asc')
        ->paginate(10)
        ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_produk' => 'required|string|max:50|unique:products,kode_produk',
            'nama_produk' => 'required|string|max:255',
            'harga_produk' => 'required|numeric|min:0',
            'barang_masuk' => 'required|integer|min:0',
            'barang_keluar' => 'required|integer|min:0',
        ], [
            'kode_produk.required' => 'Kode produk wajib diisi.',
            'kode_produk.unique' => 'Kode produk sudah terdaftar.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'harga_produk.required' => 'Harga produk wajib diisi.',
            'harga_produk.numeric' => 'Harga produk harus berupa angka.',
            'harga_produk.min' => 'Harga produk tidak boleh negatif.',
            'barang_masuk.required' => 'Jumlah barang masuk wajib diisi.',
            'barang_masuk.integer' => 'Jumlah barang masuk harus berupa bilangan bulat.',
            'barang_masuk.min' => 'Jumlah barang masuk tidak boleh negatif.',
            'barang_keluar.required' => 'Jumlah barang keluar wajib diisi.',
            'barang_keluar.integer' => 'Jumlah barang keluar harus berupa bilangan bulat.',
            'barang_keluar.min' => 'Jumlah barang keluar tidak boleh negatif.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'kode_produk' => 'required|string|max:50|unique:products,kode_produk,' . $product->id,
            'nama_produk' => 'required|string|max:255',
            'harga_produk' => 'required|numeric|min:0',
            'barang_masuk' => 'required|integer|min:0',
            'barang_keluar' => 'required|integer|min:0',
        ], [
            'kode_produk.required' => 'Kode produk wajib diisi.',
            'kode_produk.unique' => 'Kode produk sudah terdaftar.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'harga_produk.required' => 'Harga produk wajib diisi.',
            'harga_produk.numeric' => 'Harga produk harus berupa angka.',
            'harga_produk.min' => 'Harga produk tidak boleh negatif.',
            'barang_masuk.required' => 'Jumlah barang masuk wajib diisi.',
            'barang_masuk.integer' => 'Jumlah barang masuk harus berupa bilangan bulat.',
            'barang_masuk.min' => 'Jumlah barang masuk tidak boleh negatif.',
            'barang_keluar.required' => 'Jumlah barang keluar wajib diisi.',
            'barang_keluar.integer' => 'Jumlah barang keluar harus berupa bilangan bulat.',
            'barang_keluar.min' => 'Jumlah barang keluar tidak boleh negatif.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Export products to CSV.
     */
    public function export()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=daftar_produk.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $products = Product::orderBy('kode_produk', 'asc')->get();

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Kode Produk', 'Nama Produk', 'Harga Produk/Bal', 'Barang Masuk', 'Barang Keluar']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->kode_produk,
                    $product->nama_produk,
                    $product->harga_produk,
                    $product->barang_masuk,
                    $product->barang_keluar
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import products from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ], [
            'file.required' => 'File CSV wajib diunggah.',
            'file.mimes' => 'Format file harus berupa CSV.'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle !== false) {
            // Read headers
            $header = fgetcsv($handle, 1000, ',');
            
            // Remove UTF-8 BOM from the first header element if present
            if ($header && strpos($header[0], "\xEF\xBB\xBF") === 0) {
                $header[0] = substr($header[0], 3);
            }

            $rowCount = 0;

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                // If it's a blank row or has incomplete cells
                if (count($row) < 3) continue;

                $kode = trim($row[0]);
                $nama = trim($row[1]);
                $harga = trim($row[2]);
                $masuk = isset($row[3]) ? intval(trim($row[3])) : 0;
                $keluar = isset($row[4]) ? intval(trim($row[4])) : 0;

                // Clean price: remove currency symbols and dots/commas
                $hargaClean = floatval(preg_replace('/[^0-9.]/', '', $harga));

                if (!empty($kode) && !empty($nama)) {
                    Product::updateOrCreate(
                        ['kode_produk' => $kode],
                        [
                            'nama_produk' => $nama,
                            'harga_produk' => $hargaClean,
                            'barang_masuk' => $masuk,
                            'barang_keluar' => $keluar,
                        ]
                    );
                    $rowCount++;
                }
            }

            fclose($handle);
            return redirect()->route('products.index')
                ->with('success', "Berhasil memproses file. {$rowCount} data produk diimpor/diperbarui.");
        }

        return back()->withErrors(['file' => 'Gagal membuka file CSV.']);
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_produk.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kode Produk', 'Nama Produk', 'Harga Produk/Bal', 'Barang Masuk', 'Barang Keluar']);
            fputcsv($file, ['PRD-007', 'MANGKUK BULAT RB', '250000', '150', '30']);
            fputcsv($file, ['PRD-008', 'CANGKIR PLASTIK DX', '180000', '100', '10']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
