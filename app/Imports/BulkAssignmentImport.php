<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BulkAssignmentImport implements ToCollection, WithHeadingRow
{
    protected $businessId;
    protected $userRole;
    protected $userBranchId;
    public $successCount = 0;
    public $skippedCount = 0;
    public $errors = [];

    public function __construct($businessId, $userRole, $userBranchId = null)
    {
        $this->businessId = $businessId;
        $this->userRole = $userRole;
        $this->userBranchId = $userBranchId;
    }

    public function collection(Collection $rows)
    {
        $rowNumber = 1; // Start from 1 (header is row 0)

        foreach ($rows as $row) {
            $rowNumber++;

            try {
                // Log the raw row data to see what keys we're getting
                if ($rowNumber === 2) { // Log only the first data row
                    Log::info("First row keys and values:", [
                        'keys' => array_keys($row->toArray()),
                        'values' => $row->toArray(),
                    ]);
                }

                // Skip empty rows
                if (empty($row['product_name_or_barcode']) || empty($row['branch_name'])) {
                    $this->skippedCount++;
                    Log::warning("Row {$rowNumber}: Skipped - empty product or branch name", [
                        'product_name_or_barcode' => $row['product_name_or_barcode'] ?? 'NULL',
                        'branch_name' => $row['branch_name'] ?? 'NULL',
                    ]);
                    continue;
                }

                // Find product by name or barcode
                $productIdentifier = trim($row['product_name_or_barcode']);
                $product = Product::where('business_id', $this->businessId)
                    ->where(function($query) use ($productIdentifier) {
                        $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($productIdentifier)])
                              ->orWhereRaw('LOWER(TRIM(barcode)) = ?', [strtolower($productIdentifier)]);
                    })
                    ->first();

                if (!$product) {
                    $this->errors[] = "Row {$rowNumber}: Product '{$productIdentifier}' not found.";
                    $this->skippedCount++;
                    continue;
                }

                // Find branch by name
                $branchName = trim($row['branch_name']);
                $branch = Branch::where('business_id', $this->businessId)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($branchName)])
                    ->first();

                if (!$branch) {
                    $this->errors[] = "Row {$rowNumber}: Branch '{$branchName}' not found.";
                    $this->skippedCount++;
                    continue;
                }

                // For business admins/managers, verify they can only assign to their branch
                if (($this->userRole === 'business_admin' || $this->userRole === 'manager') && $branch->id !== $this->userBranchId) {
                    $this->errors[] = "Row {$rowNumber}: You can only assign products to your branch.";
                    $this->skippedCount++;
                    continue;
                }

                // Validate quantities
                $boxes = intval($row['quantity_of_boxes'] ?? 0);
                $unitsPerBox = intval($row['units_per_box'] ?? 1);

                if ($boxes < 0 || $unitsPerBox < 1) {
                    $this->errors[] = "Row {$rowNumber}: Invalid quantities (boxes: {$boxes}, units/box: {$unitsPerBox}).";
                    $this->skippedCount++;
                    continue;
                }

                $stockQuantity = $boxes * $unitsPerBox;

                // Find or create branch product
                $branchProduct = BranchProduct::firstOrNew([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ]);

                // Update quantities
                $branchProduct->quantity_of_boxes = $boxes;
                $branchProduct->quantity_per_box = $unitsPerBox;
                $branchProduct->stock_quantity = $stockQuantity;

                // Always set reorder level (default to 10 if not provided or empty)
                $branchProduct->reorder_level = isset($row['reorder_level']) && $row['reorder_level'] !== '' && $row['reorder_level'] !== null 
                    ? intval($row['reorder_level']) 
                    : 10;

                // Update price if provided, or set default for new records
                if (isset($row['selling_price']) && $row['selling_price'] !== '' && $row['selling_price'] !== null) {
                    $branchProduct->price = floatval($row['selling_price']);
                } elseif (!$branchProduct->exists) {
                    // Price is required for new records
                    $branchProduct->price = 0.00;
                }

                // Update cost price if provided
                if (isset($row['cost_price']) && $row['cost_price'] !== '' && $row['cost_price'] !== null) {
                    $branchProduct->cost_price = floatval($row['cost_price']);
                }

                // Log what we're about to save
                Log::info("Row {$rowNumber}: Attempting to save BranchProduct", [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'quantity_of_boxes' => $branchProduct->quantity_of_boxes,
                    'quantity_per_box' => $branchProduct->quantity_per_box,
                    'stock_quantity' => $branchProduct->stock_quantity,
                    'reorder_level' => $branchProduct->reorder_level,
                    'price' => $branchProduct->price,
                    'cost_price' => $branchProduct->cost_price,
                    'exists' => $branchProduct->exists,
                ]);

                $saved = $branchProduct->save();

                if ($saved) {
                    Log::info("Row {$rowNumber}: Successfully saved with ID: {$branchProduct->id}");
                    $this->successCount++;
                } else {
                    Log::error("Row {$rowNumber}: Save returned false");
                    $this->errors[] = "Row {$rowNumber}: Failed to save (save returned false).";
                    $this->skippedCount++;
                }

            } catch (\Exception $e) {
                Log::error("Row {$rowNumber}: Exception occurred", [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->errors[] = "Row {$rowNumber}: Error - " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }
}
