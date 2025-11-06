<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\BarcodeService;

class GenerateProductBarcodesAndQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-codes {--force : Regenerate even if barcode/QR code exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate barcodes and QR codes for all products that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle(BarcodeService $barcodeService)
    {
        $force = $this->option('force');
        
        // Get products that need barcodes or QR codes
        $query = Product::query();
        
        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('barcode')
                  ->orWhereNull('qr_code_path');
            });
        }
        
        $products = $query->get();
        
        if ($products->isEmpty()) {
            $this->info('No products need codes generated!');
            return 0;
        }
        
        $this->info("Processing {$products->count()} products...");
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        $generated = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($products as $product) {
            try {
                $updated = false;
                
                // Generate barcode if missing
                if (empty($product->barcode) || $force) {
                    $product->barcode = $barcodeService->generateBarcodeNumber();
                    $updated = true;
                }
                
                // Generate QR code if missing
                if (empty($product->qr_code_path) || $force) {
                    $qrPath = $barcodeService->generateQRCode($product);
                    $product->qr_code_path = $qrPath;
                    $updated = true;
                }
                
                if ($updated) {
                    $product->save();
                    $generated++;
                } else {
                    $skipped++;
                }
                
            } catch (\Exception $e) {
                $errors[] = "Product ID {$product->id} ({$product->name}): " . $e->getMessage();
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info("✓ Generated codes for {$generated} products");
        if ($skipped > 0) {
            $this->warn("⊘ Skipped {$skipped} products (already have codes)");
        }
        
        if (!empty($errors)) {
            $this->error("✗ Errors occurred:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }
        
        $this->newLine();
        $this->info('Done!');
        
        return 0;
    }
}
