<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'harga_produk',
        'barang_masuk',
        'barang_keluar',
    ];

    /**
     * Get the latest Hadley-Whitin calculation for the product.
     */
    public function latestCalculation()
    {
        return $this->hasOne(InventoryCalculation::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the current stock (stok = barang masuk - barang keluar).
     */
    public function getStokAttribute()
    {
        return $this->barang_masuk - $this->barang_keluar;
    }

    /**
     * Get the latest reorder point (s).
     */
    public function getReorderPointAttribute()
    {
        $latest = $this->latestCalculation;
        return $latest ? $latest->reorder_point : null;
    }

    /**
     * Get the calculation history for this product.
     */
    public function calculations(): HasMany
    {
        return $this->hasMany(InventoryCalculation::class);
    }

    /**
     * Get the historical demands for this product.
     */
    public function demands(): HasMany
    {
        return $this->hasMany(ProductDemand::class);
    }

    /**
     * Get demand statistics for this product.
     */
    public function getDemandStats()
    {
        $demands = $this->demands->pluck('jumlah_permintaan')->toArray();
        $count = count($demands);
        
        if ($count === 0) {
            return [
                'avg_monthly' => 0,
                'std_monthly' => 0,
                'avg_yearly' => 0,
                'std_yearly' => 0,
            ];
        }

        // Monthly Mean
        $avgMonthly = array_sum($demands) / $count;

        // Monthly Standard Deviation (sample std dev)
        $sumSquareDiffs = 0;
        foreach ($demands as $val) {
            $sumSquareDiffs += pow($val - $avgMonthly, 2);
        }
        $variance = $count > 1 ? $sumSquareDiffs / ($count - 1) : 0;
        $stdMonthly = sqrt($variance);

        // Yearly Scale
        $avgYearly = $avgMonthly * 12;
        $stdYearly = $stdMonthly; // Do not multiply by sqrt(12)

        return [
            'avg_monthly' => round($avgMonthly, 4),
            'std_monthly' => round($stdMonthly, 4),
            'avg_yearly' => round($avgYearly, 4),
            'std_yearly' => round($stdYearly, 4),
        ];
    }
}
