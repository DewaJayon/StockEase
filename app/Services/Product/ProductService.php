<?php

namespace App\Services\Product;

use App\Models\Product;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Get paginated products with searching and category.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedProducts(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Product::with(['category', 'unit'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('alert_stock', 'like', "%{$search}%")
                    ->orWhereHas('unit', function ($queryUnit) use ($search) {
                        $queryUnit->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($queryCategory) use ($search) {
                        $queryCategory->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new product.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeProduct(array $data, ?UploadedFile $image = null): Product
    {
        $data['slug'] = SlugService::createSlug(Product::class, 'slug', $data['name']);

        if ($image) {
            $data['image_path'] = $this->uploadImage($image);
        }

        return Product::create($data);
    }

    /**
     * Update an existing product.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): bool
    {
        if ($data['name'] !== $product->name) {
            $data['slug'] = SlugService::createSlug(Product::class, 'slug', $data['name']);
        }

        if ($image) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = $this->uploadImage($image);
        }

        return $product->update($data);
    }

    /**
     * Delete a product and its image.
     */
    public function deleteProduct(Product $product): bool
    {
        $this->deleteImage($product->image_path);

        return $product->delete();
    }

    /**
     * Upload product image.
     */
    private function uploadImage(UploadedFile $image): string
    {
        $imagePath = 'product';
        $imageName = time().'.'.$image->getClientOriginalExtension();

        Storage::disk('public')->put($imagePath.'/'.$imageName, file_get_contents($image));

        return "storage/{$imagePath}/{$imageName}";
    }

    /**
     * Delete product image.
     */
    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath) {
            $filePath = Str::chopStart($imagePath, 'storage/');
            Storage::disk('public')->delete($filePath);
        }
    }
}
