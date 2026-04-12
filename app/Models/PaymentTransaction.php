<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'gateway',
        'external_id',
        'status',
        'amount',
        'payment_type',
        'raw_response',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
        ];
    }

    /**
     * Check if the payment has been paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        return in_array($this->status, ['settlement', 'success', 'capture']);
    }

    /**
     * Get the sale that owns the payment transaction.
     *
     * @return BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
