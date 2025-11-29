<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ItemRequestTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * Return the sample data for the template
     */
    public function array(): array
    {
        return [
            [
                'Milo 400g',
                'Main Branch',
                5,
                24,
                'Stock replenishment for high demand product'
            ],
            [
                'Peak Milk 400g',
                'East Branch',
                10,
                12,
                'Running low on stock'
            ],
            [
                'Rice (5kg)',
                'West Branch',
                20,
                10,
                'Preparing for weekend sales'
            ],
            [
                'Cooking Oil (1L)',
                'North Branch',
                15,
                12,
                'Customer pre-orders'
            ],
            [
                'Sugar (1kg)',
                'Main Branch',
                8,
                20,
                'Regular stock rotation'
            ],
        ];
    }

    /**
     * Return the column headings
     */
    public function headings(): array
    {
        return [
            'Product Name or Barcode',
            'From Branch',
            'Quantity of Boxes',
            'Units per Box',
            'Reason (Optional)',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Blue background
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(40);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Style data rows with alternating colors
        for ($i = 2; $i <= 6; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:E{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ],
                ]);
            }
        }

        // Add borders to all cells
        $sheet->getStyle('A1:E6')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return [];
    }
}
