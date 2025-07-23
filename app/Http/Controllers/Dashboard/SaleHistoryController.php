<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleHistoryController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {

        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $perPage = $request->input('per_page', 10);

        $sales = Sale::with('user', 'saleItems', 'saleItems.product', 'paymentTransaction')
            ->where('payment_method', '!=', 'pending')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhereHas('saleItems', function ($q) use ($search) {
                            $q->whereHas('product', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('sku', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%");
                            });
                        })
                        ->orWhereHas('paymentTransaction', function ($q) use ($search) {
                            $q->where('payment_method', 'like', "%{$search}%")
                                ->orWhere('status', 'like', "%{$search}%")
                                ->orWhere('external_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('updated_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Sale/Index', [
            'sales' => $sales
        ]);
    }


    /**
     * Display the specified sale details.
     *
     * Loads related user, sale items, products, and payment transaction details
     * for the given sale.
     *
     * @param \App\Models\Sale $sale
     * @return \Inertia\Response
     */

    public function show(Sale $sale)
    {
        $sale->load('user', 'saleItems', 'saleItems.product', 'paymentTransaction');

        return Inertia::render('Sale/Show', [
            'sale' => $sale
        ]);
    }


    /**
     * Export the specified sale details to a PDF file.
     *
     * Loads related user, sale items, products, and payment transaction details
     * for the given sale and generates a PDF document.
     *
     * @param \App\Models\Sale $sale
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToPdf(Sale $sale)
    {
        $sale->load('user', 'saleItems', 'saleItems.product', 'paymentTransaction');

        $pdf = Pdf::loadView('exports.sales.detail', [
            'sale' => $sale
        ]);

        $fileName = "invoice-{$sale->id}.pdf";

        return $pdf->download($fileName);
    }
}
