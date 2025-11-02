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
        // Return sample data rows with realistic supermarket products using CURRENT categories
        return [
            [
                'Royal Stallion Rice 25kg',
                'Rice & Grains',
                'Premium quality rice in 25kg bags',
                10,
                1,
                150.00,
                120.00,
                5
            ],
            [
                'Frytol Cooking Oil 1L',
                'Oils & Fats',
                'Pure vegetable cooking oil',
                20,
                12,
                25.50,
                18.00,
                10
            ],
            [
                'Golden Tree Sugar 1kg',
                'Sugar & Sweeteners',
                'White granulated sugar',
                15,
                20,
                8.50,
                6.00,
                8
            ],
            [
                'Gari White 1kg',
                'Rice & Grains',
                'Fine quality gari',
                25,
                10,
                12.00,
                9.00,
                12
            ],
            [
                'Gino Tomato Paste 70g',
                'Canned & Packaged Foods',
                'Tomato paste in tin',
                30,
                50,
                3.50,
                2.50,
                20
            ],
            [
                'Geisha Sardines 125g',
                'Canned & Packaged Foods',
                'Sardines in tomato sauce',
                20,
                48,
                6.50,
                4.80,
                15
            ],
            [
                'Nido Milk Powder 400g',
                'Milk',
                'Full cream milk powder',
                12,
                24,
                35.00,
                28.00,
                6
            ],
            [
                'Milo 400g',
                'Tea & Coffee',
                'Chocolate malt drink',
                10,
                12,
                42.00,
                35.00,
                5
            ],
            [
                'Indomie Chicken Noodles',
                'Pasta & Noodles',
                'Instant noodles - Chicken flavor',
                50,
                40,
                1.50,
                1.00,
                30
            ],
            [
                'Coca Cola 500ml',
                'Soft Drinks',
                'Carbonated soft drink',
                40,
                24,
                4.50,
                3.20,
                25
            ],
            [
                'Bread Large',
                'Bread',
                'Freshly baked bread',
                20,
                20,
                6.00,
                4.50,
                15
            ],
            [
                'Fresh Eggs (Crate)',
                'Eggs',
                'Fresh eggs 30 pieces per crate',
                8,
                1,
                45.00,
                38.00,
                3
            ],
            [
                'Rose Toilet Paper 12 Rolls',
                'Tissue & Paper Products',
                'Soft toilet tissue',
                15,
                10,
                28.00,
                22.00,
                8
            ],
            [
                'Omo Detergent 1kg',
                'Detergents & Washing',
                'Washing powder',
                20,
                12,
                18.00,
                14.00,
                10
            ],
            [
                'Key Soap 6 Pack',
                'Soap & Body Wash',
                'Bath soap pack',
                25,
                20,
                15.00,
                11.00,
                12
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name *',
            'Category',
            'Description',
            'Quantity of Boxes *',
            'Units per Box *',
            'Selling Price',
            'Cost Price',
            'Reorder Level'
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
