<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDemand extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'periode',
        'jumlah_permintaan',
    ];

    protected $casts = [
        'periode' => 'date',
        'jumlah_permintaan' => 'integer',
    ];

    /**
     * Get the product that owns this demand record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
