<?php

namespace App\Imports;

use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ItemRequestImport implements ToCollection, WithHeadingRow
{
    protected $manager;
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;

    public function __construct()
    {
        $this->manager = Auth::user();
    }

    /**
     * Process the collection of rows from the Excel file
     */
    public function collection(Collection $rows)
    {
        $rowNumber = 2; // Starting from row 2 (after header)

        foreach ($rows as $row) {
            try {
                // Skip empty rows
                if (empty($row['product_name_or_barcode']) && empty($row['from_branch'])) {
                    $rowNumber++;
                    continue;
                }

                // Find product by name or barcode
                $product = Product::where('name', $row['product_name_or_barcode'])
                    ->orWhere('barcode', $row['product_name_or_barcode'])
                    ->first();

                if (!$product) {
                    $this->errors[] = "Row {$rowNumber}: Product '{$row['product_name_or_barcode']}' not found.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Find source branch
                $fromBranch = Branch::where('name', $row['from_branch'])
                    ->where('business_id', $this->manager->branch->business_id)
                    ->first();

                if (!$fromBranch) {
                    $this->errors[] = "Row {$rowNumber}: Branch '{$row['from_branch']}' not found in your business.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Validate manager cannot request from own branch
                if ($fromBranch->id === $this->manager->branch_id) {
                    $this->errors[] = "Row {$rowNumber}: Cannot request items from your own branch.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Get quantity values
                $quantityOfBoxes = (int) ($row['quantity_of_boxes'] ?? 0);
                $quantityPerBox = (int) ($row['quantity_per_box'] ?? 1);
                $totalQuantity = $quantityOfBoxes * $quantityPerBox;

                // Validate quantities
                if ($quantityOfBoxes <= 0) {
                    $this->errors[] = "Row {$rowNumber}: Quantity of boxes must be greater than 0.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                if ($quantityPerBox <= 0) {
                    $this->errors[] = "Row {$rowNumber}: Quantity per box must be greater than 0.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Check stock availability in source branch
                $sourceBranchProduct = BranchProduct::where('branch_id', $fromBranch->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$sourceBranchProduct) {
                    $this->errors[] = "Row {$rowNumber}: Product '{$product->name}' is not available in '{$fromBranch->name}'.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                if ($sourceBranchProduct->stock_quantity < $totalQuantity) {
                    $this->errors[] = "Row {$rowNumber}: Insufficient stock in '{$fromBranch->name}'. Available: {$sourceBranchProduct->stock_quantity}, Requested: {$totalQuantity}.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Check for duplicate pending requests
                $existingRequest = StockTransfer::where('to_branch_id', $this->manager->branch_id)
                    ->where('from_branch_id', $fromBranch->id)
                    ->where('product_id', $product->id)
                    ->where('status', 'pending')
                    ->exists();

                if ($existingRequest) {
                    $this->errors[] = "Row {$rowNumber}: You already have a pending request for '{$product->name}' from '{$fromBranch->name}'.";
                    $this->skippedCount++;
                    $rowNumber++;
                    continue;
                }

                // Get reason (optional)
                $reason = $row['reason'] ?? 'Bulk request';

                // Create stock transfer request with pricing information
                StockTransfer::create([
                    'from_branch_id' => $fromBranch->id,
                    'to_branch_id' => $this->manager->branch_id,
                    'product_id' => $product->id,
                    'quantity' => $totalQuantity,
                    'quantity_of_boxes' => $quantityOfBoxes,
                    'quantity_per_box' => $quantityPerBox,
                    'reason' => $reason,
                    'status' => 'pending',
                    'requested_by' => $this->manager->id,
                    'requested_at' => now(),
                    // Include pricing information from source branch
                    'price' => $sourceBranchProduct->price ?? null,
                    'cost_price' => $sourceBranchProduct->cost_price ?? null,
                    'price_per_kilo' => $sourceBranchProduct->price_per_kilo ?? null,
                    'price_per_box' => $sourceBranchProduct->price_per_box ?? null,
                    'weight_unit' => $sourceBranchProduct->weight_unit ?? null,
                    'price_per_unit_weight' => $sourceBranchProduct->price_per_unit_weight ?? null,
                ]);

                $this->successCount++;
                Log::info("Item request created: {$product->name} from {$fromBranch->name} - {$quantityOfBoxes} boxes ({$totalQuantity} units)");

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: Error processing request - {$e->getMessage()}";
                $this->skippedCount++;
                Log::error("Item request import error on row {$rowNumber}: " . $e->getMessage());
            }

            $rowNumber++;
        }
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get successful import count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get skipped row count
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Get import summary
     */
    public function getSummary(): string
    {
        $summary = "Import completed: {$this->successCount} requests created successfully";
        
        if ($this->skippedCount > 0) {
            $summary .= ", {$this->skippedCount} rows skipped";
        }
        
        return $summary;
    }
}
