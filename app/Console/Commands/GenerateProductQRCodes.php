<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\BarcodeService;

class GenerateProductQRCodes extends Command
{
    protected $signature = 'products:generate-qrcodes';
    protected $description = 'Generate QR codes for all products';

    public function handle(BarcodeService $barcodeService)
    {
        $products = Product::all();
        $bar = $this->output->createProgressBar(count($products));
        
        foreach ($products as $product) {
            try {
                $path = $barcodeService->generateQRCode($product);
                $this->info("Generated QR code for: {$product->name} -> {$path}");
            } catch (\Exception $e) {
                $this->error("Failed for {$product->name}: " . $e->getMessage());
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nQR codes generated successfully!");
    }
}