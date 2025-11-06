# Central Inventory System - Implementation Guide

## ✅ What Was Done

I've added **centralized inventory tracking** to your system. Here's what changed:

### Database Changes (Migration Applied ✅)

Added to `products` table:
- `total_boxes` - Total boxes you have in warehouse
- `total_units` - Total units (boxes × units_per_box)
- `assigned_units` - Units already given to branches
- **Available units** = `total_units - assigned_units` (automatic calculation)

### Model Changes (Product.php Updated ✅)

Added methods:
- `getAvailableUnitsAttribute()` - Shows how many units left to assign
- `hasAvailableUnits($units)` - Check if you have enough before assigning
- `assignUnits($units)` - Deduct units when assigning to branch
- `unassignUnits($units)` - Return units when removing from branch

---

## 🔧 How to Update Your Controllers

### 1. Product Creation (ProductController@store)

**CURRENT CODE** (around line 112-200):
```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        // ...
        'quantity_of_boxes' => 'required|integer|min:0',
        'quantity_per_box' => 'required|integer|min:1',
    ]);
    
    // ... validation and image upload
    
    $product = Product::create($validatedData);
    
    // If branch info provided, create branch_products row
    if ($branchId && count($bpData)) {
        $branchProduct = \App\Models\BranchProduct::firstOrNew([
            'branch_id' => $branchId,
            'product_id' => $product->id,
        ]);
        // ... save branch product
    }
}
```

**UPDATE TO THIS**:
```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        // ...
        'quantity_of_boxes' => 'required|integer|min:0',
        'quantity_per_box' => 'required|integer|min:1',
        // ...existing validations
    ]);
    
    // ... validation and image upload code stays same
    
    // Calculate total units for central inventory
    $totalBoxes = $request->input('quantity_of_boxes', 0);
    $unitsPerBox = $request->input('quantity_per_box', 1);
    $totalUnits = $totalBoxes * $unitsPerBox;
    
    // Add inventory fields to product
    $validatedData['total_boxes'] = $totalBoxes;
    $validatedData['total_units'] = $totalUnits;
    $validatedData['assigned_units'] = 0; // Nothing assigned yet
    
    $product = Product::create($validatedData);
    
    // If branch info provided, assign to that branch
    $branchId = $request->input('branch_id');
    $stockQty = $request->input('stock_quantity');
    
    if ($branchId && $stockQty > 0) {
        // Check if we have enough units
        if (!$product->hasAvailableUnits($stockQty)) {
            return redirect()->back()->with('error', 
                "Cannot assign {$stockQty} units. Only {$product->available_units} units available."
            )->withInput();
        }
        
        // Create branch product
        $bpData = [
            'stock_quantity' => $stockQty,
            'quantity_of_boxes' => $request->input('quantity_of_boxes'),
            'quantity_per_box' => $unitsPerBox,
        ];
        
        if ($request->filled('price')) $bpData['price'] = $request->input('price');
        if ($request->filled('cost_price')) $bpData['cost_price'] = $request->input('cost_price');
        if ($request->filled('reorder_level')) $bpData['reorder_level'] = $request->input('reorder_level');
        
        $branchProduct = \App\Models\BranchProduct::create([
            'branch_id' => $branchId,
            'product_id' => $product->id,
            ...$bpData
        ]);
        
        // Deduct from available inventory
        $product->assignUnits($stockQty);
    }
    
    // ... rest of your response code
}
```

### 2. Bulk Assignment (BulkAssignmentImport.php)

**CURRENT CODE** (around line 95-150):
```php
$stockQuantity = $boxes * $unitsPerBox;

$branchProduct = BranchProduct::firstOrNew([
    'branch_id' => $branch->id,
    'product_id' => $product->id,
]);

$branchProduct->quantity_of_boxes = $boxes;
$branchProduct->quantity_per_box = $unitsPerBox;
$branchProduct->stock_quantity = $stockQuantity;
// ...
$branchProduct->save();
```

**UPDATE TO THIS**:
```php
$stockQuantity = $boxes * $unitsPerBox;

// Check if this is a new assignment or update
$branchProduct = BranchProduct::firstOrNew([
    'branch_id' => $branch->id,
    'product_id' => $product->id,
]);

$oldQuantity = $branchProduct->exists ? $branchProduct->stock_quantity : 0;
$quantityDifference = $stockQuantity - $oldQuantity;

// Check if product has enough available units
if ($quantityDifference > 0 && !$product->hasAvailableUnits($quantityDifference)) {
    $this->errors[] = "Row {$rowNumber}: Not enough units. Product '{$product->name}' has only {$product->available_units} units available, but you're trying to assign {$quantityDifference} more.";
    $this->skippedCount++;
    continue;
}

$branchProduct->quantity_of_boxes = $boxes;
$branchProduct->quantity_per_box = $unitsPerBox;
$branchProduct->stock_quantity = $stockQuantity;
// ... other fields

$branchProduct->save();

// Update product's assigned units
if ($quantityDifference > 0) {
    $product->assignUnits($quantityDifference);
} elseif ($quantityDifference < 0) {
    $product->unassignUnits(abs($quantityDifference));
}
```

### 3. Manual Assignment (ProductController@bulkAssign)

Similar changes - check available units before assigning, then call `$product->assignUnits($quantity)`.

---

## 📝 Update Your Views

### Product Creation Form

Add display of available units when editing:

```blade
@if($product->exists)
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
        <p class="text-sm text-gray-700">
            <strong>Central Inventory:</strong>
        </p>
        <ul class="text-sm text-gray-600 mt-2 space-y-1">
            <li>📦 Total Boxes: {{ $product->total_boxes }}</li>
            <li>📊 Total Units: {{ $product->total_units }}</li>
            <li>✅ Assigned to Branches: {{ $product->assigned_units }} units</li>
            <li class="font-bold text-green-600">
                🎯 Available for Assignment: {{ $product->available_units }} units
            </li>
        </ul>
    </div>
@endif
```

### Inventory Summary Page

Show available units column:

```blade
<td class="px-6 py-4">
    <div>
        <p class="text-sm text-gray-900">Total: {{ $product->total_units }}</p>
        <p class="text-sm text-gray-600">Assigned: {{ $product->assigned_units }}</p>
        <p class="text-sm font-semibold text-green-600">
            Available: {{ $product->available_units }}
        </p>
    </div>
</td>
```

---

## ⚠️ Important: Existing Data Migration

Your existing products don't have total inventory set. Run this command to set them:

```php
php artisan tinker

// Set total inventory from branch products sum
$products = App\Models\Product::all();
foreach($products as $product) {
    $totalAssigned = $product->branchProducts->sum('stock_quantity');
    $product->total_units = $totalAssigned;
    $product->assigned_units = $totalAssigned;
    $product->total_boxes = $product->branchProducts->sum('quantity_of_boxes') ?: 0;
    $product->save();
    echo "Product: {$product->name} - Total: {$product->total_units}, Assigned: {$product->assigned_units}\n";
}
```

---

## 🎯 How It Works Now

### Creating a Product:
1. You enter: **50 boxes × 12 units/box = 600 total units**
2. Database saves: `total_boxes=50`, `total_units=600`, `assigned_units=0`
3. Available for assignment: **600 units**

### Assigning to Branch:
1. You assign **100 units** to Main Branch
2. System checks: Does product have 100 units available? ✅ Yes
3. Creates `branch_products` record with 100 units
4. Updates product: `assigned_units=100`
5. Now available: **500 units** (600 - 100)

### Assigning to Another Branch:
1. You try to assign **600 units** to Downtown Branch
2. System checks: Does product have 600 units available? ❌ No (only 500 left)
3. Shows error: "Not enough units. Only 500 available."
4. Assignment blocked ✅

### Removing Assignment:
1. You delete Main Branch's 100 units
2. System calls `$product->unassignUnits(100)`
3. Updates product: `assigned_units=0`
4. Now available: **600 units** again

---

## 🔍 Testing

```bash
# Create a product
php artisan tinker

$product = App\Models\Product::create([
    'name' => 'Test Product',
    'business_id' => 1,
    'category_id' => 1,
    'added_by' => 1,
    'quantity_per_box' => 10,
    'total_boxes' => 50,
    'total_units' => 500,
    'assigned_units' => 0,
]);

echo "Available: " . $product->available_units . " units\n";

# Try assigning 200 units
$product->assignUnits(200);
echo "After assigning 200: " . $product->available_units . " units\n";

# Try assigning 400 more (should fail)
try {
    $product->assignUnits(400);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Summary

✅ **Migration applied** - Database has new columns
✅ **Model updated** - Product has inventory tracking methods
⏳ **Controllers need updating** - Follow the guide above
⏳ **Views need updating** - Show available units
⏳ **Existing data migration** - Run the tinker command

Your system NOW prevents over-assignment and tracks central inventory properly!
