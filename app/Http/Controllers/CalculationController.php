<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryCalculation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalculationController extends Controller
{
    /**
     * Display a listing of calculations.
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('nama_produk', 'asc')->get();

        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $calculations = InventoryCalculation::with('product')
            ->when($productId, function ($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('created_at', '>=', Carbon::parse($startDate));
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('created_at', '<=', Carbon::parse($endDate));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('calculations.index', compact('calculations', 'products', 'productId', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new calculation.
     */
    public function create()
    {
        $products = Product::with('demands')->orderBy('nama_produk', 'asc')->get();
        return view('calculations.create', compact('products'));
    }

    /**
     * Store a newly created calculation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'd' => 'required|numeric|gt:0',
            'sigma' => 'required|numeric|gt:0',
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak valid.',
            'start_date.required' => 'Tanggal mulai periode wajib diisi.',
            'start_date.date' => 'Tanggal mulai periode tidak valid.',
            'end_date.required' => 'Tanggal akhir periode wajib diisi.',
            'end_date.date' => 'Tanggal akhir periode tidak valid.',
            'end_date.after' => 'Tanggal akhir periode harus setelah tanggal mulai.',
            'd.required' => 'Permintaan rata-rata (D) wajib diisi.',
            'd.gt' => 'Permintaan rata-rata (D) harus lebih besar dari 0.',
            'sigma.required' => 'Standar Deviasi (σ) wajib diisi.',
            'sigma.gt' => 'Standar Deviasi (σ) harus lebih besar dari 0.',
        ]);

        $product = Product::findOrFail($request->product_id);
        $v = (float) $product->harga_produk;

        $d = (float) $request->d;
        
        // Calculate r_period based on start_date and end_date
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end);
        
        // represented as fraction of a year (R)
        $r_period = (float) ($days / 365);
        if ($r_period <= 0) {
            $r_period = 0.0001; // Avoid division by zero
        }

        $sigma = (float) $request->sigma;

        // Fixed parameters
        $lead_time = 0.019; // Fixed at 7 days
        $ordering_cost = 157947.92;
        $holding_cost = 27545.96;
        $shortage_cost = 0.10 * $v; // 10% of item price

        try {
            $result = $this->runIterationSearch($d, $lead_time, $ordering_cost, $holding_cost, $shortage_cost, $sigma, $v);
            $cheapest = $result['optimal'];

            if (!$cheapest) {
                return back()->withErrors(['math_error' => 'Perhitungan gagal menghasilkan kebijakan optimal. Periksa kembali input data Anda.'])->withInput();
            }

            // Extract values from the optimal (cheapest) iteration row
            $r_period_opt = $cheapest['t0'];
            $xr = $cheapest['xr'];
            $xr_l = $cheapest['xr_l'];
            $qp = $cheapest['qp'];
            $reorder_point = $cheapest['s'];
            $max_inventory = $cheapest['S'];
            $z_value = $cheapest['z'];
            $sp = $cheapest['sp'];

            // Check for NaN or Infinite results due to formula constraints
            if (is_nan($qp) || is_infinite($qp) || is_nan($sp) || is_infinite($sp) || is_nan($reorder_point) || is_nan($max_inventory)) {
                return back()->withErrors(['math_error' => 'Perhitungan menghasilkan nilai tidak terdefinisi (NaN/Infinity). Silakan periksa kembali parameter input Anda.'])->withInput();
            }

            // Save calculation to database
            $calculation = InventoryCalculation::create([
                'product_id' => $product->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'd' => $d,
                'r_period' => $r_period_opt,
                'lead_time' => $lead_time,
                'ordering_cost' => $ordering_cost,
                'item_price' => $v,
                'holding_cost' => $holding_cost,
                'shortage_cost' => $shortage_cost,
                'sigma' => $sigma,
                'xr' => $xr,
                'xr_l' => $xr_l,
                'qp' => $qp,
                'z_value' => $z_value,
                'sp' => $sp,
                'reorder_point' => $reorder_point,
                'max_inventory' => $max_inventory,
            ]);

            return redirect()->route('calculations.show', $calculation->id)
                ->with('success', 'Perhitungan Hadley-Within berhasil diselesaikan.');

        } catch (\Exception $e) {
            return back()->withErrors(['math_error' => 'Terjadi kesalahan matematis saat melakukan perhitungan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified calculation.
     */
    public function show(InventoryCalculation $calculation)
    {
        $calculation->load('product');
        $iterations = $this->generateIterations($calculation);
        return view('calculations.show', compact('calculation', 'iterations'));
    }

    /**
     * Delete the calculation record.
     */
    public function destroy(InventoryCalculation $calculation)
    {
        $calculation->delete();
        return redirect()->route('calculations.index')
            ->with('success', 'Riwayat perhitungan berhasil dihapus.');
    }

    /**
     * Print calculation report view.
     */
    public function print(InventoryCalculation $calculation)
    {
        $calculation->load('product');
        $iterations = $this->generateIterations($calculation);
        return view('calculations.print', compact('calculation', 'iterations'));
    }

    /**
     * Generate the iterations for the Hadley-Whitin model dynamically.
     */
    private function generateIterations(InventoryCalculation $calculation)
    {
        $v = (float) $calculation->item_price;
        $d = (float) $calculation->d;
        $lead_time = (float) $calculation->lead_time;
        $sigma = (float) $calculation->sigma;
        
        $ordering_cost = (float) $calculation->ordering_cost;
        $holding_cost = (float) $calculation->holding_cost;
        $shortage_cost = (float) $calculation->shortage_cost;
        
        $result = $this->runIterationSearch($d, $lead_time, $ordering_cost, $holding_cost, $shortage_cost, $sigma, $v);
        return $result['iterations'];
    }

    /**
     * Run the Hadley-Whitin iteration search.
     * Returns an array containing the iterations table and the optimal parameter values.
     */
    private function runIterationSearch($d, $lead_time, $ordering_cost, $holding_cost, $shortage_cost, $sigma, $v)
    {
        $t_eoq = sqrt((2 * $ordering_cost) / ($holding_cost * $d));
        
        $iterations = [];
        $previous_total = null;
        $cheapest_row = null;
        
        for ($i = 1; $i <= 20; $i++) {
            $t0 = $t_eoq - ($i - 1) * 0.010;
            if ($t0 <= 0) {
                break;
            }
            
            // 2. Alpha (α)
            $alpha = ($t0 * $holding_cost) / $shortage_cost;
            $alpha_clipped = min(0.9999, max(0.0001, $alpha));
            
            // Z_alpha from normal distribution
            $za = $this->stdNormalInverseCdf(1.0 - $alpha_clipped);
            $f_za = $this->stdNormalPdf($za);
            $psi_za = $this->stdNormalCdf($za);
            
            // 3. R (Maximum Inventory Target in iteration)
            $r = $d * ($t0 + $lead_time) + ($za * sqrt($t0 + $lead_time));
            
            // 4. Expected Shortage (N)
            $n = $sigma * sqrt($t0 + $lead_time) * ($f_za - ($za * $psi_za));
            
            // 5. Optimal Order Batch (Qp) & Sp
            $xr = $d * $r;
            $xr_l = $d * ($r + $lead_time);
            $sigma_R_L = $sigma * sqrt($r + $lead_time);
            
            // Ensure xr is positive to avoid pow() error
            $xr_safe = max(0.0001, $xr);
            $qp = 1.3 * pow($xr_safe, 0.494) * pow($ordering_cost / $holding_cost, 0.506) * pow(1.0 + (pow($sigma_R_L, 2) / pow($xr_safe, 2)), 0.116);
            
            // z parameter
            $z_val = $qp / sqrt(max(0.0001, $sigma_R_L * $shortage_cost));
            
            // Sp limit
            $sp = 0.973 * $xr_l + $sigma_R_L * ((0.183 / max(0.0001, $z_val)) + 1.063 - (2.192 * $z_val));
            
            // 6. So
            $So = $xr_l + $za * $sigma_R_L;
            $k = $holding_cost / ($shortage_cost + $holding_cost);
            $pu = 1.0 - $alpha;
            
            // 7. s (Reorder Point) and S (Maximum Level)
            $s = ($pu >= $k) ? min($sp, $So) : $sp;
            $S = $sp + $qp;
            
            // Costs calculation
            $ob = $v * $d;
            $op = $ordering_cost / $t0;
            $os = $holding_cost * (($d * $t0) / 2 + $za * sqrt($t0 + $lead_time));
            $ok = ($shortage_cost * $n) / $t0;
            
            $total = $ob + $op + $os + $ok;
            
            // Logika dalam iterasi: stop ketika hasil iterasi selanjutnya lebih mahal (Total Biaya)
            if ($previous_total !== null && $total > $previous_total) {
                break;
            }
            
            $row = [
                'no' => $i,
                't0' => $t0,
                'alpha' => $alpha,
                'za' => $za,
                'f_za' => $f_za,
                'psi_za' => $psi_za,
                'r' => $r,
                'n' => $n,
                'ekspektasi_stockout' => $n / $t0,
                'xr' => $xr,
                'xr_l' => $xr_l,
                'qp' => $qp,
                'z' => $z_val,
                'sp' => $sp,
                'so' => $So,
                's' => $s,
                'S' => $S,
                'ob' => $ob,
                'op' => $op,
                'os' => $os,
                'ok' => $ok,
                'total' => $total,
                'status' => 'LANJUT'
            ];
            
            $iterations[] = $row;
            $cheapest_row = $row;
            $previous_total = $total;
        }
        
        if ($cheapest_row) {
            $iterations[count($iterations) - 1]['status'] = 'OPTIMAL';
        }
        
        return [
            'iterations' => $iterations,
            'optimal' => $cheapest_row
        ];
    }

    /**
     * Export calculations history to CSV.
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=riwayat_perhitungan.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $calculations = InventoryCalculation::with('product')
            ->when($productId, function ($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('created_at', '>=', Carbon::parse($startDate));
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('created_at', '<=', Carbon::parse($endDate));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function() use ($calculations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fputcsv($file, [
                'Tanggal Perhitungan', 
                'Nama Produk', 
                'Harga Produk (v)', 
                'Tanggal Mulai Periode',
                'Tanggal Akhir Periode',
                'Permintaan Rata-rata (D)',
                'Review Period (R) - Tahun',
                'Lead Time (L)',
                'Biaya Pemesanan (A)',
                'Biaya Simpan (h)',
                'Biaya Kekurangan (p)',
                'Standar Deviasi (σ)',
                'XR',
                'XR+L',
                'Qp (Pemesanan Optimal)',
                'z Value',
                'Sp (Batas Persediaan)',
                'Reorder Point (s)',
                'Max Inventory (S)'
            ]);

            foreach ($calculations as $calc) {
                fputcsv($file, [
                    $calc->created_at->format('Y-m-d H:i:s'),
                    $calc->product->nama_produk,
                    $calc->item_price,
                    $calc->start_date ? $calc->start_date->format('Y-m-d') : '-',
                    $calc->end_date ? $calc->end_date->format('Y-m-d') : '-',
                    $calc->d,
                    $calc->r_period,
                    $calc->lead_time,
                    $calc->ordering_cost,
                    $calc->holding_cost,
                    $calc->shortage_cost,
                    $calc->sigma,
                    $calc->xr,
                    $calc->xr_l,
                    $calc->qp,
                    $calc->z_value,
                    $calc->sp,
                    $calc->reorder_point,
                    $calc->max_inventory
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Standard Normal Probability Density Function f(z)
     */
    private function stdNormalPdf($z)
    {
        return (1.0 / sqrt(2.0 * M_PI)) * exp(-$z * $z / 2.0);
    }

    /**
     * Standard Normal Cumulative Distribution Function psi(z)
     */
    private function stdNormalCdf($z)
    {
        $t = 1.0 / (1.0 + 0.2316419 * abs($z));
        $d = 0.39894228040143 * exp(-$z * $z / 2.0);
        $p = $d * $t * (0.319381530 + $t * (-0.356563782 + $t * (1.781477937 + $t * (-1.821255978 + $t * 1.330274429))));
        if ($z > 0) {
            return 1.0 - $p;
        }
        return $p;
    }

    /**
     * Standard Normal Inverse Cumulative Distribution Function
     * Beasley-Springer-Moro or rational approximation for finding Z_alpha from alpha
     */
    private function stdNormalInverseCdf($p)
    {
        if ($p <= 0.0001) return -3.89; // Clip values for numerical stability
        if ($p >= 0.9999) return 3.89;
        
        if ($p < 0.5) {
            $t = sqrt(-2.0 * log($p));
            return -($t - ((2.515517 + 0.802853 * $t + 0.010328 * $t * $t) / 
                          (1.0 + 1.432788 * $t + 0.189269 * $t * $t + 0.001308 * $t * $t * $t)));
        } else {
            $t = sqrt(-2.0 * log(1.0 - $p));
            return $t - ((2.515517 + 0.802853 * $t + 0.010328 * $t * $t) / 
                         (1.0 + 1.432788 * $t + 0.189269 * $t * $t + 0.001308 * $t * $t * $t));
        }
    }
}

