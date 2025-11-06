<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample data - products to be created (NOT assigned to branches yet)
        return [
            [
                'Royal Stallion Rice 25kg',
                'Rice & Grains',
                'Premium quality rice in 25kg bags',
                100,  // Total boxes in warehouse
                1,    // Units per box
            ],
            [
                'Frytol Cooking Oil 1L',
                'Oils & Fats',
                'Pure vegetable cooking oil',
                50,
                12,
            ],
            [
                'Golden Tree Sugar 1kg',
                'Sugar & Sweeteners',
                'White granulated sugar',
                200,
                20,
            ],
            [
                'Gari White 1kg',
                'Rice & Grains',
                'Fine quality gari',
                150,
                10,
            ],
            [
                'Gino Tomato Paste 70g',
                'Canned & Packaged Foods',
                'Tomato paste in tin',
                500,
                50,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name *',
            'Category *',
            'Description',
            'Total Boxes',
            'Units per Box *',
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
