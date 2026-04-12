<?php

namespace App\Models;

use App\Actions\NotifyStockAlert;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'category_id',
        'slug',
        'name',
        'sku',
        'barcode',
        'unit_id',
        'stock',
        'purchase_price',
        'selling_price',
        'alert_stock',
        'image_path',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
        ];
    }

    /**
     * Return the sluggable configuration for the model.
     *
     * @return array<string, mixed>
     *
     * @see Sluggable
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
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the category that the product belongs to.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the unit that the product belongs to.
     *
     * @return BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the purchase items that belong to the product.
     *
     * @return HasMany
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the sale items that belong to the product.
     *
     * @return HasMany
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the stock logs that belong to the product.
     *
     * @return HasMany
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Reduce the stock of a product based on the given sale items.
     */
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
            $product->refresh(); // Refresh to get the actual stock value after decrement

            if ($product->stock <= $product->alert_stock) {
                (new NotifyStockAlert)->execute($product);
            }

            StockLog::create([
                'product_id' => $product->id,
                'qty' => $item->qty,
                'type' => 'out',
                'reference_type' => 'Sale',
                'reference_id' => $item->sale_id,
                'note' => 'Penjualan produk '.$product->name,
            ]);
        }
    }
}
