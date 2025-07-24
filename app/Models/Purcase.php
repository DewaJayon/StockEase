<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purcase extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'total',
        'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purcaseItems()
    {
        return $this->hasMany(PurcaseItem::class);
    }
}
