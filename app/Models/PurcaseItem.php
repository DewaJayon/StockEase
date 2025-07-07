<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurcaseItem extends Model
{

    public function purcase()
    {
        return $this->belongsTo(Purcase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
