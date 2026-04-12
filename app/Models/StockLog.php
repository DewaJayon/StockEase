<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'reference_type',
        'reference_id',
        'qty',
        'note',
    ];

    /**
     * Get the product that owns the stock log.
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
