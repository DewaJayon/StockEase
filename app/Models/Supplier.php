<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        "slug",
        "name",
        "phone",
        "address"
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function purcases()
    {
        return $this->hasMany(Purcase::class);
    }
}
