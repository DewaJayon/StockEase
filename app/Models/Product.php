<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'slug',
        'name',
        'sku',
        'barcode',
        'unit',
        'stock',
        'purchase_price',
        'selling_price',
        'alert_stock',
        'image_path',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurcaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
