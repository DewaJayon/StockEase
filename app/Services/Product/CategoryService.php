<?php

namespace App\Services\Product;

use App\Models\Category;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    /**
     * Get paginated categories with searching.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCategories(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Store a new category.
     *
     * @param  array{name: string}  $data
     */
    public function storeCategory(array $data): Category
    {
        $slug = SlugService::createSlug(Category::class, 'slug', $data['name']);

        return Category::create([
            'slug' => $slug,
            'name' => $data['name'],
        ]);
    }

    /**
     * Update an existing category.
     *
     * @param  array{name: string}  $data
     */
    public function updateCategory(Category $category, array $data): bool
    {
        $payload = [
            'name' => $data['name'],
        ];

        if ($category->name !== $data['name']) {
            $payload['slug'] = SlugService::createSlug(Category::class, 'slug', $data['name']);
        }

        return $category->update($payload);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): ?bool
    {
        return $category->delete();
    }
}
