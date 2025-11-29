<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BulkAssignmentTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample data rows
        return [
            [
                'Royal Stallion Rice 25kg',
                'Main Branch',
                10,
                1,
                150.00,
                120.00,
                5,
                6.00,    // Price per kilo
                150.00,  // Price per box
                'kg',    // Weight unit
                6.00,    // Price per unit weight
            ],
            [
                'Frytol Cooking Oil 1L',
                'Main Branch',
                20,
                12,
                25.50,
                18.00,
                10,
                '',
                '',
                '',
                '',
            ],
            [
                'Golden Tree Sugar 1kg',
                'East Branch',
                15,
                20,
                8.50,
                6.00,
                8,
                8.50,
                170.00,
                'kg',
                8.50,
            ],
            [
                'Gino Tomato Paste 70g',
                'West Branch',
                30,
                50,
                3.50,
                2.50,
                20,
                '',
                '',
                '',
                '',
            ],
            [
                'Nido Milk Powder 400g',
                'Main Branch',
                12,
                24,
                35.00,
                28.00,
                6,
                87.50,
                840.00,
                'kg',
                87.50,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name or Barcode',
            'Branch Name',
            'Quantity of Boxes',
            'Units per Box',
            'Selling Price',
            'Cost Price',
            'Reorder Level',
            'Price per Kilo',
            'Price per Box',
            'Weight Unit',
            'Price per Unit Weight',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'], // Blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
