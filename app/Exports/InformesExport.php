<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InformesExport implements FromView
{
    public function __construct(
        private readonly array $report,
        private readonly array $filters
    ) {
    }

    public function view(): View
    {
        return view('informes.export', [
            'report' => $this->report,
            'filters' => $this->filters,
        ]);
    }
}
