<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::select('slug', 'name')->get();

        $categories = $categories->map(function ($category) {
            return [
                'value' => $category->slug,
                'label' => $category->name,
            ];
        });

        $categoryFilter = $request->category;

        $products = Product::query()
            ->when($categoryFilter, function ($query, $categoryFilter) {
                $query->whereHas('category', function ($queryCategory) use ($categoryFilter) {
                    $queryCategory->where('slug', $categoryFilter);
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();


        return Inertia::render('Pos/Index', [
            'products'      => $products,
            'categories'    => $categories
        ]);
    }
}
