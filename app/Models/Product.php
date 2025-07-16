<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Sluggable;

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

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

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

    public static function reduceStockFromSaleItems($saleItems)
    {
        $productIds = $saleItems->pluck('product_id')->toArray();
        $products = self::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

        foreach ($saleItems as $item) {
            $product = $products[$item->product_id];

            if ($product->stock < $item->qty) {
                throw new \Exception("Stok produk {$product->name} tidak cukup.");
            }

            $product->decrement('stock', $item->qty);

            StockLog::create([
                'product_id'     => $product->id,
                'qty'            => $item->qty,
                'type'           => 'out',
                'reference_type' => 'Sale',
                'reference_id'   => $item->sale_id,
                'note'           => 'Penjualan produk ' . $product->name,
            ]);
        }
    }
}
