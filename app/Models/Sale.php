<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'total',
        'payment_method',
        'paid',
        'change',
        'date',
        'status',
    ];

    /**
     * Define the model's castable attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:4',
            'paid' => 'decimal:4',
            'change' => 'decimal:4',
        ];
    }

    /**
     * Get the user that created the sale.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale items that belong to the sale.
     *
     * @return HasMany
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the payment transaction that belongs to the sale.
     *
     * @return HasOne
     */
    public function paymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class);
    }

    /**
     * Calculate the total of a sale by summing up all sale items prices
     * and updating the sale record.
     *
     * @return float The total of the sale
     */
    public function calculateTotal()
    {
        $total = 0;

        if (! $this->relationLoaded('saleItems')) {
            $this->load('saleItems.product');
        }

        foreach ($this->saleItems as $item) {
            $total += $item->price * $item->qty;
        }

        $this->update([
            'total' => $total,
        ]);

        return $total;
    }
}
