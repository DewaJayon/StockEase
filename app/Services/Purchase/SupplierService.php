<?php

namespace App\Services\Purchase;

use App\Models\Supplier;
use Cviebrock\EloquentSluggable\Services\SlugService;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierService
{
    /**
     * Get paginated suppliers with searching.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedSuppliers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Supplier::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new supplier.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeSupplier(array $data): Supplier
    {
        $data['slug'] = SlugService::createSlug(Supplier::class, 'slug', $data['name']);

        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSupplier(Supplier $supplier, array $data): bool
    {
        if ($supplier->name !== ($data['name'] ?? $supplier->name)) {
            $data['slug'] = SlugService::createSlug(Supplier::class, 'slug', $data['name']);
        }

        return $supplier->update($data);
    }

    /**
     * Delete a supplier.
     */
    public function deleteSupplier(Supplier $supplier): bool
    {
        if ($supplier->purchases()->exists()) {
            throw new DomainException('Supplier has purchases.');
        }

        return $supplier->delete();
    }
}
