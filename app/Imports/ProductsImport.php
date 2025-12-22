<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Services\BarcodeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $businessId;
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;
    protected $barcodeService;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
        $this->barcodeService = new BarcodeService();
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header row and 0-based index
                
                try {
                    // Skip if product name is empty
                    if (empty($row['product_name'])) {
                        $this->errors[] = "Row {$rowNumber}: Product name is required";
                        $this->skippedCount++;
                        continue;
                    }

            // Find or create category (case-insensitive, trimmed)
            $categoryName = trim($row['category'] ?? '');
            
            if (empty($categoryName)) {
                $this->errors[] = "Row {$rowNumber}: Category is required. Skipping product.";
                $this->skippedCount++;
                continue;
            }
            
            $category = Category::where('business_id', $this->businessId)
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($categoryName)])
                ->first();

            if (!$category) {
                // Auto-create missing category
                $category = Category::create([
                    'business_id' => $this->businessId,
                    'name' => $categoryName,
                    'description' => 'Auto-created from product import',
                    'is_active' => true,
                    'display_order' => 999,
                ]);
                Log::info("Row {$rowNumber}: Auto-created category '{$categoryName}'");
            }                    
                    
                    // Check if product already exists by name
                    $product = Product::where('business_id', $this->businessId)
                        ->where('name', $row['product_name'])
                        ->first();

                    if ($product) {
                        // Product already exists, skip
                        $this->errors[] = "Row {$rowNumber}: Product '{$row['product_name']}' already exists. Skipping.";
                        $this->skippedCount++;
                        continue;
                    }

                    // Get total inventory from Excel
                    $totalBoxes = !empty($row['total_boxes']) ? (int)$row['total_boxes'] : 0;
                    $unitsPerBox = !empty($row['units_per_box']) ? (int)$row['units_per_box'] : 1;
                    $totalUnits = $totalBoxes * $unitsPerBox;

                    // Weight-based selling fields (optional)
                    $boxWeight = !empty($row['box_weight_kg']) ? (float)$row['box_weight_kg'] : null;
                    $sellingMode = !empty($row['selling_mode']) ? $row['selling_mode'] : 'unit';
                    $pricePerKilo = !empty($row['price_per_kilo']) ? (float)$row['price_per_kilo'] : null;
                    $pricePerBox = !empty($row['price_per_box']) ? (float)$row['price_per_box'] : null;
                    $weightUnit = !empty($row['weight_unit']) ? $row['weight_unit'] : null;
                    $pricePerUnitWeight = !empty($row['price_per_unit_weight']) ? (float)$row['price_per_unit_weight'] : null;
                    $price = !empty($row['price']) ? (float)$row['price'] : null;
                    $costPrice = !empty($row['cost_price']) ? (float)$row['cost_price'] : null;
                    
                    // Validate selling mode
                    if (!in_array($sellingMode, ['unit', 'weight', 'box', 'both'])) {
                        $sellingMode = 'unit';
                    }

                    // Calculate default price for weight-based selling (same logic as ProductController)
                    if (($sellingMode === 'weight' || $sellingMode === 'box' || $sellingMode === 'both') && empty($price)) {
                        if ($boxWeight > 0 && $pricePerKilo > 0) {
                            // Calculate price per unit: (box_weight × price_per_kilo) / units_per_box
                            $price = round(($boxWeight * $pricePerKilo) / $unitsPerBox, 2);
                        } elseif ($pricePerBox > 0) {
                            // If price per box is provided, calculate per unit
                            $price = round($pricePerBox / $unitsPerBox, 2);
                        } elseif ($pricePerKilo > 0) {
                            // Fallback to price per kilo if no box weight
                            $price = $pricePerKilo;
                        }
                    }

                    // Create new product (NOT assigned to any branch yet)
                    $product = Product::create([
                        'name' => $row['product_name'],
                        'description' => $row['description'] ?? null,
                        'category_id' => $category->id,
                        'business_id' => $this->businessId,
                        'added_by' => Auth::id(),
                        'quantity_per_box' => $unitsPerBox,
                        'total_boxes' => $totalBoxes,
                        'total_units' => $totalUnits,
                        'assigned_units' => 0, // No assignments yet
                        'box_weight' => $boxWeight,
                        'selling_mode' => $sellingMode,
                        'price' => $price,
                        'cost_price' => $costPrice,
                        'price_per_kilo' => $pricePerKilo,
                        'price_per_box' => $pricePerBox,
                        'weight_unit' => $weightUnit,
                        'price_per_unit_weight' => $pricePerUnitWeight,
                    ]);

                    // Generate barcode and QR code
                    if (!$product->barcode) {
                        $product->barcode = $this->barcodeService->generateBarcode();
                        $product->save();
                    }

                    if (!$product->qr_code_path) {
                        $qrPath = $this->barcodeService->generateQRCode($product);
                        $product->qr_code_path = $qrPath;
                        $product->save();
                    }

                    $this->successCount++;
                    Log::info("Row {$rowNumber}: Product '{$product->name}' created successfully with {$totalUnits} total units");

                } catch (\Exception $e) {
                    $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $this->skippedCount++;
                    Log::error("Product import error on row {$rowNumber}: " . $e->getMessage());
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'total_boxes' => 'nullable|numeric|min:0',
            'units_per_box' => 'nullable|numeric|min:1',
            'box_weight_kg' => 'nullable|numeric|min:0',
            'selling_mode' => 'nullable|in:unit,weight,box,both',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'price_per_kilo' => 'nullable|numeric|min:0',
            'price_per_box' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:kg,g,ton,lb,oz',
            'price_per_unit_weight' => 'nullable|numeric|min:0',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
