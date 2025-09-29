<?php

namespace App\Http\Controllers\Dashboard;

use Inertia\Inertia;
use App\Enums\UnitEnum;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Cviebrock\EloquentSluggable\Services\SlugService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('alert_stock', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($queryCategory) use ($search) {
                        $queryCategory->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Product/Index', [
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = UnitEnum::options();

        $categories = Category::select('id', 'name')->get();

        $categories = $categories->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        });

        return Inertia::render('Product/form/ProductCreateForm', [
            'units'         => $units,
            'categories'    => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = SlugService::createSlug(Product::class, 'slug', $data['name']);

        if ($request->hasFile('image')) {
            $imagePath = "product";

            $image = $request->file('image');

            $imageName = time() . '.' . $image->getClientOriginalExtension();

            Storage::disk('public')->put($imagePath . '/' . $imageName, file_get_contents($image));

            $data['image_path'] = "storage/{$imagePath}/{$imageName}";
        }

        Product::create($data);

        return redirect()->route('product.index')->with('success', 'Product berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return Inertia::render('Product/Show', [
            'product' => $product->load('category')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {

        $units = UnitEnum::options();

        $categories = Category::select('id', 'name')->get();

        $categories = $categories->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        });

        return Inertia::render('Product/form/ProductEditForm', [
            'product'       => $product,
            'units'         => $units,
            'categories'    => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($data['name'] != $product->name) {
            $data['slug'] = SlugService::createSlug(Product::class, 'slug', $data['name']);
        }

        if ($request->hasFile('image')) {

            if ($product->image_path) {
                $filePath = Str::chopStart($product->image_path, 'storage/');
                Storage::disk('public')->delete($filePath);
            }

            $imagePath = "product";

            $image = $request->file('image');

            $imageName = time() . '.' . $image->getClientOriginalExtension();

            Storage::disk('public')->put($imagePath . '/' . $imageName, file_get_contents($image));

            $data['image_path'] = "storage/{$imagePath}/{$imageName}";
        } else {
            $data['image_path'] = $product->image_path;
        }

        $product->update($data);

        return redirect()->route('product.index')->with('success', 'Product berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        try {
            $filePath = Str::chopStart($product->image_path, 'storage/');

            if ($product->image_path) {
                Storage::disk('public')->delete($filePath);
            }

            $product->delete();

            return redirect()->route('product.index')->with('success', 'Product berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Product gagal dihapus');
        }
    }
}
