<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{

    protected $fillable = [
        'product_id',
        'type',
        'reference_type',
        'reference_id',
        'qty',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
