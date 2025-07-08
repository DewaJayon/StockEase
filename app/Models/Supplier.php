<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use Sluggable;

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

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function purcases(): HasMany
    {
        return $this->hasMany(Purcase::class);
    }
}
