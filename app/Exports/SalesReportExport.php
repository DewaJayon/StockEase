<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesReportExport implements FromView
{
    protected $sales;
    protected $filters;
    protected $summary;

    public function __construct($sales, $filters, $summary)
    {
        $this->sales = $sales;
        $this->filters = $filters;
        $this->summary = $summary;
    }

    public function view(): View
    {
        return view('exports.sales.excel', [
            'sales'   => $this->sales,
            'filters' => $this->filters,
            'summary' => $this->summary,
        ]);
    }
}
