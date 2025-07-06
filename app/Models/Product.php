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
}
