<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\BranchProduct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $branchId;
    protected $businessId;
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;

    public function __construct($branchId, $businessId)
    {
        $this->branchId = $branchId;
        $this->businessId = $businessId;
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

            // Find category (case-insensitive, trimmed, supports both parent and subcategories)
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
                $this->errors[] = "Row {$rowNumber}: Category '{$categoryName}' not found. Skipping product.";
                $this->skippedCount++;
                continue;
            }                    // Check if product already exists by name
                    $product = Product::where('business_id', $this->businessId)
                        ->where('name', $row['product_name'])
                        ->first();

                    if (!$product) {
                        // Create new product
                        $product = Product::create([
                            'name' => $row['product_name'],
                            'description' => $row['description'] ?? null,
                            'category_id' => $category?->id,
                            'business_id' => $this->businessId,
                            'added_by' => Auth::id(),
                            'quantity_per_box' => !empty($row['units_per_box']) ? (int)$row['units_per_box'] : null,
                        ]);
                    }

                    // Create or update branch product
                    $quantityOfBoxes = !empty($row['quantity_of_boxes']) ? (int)$row['quantity_of_boxes'] : 0;
                    $quantityPerBox = !empty($row['units_per_box']) ? (int)$row['units_per_box'] : 1;
                    $stockQuantity = $quantityOfBoxes * $quantityPerBox;

                    $branchProduct = BranchProduct::where('product_id', $product->id)
                        ->where('branch_id', $this->branchId)
                        ->first();

                    if ($branchProduct) {
                        // Update existing
                        $branchProduct->stock_quantity += $stockQuantity;
                        $branchProduct->quantity_of_boxes = ($branchProduct->quantity_of_boxes ?? 0) + $quantityOfBoxes;
                        $branchProduct->quantity_per_box = $quantityPerBox;
                        if (!empty($row['selling_price'])) {
                            $branchProduct->price = (float)$row['selling_price'];
                        }
                        if (!empty($row['cost_price'])) {
                            $branchProduct->cost_price = (float)$row['cost_price'];
                        }
                        if (!empty($row['reorder_level'])) {
                            $branchProduct->reorder_level = (int)$row['reorder_level'];
                        }
                        $branchProduct->save();
                    } else {
                        // Create new
                        BranchProduct::create([
                            'product_id' => $product->id,
                            'branch_id' => $this->branchId,
                            'stock_quantity' => $stockQuantity,
                            'quantity_of_boxes' => $quantityOfBoxes,
                            'quantity_per_box' => $quantityPerBox,
                            'price' => !empty($row['selling_price']) ? (float)$row['selling_price'] : null,
                            'cost_price' => !empty($row['cost_price']) ? (float)$row['cost_price'] : null,
                            'reorder_level' => !empty($row['reorder_level']) ? (int)$row['reorder_level'] : null,
                        ]);
                    }

                    $this->successCount++;

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
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity_of_boxes' => 'nullable|numeric|min:0',
            'units_per_box' => 'nullable|numeric|min:1',
            'selling_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
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
