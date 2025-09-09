<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StockExportExcel implements FromView
{
    protected $filters;
    protected $summary;

    public function __construct($filters, $summary)
    {
        $this->filters = $filters;
        $this->summary = $summary;
    }

    public function view(): View
    {
        return view('exports.stock-report.export-excel', [
            'filters'           => $this->filters,
            'filteredStocks'    => $this->summary
        ]);
    }
}
