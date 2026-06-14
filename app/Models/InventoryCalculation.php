<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'start_date',
        'end_date',
        'd',
        'r_period',
        'lead_time',
        'ordering_cost',
        'item_price',
        'holding_cost',
        'shortage_cost',
        'sigma',
        'xr',
        'xr_l',
        'qp',
        'z_value',
        'sp',
        'reorder_point',
        'max_inventory',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'd' => 'float',
        'r_period' => 'float',
        'lead_time' => 'float',
        'ordering_cost' => 'float',
        'item_price' => 'float',
        'holding_cost' => 'float',
        'shortage_cost' => 'float',
        'sigma' => 'float',
        'xr' => 'float',
        'xr_l' => 'float',
        'qp' => 'float',
        'z_value' => 'float',
        'sp' => 'float',
        'reorder_point' => 'float',
        'max_inventory' => 'float',
    ];

    /**
     * Get the product that owns this calculation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
