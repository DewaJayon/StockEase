<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{

    protected $fillable = [
        'sale_id',
        'gateway',
        'external_id',
        'status',
        'amount',
        'payment_type',
        'raw_response',
    ];

    public function isPaid()
    {
        return in_array($this->status, ['settlement', 'success', 'capture']);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
