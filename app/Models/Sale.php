<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'user_id',
        'customer_name',
        'total',
        'payment_method',
        'paid',
        'change',
        'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class);
    }

    public function calculateTotal()
    {
        $total = 0;

        if (!$this->relationLoaded('saleItems')) {
            $this->load('saleItems.product');
        }

        foreach ($this->saleItems as $item) {
            $total += $item->price * $item->qty;
        }

        $this->update([
            'total' => $total
        ]);

        return $total;
    }
}
