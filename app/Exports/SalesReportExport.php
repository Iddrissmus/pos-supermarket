<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    private Collection $sales;
    private array $summary;
    private $startDate;
    private $endDate;

    public function __construct(Collection $sales, array $summary, $startDate, $endDate)
    {
        $this->sales = $sales;
        $this->summary = $summary;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function headings(): array
    {
        return [
            'Sale ID',
            'Branch',
            'Cashier',
            'Date',
            'Revenue',
            'COGS',
            'Profit',
            'Margin %',
            'Items Count',
            'Total Quantity',
        ];
    }

    public function collection(): Collection
    {
        return $this->sales->map(function ($sale) {
            $cogs = $sale->items->sum('total_cost');
            $profit = $sale->total - $cogs;
            $margin = $sale->total > 0 ? ($profit / $sale->total) * 100 : 0;

            return [
                $sale->id,
                $sale->branch->name ?? 'N/A',
                optional($sale->cashier)->name ?? 'N/A',
                $sale->created_at->format('Y-m-d H:i:s'),
                $sale->total,
                $cogs,
                $profit,
                round($margin, 2),
                $sale->items->count(),
                $sale->items->sum('quantity'),
            ];
        });
    }
}
