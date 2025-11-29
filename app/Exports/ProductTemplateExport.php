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
                25.0, // Box weight in kg
                'weight', // Selling mode
                null, // Price (auto-calculated for weight-based)
                20.00, // Cost price
                8.00, // Price per kilo
                200.00, // Price per box
                null, // Weight unit (alternative)
                null, // Price per unit weight
            ],
            [
                'Frytol Cooking Oil 1L',
                'Oils & Fats',
                'Pure vegetable cooking oil',
                50,
                12,
                12.0,
                'unit',
                45.00,
                35.00,
                null,
                null,
                null,
                null,
            ],
            [
                'Golden Tree Sugar 1kg',
                'Sugar & Sweeteners',
                'White granulated sugar',
                200,
                20,
                20.0,
                'both',
                null,
                6.00,
                7.50,
                150.00,
                null,
                null,
            ],
            [
                'Gari White 1kg',
                'Rice & Grains',
                'Fine quality gari',
                150,
                10,
                10.0,
                'weight',
                null,
                5.00,
                6.00,
                60.00,
                null,
                null,
            ],
            [
                'Gino Tomato Paste 70g',
                'Canned & Packaged Foods',
                'Tomato paste in tin',
                500,
                50,
                3.5,
                'unit',
                2.50,
                1.80,
                null,
                null,
                null,
                null,
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
            'Box Weight (kg)',
            'Selling Mode',
            'Price',
            'Cost Price *',
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
