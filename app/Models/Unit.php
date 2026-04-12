<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'short_name',
        'slug',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the sluggable configuration for the model.
     *
     * @return array<string, mixed>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Get the products that belong to this unit.
     *
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
