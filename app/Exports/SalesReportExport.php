<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
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

    public function title(): string
    {
        return 'Sales Report';
    }

    public function headings(): array
    {
        return [
            ['SALES PERFORMANCE REPORT'],
            ['Period: ' . $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y')],
            [],
            ['SUMMARY'],
            ['Total Sales', $this->summary['total_sales']],
            ['Total Revenue', 'GH₵' . number_format($this->summary['total_revenue'], 2)],
            ['Total COGS', 'GH₵' . number_format($this->summary['total_cogs'], 2)],
            ['Gross Profit', 'GH₵' . number_format($this->summary['total_profit'], 2)],
            ['Average Margin', number_format($this->summary['average_margin'], 2) . '%'],
            ['Total Items', $this->sales->sum(fn($s) => $s->items->count())],
            ['Total Quantity', $this->sales->sum(fn($s) => $s->items->sum('quantity'))],
            [],
            ['DETAILED TRANSACTIONS'],
            [
                'Sale ID',
                'Branch',
                'Cashier',
                'Date',
                'Time',
                'Revenue (GH₵)',
                'COGS (GH₵)',
                'Profit (GH₵)',
                'Margin (%)',
                'Items Count',
                'Total Quantity',
                'Payment Method',
            ],
        ];
    }

    public function collection(): Collection
    {
        return $this->sales->map(function ($sale) {
            $cogs = $sale->items->sum('total_cost') ?? 0;
            $profit = $sale->total - $cogs;
            $margin = $sale->total > 0 ? ($profit / $sale->total) * 100 : 0;

            return [
                $sale->id,
                optional($sale->branch)->name ?? 'N/A',
                optional($sale->cashier)->name ?? 'N/A',
                $sale->created_at->format('Y-m-d'),
                $sale->created_at->format('H:i:s'),
                'GH₵' . number_format($sale->total, 2),
                'GH₵' . number_format($cogs, 2),
                'GH₵' . number_format($profit, 2),
                number_format($margin, 2) . '%',
                $sale->items->count(),
                $sale->items->sum('quantity'),
                ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')),
            ];
        });
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style for title row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '1E3A8A'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Style for period row
            2 => [
                'font' => [
                    'size' => 11,
                    'italic' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Style for SUMMARY header
            4 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ],
            
            // Style for summary rows (5-11)
            '5:11' => [
                'font' => [
                    'size' => 10,
                ],
            ],
            
            // Style for DETAILED TRANSACTIONS header
            13 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ],
            
            // Style for column headers
            14 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Auto-size columns
                foreach(range('A','L') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Merge title cell
                $event->sheet->getDelegate()->mergeCells('A1:L1');
                $event->sheet->getDelegate()->mergeCells('A2:L2');
                
                // Add borders to data section
                $lastRow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->getStyle('A14:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
                
                // Add totals row
                $totalRow = $lastRow + 1;
                $event->sheet->getDelegate()->setCellValue('A' . $totalRow, 'TOTALS');
                $event->sheet->getDelegate()->mergeCells('A' . $totalRow . ':E' . $totalRow);
                $event->sheet->getDelegate()->setCellValue('F' . $totalRow, '₵' . number_format($this->summary['total_revenue'], 2));
                $event->sheet->getDelegate()->setCellValue('G' . $totalRow, '₵' . number_format($this->summary['total_cogs'], 2));
                $event->sheet->getDelegate()->setCellValue('H' . $totalRow, '₵' . number_format($this->summary['total_profit'], 2));
                $event->sheet->getDelegate()->setCellValue('I' . $totalRow, number_format($this->summary['average_margin'], 2) . '%');
                $event->sheet->getDelegate()->setCellValue('J' . $totalRow, $this->sales->sum(fn($s) => $s->items->count()));
                $event->sheet->getDelegate()->setCellValue('K' . $totalRow, $this->sales->sum(fn($s) => $s->items->sum('quantity')));
                
                // Style totals row
                $event->sheet->getDelegate()->getStyle('A' . $totalRow . ':L' . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => '9CA3AF'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
