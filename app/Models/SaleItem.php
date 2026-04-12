<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'price',
    ];

    /**
     * Define the model's castable attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
        ];
    }

    /**
     * Get the sale that this sale item belongs to.
     *
     * @return BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product that this sale item belongs to.
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
