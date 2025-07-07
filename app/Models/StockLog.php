<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
