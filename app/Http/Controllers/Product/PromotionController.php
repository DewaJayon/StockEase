<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\Product\PromotionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        $promotions = $this->promotionService->getPaginatedPromotions(
            $request->only(['search', 'start', 'end']),
            $perPage
        );

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Promotions/Index', [
            'promotions' => $promotions,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request)
    {
        Promotion::create($request->validated());

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        $promotion->update($request->validated());

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil dihapus.');
    }
}
