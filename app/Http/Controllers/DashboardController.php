<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryCalculation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with stats and graphs.
     */
    public function index()
    {
        // 1. Statistics
        $totalProducts = Product::count();
        $totalCalculations = InventoryCalculation::count();
        
        $expensiveProduct = Product::orderBy('harga_produk', 'desc')->first();
        $cheapestProduct = Product::orderBy('harga_produk', 'asc')->first();
        
        // 2. Latest Calculation Details
        $latestCalculation = InventoryCalculation::with('product')
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. Chart Data
        // A. Product Prices Chart (Top 10 most expensive or simply first 10 products)
        $priceProducts = Product::orderBy('harga_produk', 'desc')->take(10)->get();
        $priceLabels = $priceProducts->pluck('nama_produk')->toArray();
        $priceValues = $priceProducts->pluck('harga_produk')->toArray();

        // B. Qp, Rop, Max Inventory charts based on the 10 most recent calculations
        $recentCalculations = InventoryCalculation::with('product')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse(); // chronological order

        $recentLabels = $recentCalculations->map(function ($calc) {
            return $calc->product->nama_produk . ' (' . $calc->created_at->format('d M') . ')';
        })->toArray();

        $recentQp = $recentCalculations->pluck('qp')->toArray();
        $recentRop = $recentCalculations->pluck('reorder_point')->toArray();
        $recentMaxInv = $recentCalculations->pluck('max_inventory')->toArray();

        // C. Monthly Calculations trend (last 6 months)
        $monthlyCounts = InventoryCalculation::selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%M %Y") as month')
            ->groupBy('month')
            ->orderByRaw('MIN(created_at) desc')
            ->take(6)
            ->get()
            ->reverse();

        $historyLabels = $monthlyCounts->pluck('month')->toArray();
        $historyValues = $monthlyCounts->pluck('count')->toArray();

        return view('dashboard', compact(
            'totalProducts',
            'totalCalculations',
            'expensiveProduct',
            'cheapestProduct',
            'latestCalculation',
            'priceLabels',
            'priceValues',
            'recentLabels',
            'recentQp',
            'recentRop',
            'recentMaxInv',
            'historyLabels',
            'historyValues'
        ));
    }
}
