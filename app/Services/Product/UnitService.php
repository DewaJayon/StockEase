<?php

namespace App\Services\Product;

use App\Models\Unit;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UnitService
{
    /**
     * Get paginated units with searching.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedUnits(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Unit::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('short_name', 'like', '%'.$search.'%');
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new unit.
     *
     * @param  array{name: string, short_name: string}  $data
     */
    public function storeUnit(array $data): Unit
    {
        $slug = SlugService::createSlug(Unit::class, 'slug', $data['name']);

        return Unit::create([
            'slug' => $slug,
            'name' => $data['name'],
            'short_name' => $data['short_name'],
        ]);
    }

    /**
     * Update an existing unit.
     *
     * @param  array{name: string, short_name: string}  $data
     */
    public function updateUnit(Unit $unit, array $data): bool
    {
        $payload = [
            'name' => $data['name'],
            'short_name' => $data['short_name'],
        ];

        if ($unit->name !== $data['name']) {
            $payload['slug'] = SlugService::createSlug(Unit::class, 'slug', $data['name']);
        }

        return $unit->update($payload);
    }

    /**
     * Delete a unit.
     */
    public function deleteUnit(Unit $unit): ?bool
    {
        return $unit->delete();
    }
}
