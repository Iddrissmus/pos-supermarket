<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class BarcodeService
{
    /**
     * Generate a unique barcode number for a product
     * Uses EAN-13 format (13 digits)
     */
    public function generateBarcodeNumber(): string
    {
        do {
            // Generate random 12-digit number
            $code = '20' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            
            // Add check digit (EAN-13 standard)
            $code .= $this->calculateEAN13CheckDigit($code);
            
        } while (Product::where('barcode', $code)->exists());
        
        return $code;
    }

    /**
     * Calculate EAN-13 check digit
     * This ensures barcode is valid
     */
    private function calculateEAN13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Generate barcode IMAGE (PNG format)
     * Returns base64 encoded image for display
     */
    public function generateBarcodeImage(string $barcodeNumber): string
    {
        $generator = new BarcodeGeneratorPNG();
        return base64_encode(
            $generator->getBarcode($barcodeNumber, $generator::TYPE_EAN_13, 3, 80)
        );
    }

    /**
     * Generate QR code and save to storage
     * Returns the file path
     */
    public function generateQRCode(Product $product): string
    {
        // QR Code will contain: Product ID, Name, Price, Barcode
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price ?? 0,
            'barcode' => $product->barcode,
        ];
        
        $filename = 'product_' . $product->id . '.svg';
        $path = 'qrcodes/' . $filename;
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/public/qrcodes'))) {
            mkdir(storage_path('app/public/qrcodes'), 0755, true);
        }
        
        // Generate and save QR code as SVG (doesn't require imagick)
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate(json_encode($data));
        
        // Save to storage
        file_put_contents(storage_path('app/public/' . $path), $qrCode);
        
        return $path;
    }
}