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
            // 1. XR = D * R
            $xr = $d * $r_period;

            // 2. XR+L = D * (R + L)
            $xr_l = $d * ($r_period + $lead_time);

            // 3. Qp = 1.3 * XR^0.494 * ( A / h )^0.506 * ( 1 + sigma^2 / XR^2 )^0.116
            $term_xr = pow($xr, 0.494);
            $term_a_h = pow($ordering_cost / $holding_cost, 0.506);
            $term_ratio = 1.0 + (pow($sigma, 2) / pow($xr, 2));
            $term_ratio_pow = pow($term_ratio, 0.116);
            $qp = 1.3 * $term_xr * $term_a_h * $term_ratio_pow;

            // 4. z = Qp / sigma
            $z_value = $qp / $sigma;

            // 5. Sp = 0.973 * XR+L + sigma * ( 0.183 / z + 1.063 - 2.192 * z )
            $sp_z_term = (0.183 / $z_value) + 1.063 - (2.192 * $z_value);
            $sp = (0.973 * $xr_l) + ($sigma * $sp_z_term);

            // 6. Reorder Point (s) = Sp
            $reorder_point = $sp;

            // 7. Maximum Inventory (S) = Sp + Qp
            $max_inventory = $sp + $qp;

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
                'r_period' => $r_period,
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
     * Generate the 20 iterations for the Hadley-Whitin model dynamically.
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
        
        // T_EOQ = sqrt((2 * A) / (h * D))
        $t_eoq = sqrt((2 * $ordering_cost) / ($holding_cost * $d));
        
        $rows = [];
        $previous_total = null;
        for ($i = 1; $i <= 20; $i++) {
            $t0 = $t_eoq - ($i - 1) * 0.010;
            
            // alpha
            $alpha = ($t0 * $holding_cost) / $shortage_cost;
            
            // Za
            $za = 0.50 - 0.39 * $alpha;
            
            // f(Za)
            $f_za = 0.30 + 0.20 * $za;
            
            // psi(Za) (Normal loss function)
            $psi_za = 0.361 - 0.326 * $za;
            
            // N (Safety Stock / Expected Shortage)
            // Formula II-7: N = sigma * sqrt(T + L) * [f(Za) - Za * alpha]
            $t0_l = max(0.0001, $t0 + $lead_time);
            $n = $sigma * sqrt($t0_l) * ($f_za - $za * $alpha);
            
            // R (ROP)
            // Formula II-6: R = D(T + L) + Za * sigma * sqrt(T + L)
            $r = $d * $t0_l + $za * $sigma * sqrt($t0_l);
            
            // Ob (purchase cost)
            $ob = $v * $d;
            
            // Op (ordering cost)
            $op = abs($t0) > 0.00001 ? ($ordering_cost / $t0) : INF;
            
            // Os (holding cost)
            // Os = h * ( (D * T0 / 2) + Za * sigma * sqrt(T + L) )
            $os = $holding_cost * (($d * $t0) / 2 + $za * $sigma * sqrt($t0_l));
            
            // Ok (shortage cost)
            // Ok = p * N / T0
            $ok = abs($t0) > 0.00001 ? (($shortage_cost * $n) / $t0) : INF;
            
            $total = $ob + $op + $os + $ok;
            
            $rows[] = [
                'no' => $i,
                't0' => $t0,
                'alpha' => $alpha,
                'za' => $za,
                'f_za' => $f_za,
                'psi_za' => $psi_za,
                'r' => $r,
                'n' => $n,
                'ob' => $ob,
                'op' => $op,
                'os' => $os,
                'ok' => $ok,
                'total' => $total
            ];

            if ($previous_total !== null && $total > $previous_total) {
                // Stopped because a minimum total cost has been reached and cost starts to rise
                break;
            }
            $previous_total = $total;
        }
        
        // Find optimal (minimum cost where t0 > 0)
        $valid_min = INF;
        $optimal_no = -1;
        foreach ($rows as $row) {
            if ($row['t0'] > 0 && $row['total'] < $valid_min) {
                $valid_min = $row['total'];
                $optimal_no = $row['no'];
            }
        }
        
        // Assign statuses
        foreach ($rows as &$row) {
            if ($row['no'] == $optimal_no) {
                $row['status'] = 'OPTIMAL';
            } elseif ($row['t0'] <= 0) {
                $row['status'] = 'TERAKHIR';
            } else {
                $row['status'] = 'LANJUT';
            }
        }
        unset($row);
        
        return $rows;
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
}
