<?php

namespace App\Http\Controllers;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\BranchProduct;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductTemplateExport;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $categoryId = request()->input('category_id');
        $filterUncategorized = $categoryId === 'null';

        // Determine which branches the user has access to
        $branchIds = collect();
        if (($user->role === 'manager' || $user->role === 'business_admin') && $user->branch_id) {
            $branchIds = collect([$user->branch_id]);
        } else {
            // Superadmin or business admin without specific branch - get all branches
            $branchIds = Branch::where('business_id', $user->business_id)->pluck('id');
        }

        // Get categories that have products available in the accessible branches
        $categories = Category::forBusiness($user->business_id)
            ->active()
            ->parents()
            ->whereHas('products', function ($query) use ($branchIds) {
                $query->whereHas('branchProducts', function ($q) use ($branchIds) {
                    $q->whereIn('branch_id', $branchIds);
                });
            })
            ->withCount([
                'products' => function ($query) use ($branchIds) {
                    $query->whereHas('branchProducts', function ($q) use ($branchIds) {
                        $q->whereIn('branch_id', $branchIds);
                    });
                }
            ])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        // Count uncategorized products
        $uncategorizedCount = 0;
        if (($user->role === 'manager' || $user->role === 'business_admin') && $user->branch_id) {
            $uncategorizedCount = BranchProduct::where('branch_id', $user->branch_id)
                ->whereHas('product', function($q) {
                    $q->whereNull('category_id');
                })->count();
        } else {
            $uncategorizedCount = BranchProduct::whereHas('product', function($q) {
                $q->whereNull('category_id');
            })->count();
        }

        if (($user->role === 'manager' || $user->role === 'business_admin') && $user->branch_id) {
            $productsQuery = BranchProduct::where('branch_id', $user->branch_id)
                ->with(['product.category']);
            if ($filterUncategorized) {
                $productsQuery->whereHas('product', function($q) {
                    $q->whereNull('category_id');
                });
            } elseif ($categoryId) {
                $productsQuery->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            
            // Get ALL products for financial metrics calculation
            $allProducts = clone $productsQuery;
            $allProductsForMetrics = $allProducts->get();
            
            // Get paginated products for display
            $products = $productsQuery->orderBy('updated_at', 'desc')->paginate(15);

            $totalProducts = BranchProduct::where('branch_id', $user->branch_id)->count();
            $inStoreProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where(function($query) {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                          ->orWhere('stock_quantity', '<=', 10);
                })->count();
        } else {
            $productsQuery = BranchProduct::with(['product.category', 'branch']);
            if ($filterUncategorized) {
                $productsQuery->whereHas('product', function($q) {
                    $q->whereNull('category_id');
                });
            } elseif ($categoryId) {
                $productsQuery->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            
            // Get ALL products for financial metrics calculation
            $allProducts = clone $productsQuery;
            $allProductsForMetrics = $allProducts->get();
            
            // Get paginated products for display
            $products = $productsQuery->orderBy('updated_at', 'desc')->paginate(15);

            $totalProducts = Product::count();
            $inStoreProducts = Product::whereHas('branchProducts')->count();
            $lowStockProducts = BranchProduct::where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                      ->orWhere('stock_quantity', '<=', 10);
            })->distinct('product_id')->count('product_id');
        }

        // Calculate financial metrics for ALL products (not just paginated)
        $financialMetrics = $this->calculateFinancialMetrics($allProductsForMetrics);

        $stats = [
            'total_products' => $totalProducts,
            'in_store_products' => $inStoreProducts,
            'low_stock_products' => $lowStockProducts,
        ];

        if (request()->wantsJson() || request()->ajax() || request()->expectsJson()) {
            return response()->json($products);
        }

        return view('layouts.product', [
            'products' => $products,
            'stats' => $stats,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'financialMetrics' => $financialMetrics,
            'uncategorizedCount' => $uncategorizedCount
        ]);
    }

    /**
     * Calculate financial metrics for products
     */
    private function calculateFinancialMetrics($products)
    {
        $totalSellingPrice = 0;
        $totalCostPrice = 0;

        foreach($products as $item) {
            $branchProduct = $item->product ? $item : null;
            $sellingPrice = $branchProduct->price ?? 0;
            $costPrice = $branchProduct->cost_price ?? 0;
            $quantity = $branchProduct->stock_quantity ?? 0;
            
            $totalSellingPrice += ($sellingPrice * $quantity);
            $totalCostPrice += ($costPrice * $quantity);
        }
        $totalMargin = $totalSellingPrice - $totalCostPrice;
        $marginPercentage = $totalCostPrice > 0 ? (($totalMargin / $totalCostPrice) * 100) : 0;

        return [
            'total_selling_price' => $totalSellingPrice,
            'total_cost_price' => $totalCostPrice,
            'total_margin' => $totalMargin,
            'margin_percentage' => $marginPercentage
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',

            // optional branch/stock fields
            'branch_id' => 'nullable|exists:branches,id',
            'stock_quantity' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            
            // Box quantity fields (now required)
            'quantity_of_boxes' => 'required|integer|min:0',
            'quantity_per_box' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            // if caller expects JSON (AJAX), return JSON errors
            if ($request->wantsJson()|| $request->ajax() || $request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            //Otherwise redirect back with errors and old input
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validatedData = $validator->validated();

            // Get business_id from the selected branch or default to the first available business
            $businessId = null;
            
            if (!empty($validatedData['branch_id'])) {
                // Get business_id from the selected branch
                $businessId = \App\Models\Branch::where('id', $validatedData['branch_id'])->value('business_id');
            } else {
                // If no branch selected, get the first available business (for admin/general products)
                $businessId = \App\Models\Business::first()?->id;
            }
            
            if (!$businessId) {
                $error_message = 'No business found. Please ensure branches and businesses are properly set up.';
                if ($request->wantsJson()|| $request->ajax() || $request->expectsJson()) {
                    return response()->json(['error' => $error_message], 422);
                }
                return redirect()->back()->with('error',$error_message)->withInput();
            }
            
            $validatedData['business_id'] = $businessId;
            $validatedData['added_by'] = Auth::id();

            // Calculate total inventory from boxes and units per box
            $totalBoxes = $request->input('quantity_of_boxes', 0);
            $unitsPerBox = $request->input('quantity_per_box', 1);
            $totalUnits = $totalBoxes * $unitsPerBox;
            
            // Add inventory tracking fields
            $validatedData['total_boxes'] = $totalBoxes;
            $validatedData['total_units'] = $totalUnits;
            $validatedData['assigned_units'] = 0; // Nothing assigned yet

            // Handle product image upload (stored in 'image' field)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product-images', 'public');
                $validatedData['image'] = $imagePath;
            }

            $product = Product::create($validatedData);

            // If branch info provided, assign to that branch
            $branchId = $request->input('branch_id');
            $stockQty = $request->input('stock_quantity');
            
            if ($branchId && $stockQty > 0) {
                // Check if we have enough units available
                if (!$product->hasAvailableUnits($stockQty)) {
                    $error_message = "Cannot assign {$stockQty} units. Only {$product->available_units} units available in inventory.";
                    if ($request->wantsJson()|| $request->ajax() || $request->expectsJson()) {
                        return response()->json(['error' => $error_message], 422);
                    }
                    return redirect()->back()->with('error', $error_message)->withInput();
                }
                
                // Create branch product assignment
                $bpData = [
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'stock_quantity' => (int) $stockQty,
                    'quantity_of_boxes' => $request->input('quantity_of_boxes'),
                    'quantity_per_box' => $unitsPerBox,
                ];
                
                if ($request->filled('price')) $bpData['price'] = $request->input('price');
                if ($request->filled('cost_price')) $bpData['cost_price'] = $request->input('cost_price');
                if ($request->filled('reorder_level')) $bpData['reorder_level'] = $request->input('reorder_level');
                
                $branchProduct = \App\Models\BranchProduct::create($bpData);
                
                // Deduct from available inventory
                $product->assignUnits($stockQty);
                
                Log::info("Product assigned to branch", [
                    'product_id' => $product->id,
                    'branch_id' => $branchId,
                    'assigned_units' => $stockQty,
                    'remaining_available' => $product->available_units
                ]);
            }
            
            // JSON for AJAX, redirect for regular form submit
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Product created successfully',
                    'product' => $product
                ], 201);
            }

            return redirect()->route('layouts.product')->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => 'Failed to create product'], 500);
            }
            return redirect()->back()->with('error', 'Failed to create product')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('business')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'business_admin_id' => 'sometimes|required|exists:users,id',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($validator->validated());
        return response()->json($product)->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Business deleted successfully'], 204);
    }

    /**
     * Show bulk import page
     */
    public function showBulkImport()
    {
        $user = Auth::user();
        // Only superadmins can import into any branch. Business admins and managers
        // may only import into their assigned branch.
        if ($user->role === 'superadmin') {
            $branches = Branch::orderBy('name')->get();
        } elseif (($user->role === 'business_admin' || $user->role === 'manager') && $user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            // No branch assigned or not permitted
            abort(403, 'You do not have permission to access bulk import.');
        }

        return view('inventory.bulk-import', compact('branches'));
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'product_import_template.xlsx');
    }

    /**
     * Import products from Excel
     */
    public function importProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        try {
            $import = new ProductsImport($user->business_id);
            Excel::import($import, $request->file('file'));

            $message = "Import completed! ";
            $message .= "Created: {$import->getSuccessCount()} new products, ";
            $message .= "Skipped: {$import->getSkippedCount()}";

            $errors = $import->getErrors();
            if (!empty($errors)) {
                $message .= ". Some rows had errors.";
                return redirect()->route('layouts.product')
                    ->with('warning', $message)
                    ->with('import_errors', $errors)
                    ->with('import_info', 'Products created successfully. You can now assign them to branches.');
            }

            return redirect()->route('layouts.product')
                ->with('success', $message)
                ->with('import_info', 'Products created successfully. You can now assign them to branches.');

        } catch (\Exception $e) {
            Log::error('Product import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk assignment page
     */
    /**
     * Show bulk assignment page (Excel upload)
     */
    public function showBulkAssignment()
    {
        $user = Auth::user();
        
        $userRole = $user->role;
        $userBranchName = null;
        
        if ($user->role === 'business_admin' || $user->role === 'manager') {
            if (empty($user->branch_id)) {
                abort(403, 'No branch assigned.');
            }
            $branch = Branch::find($user->branch_id);
            $userBranchName = $branch ? $branch->name : 'Unknown';
        }

        return view('inventory.bulk-assignment', compact('userRole', 'userBranchName'));
    }

    /**
     * Download bulk assignment template
     */
    public function downloadAssignmentTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BulkAssignmentTemplateExport(), 
            'bulk_assignment_template.xlsx'
        );
    }

    /**
     * Upload and process bulk assignment Excel
     */
    public function uploadBulkAssignment(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $user = Auth::user();
        
        try {
            Log::info('Starting bulk assignment upload', [
                'user_id' => $user->id,
                'business_id' => $user->business_id,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
            ]);

            $import = new \App\Imports\BulkAssignmentImport(
                $user->business_id,
                $user->role,
                $user->branch_id
            );
            
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            Log::info('Bulk assignment completed', [
                'success_count' => $import->successCount,
                'skipped_count' => $import->skippedCount,
                'errors_count' => count($import->errors),
            ]);

            $message = "Bulk assignment completed!";
            
            if ($import->successCount > 0) {
                $message .= " Successfully assigned {$import->successCount} products.";
            }
            
            if ($import->skippedCount > 0) {
                $message .= " Skipped {$import->skippedCount} rows.";
            }

            // If no products were successfully assigned, show error
            if ($import->successCount === 0) {
                return redirect()->route('inventory.bulk-assignment')
                    ->with('error', 'No products were assigned. Please check the errors below.')
                    ->with('import_errors', $import->errors);
            }

            // Success! Redirect to product manager to view assigned products
            return redirect()->route('layouts.productman')
                ->with('success', $message)
                ->with('details', [
                    'success' => $import->successCount,
                    'skipped' => $import->skippedCount,
                ])
                ->with('import_info', 'Your products have been successfully assigned to the branch. You can see them in the list below.');
                
        } catch (\Exception $e) {
            Log::error('Bulk assignment import failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('inventory.bulk-assignment')
                ->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show manual assignment page (old bulk assignment form)
     */
    public function showAssign()
    {
        $user = Auth::user();
        // Superadmins see all products/branches. Others see only their business/products
        if ($user->role === 'superadmin') {
            $products = Product::with(['category', 'branchProducts.branch'])->get();
            $branches = Branch::with('manager')->orderBy('name')->get();
        } elseif ($user->role === 'business_admin' || $user->role === 'manager') {
            if (empty($user->branch_id)) {
                abort(403, 'No branch assigned.');
            }
            $products = Product::where('business_id', $user->business_id)
                ->with(['category', 'branchProducts.branch'])
                ->get();
            $branches = Branch::where('id', $user->branch_id)
                ->with('manager')
                ->get();
        } else {
            abort(403, 'You do not have permission to access assignment.');
        }

        return view('inventory.assign', compact('products', 'branches'));
    }

    /**
     * Assign products to branch (manual form submission)
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity_of_boxes' => 'required|integer|min:0',
            'products.*.quantity_per_box' => 'required|integer|min:1',
            'products.*.selling_price' => 'nullable|numeric|min:0',
            'products.*.cost_price' => 'nullable|numeric|min:0',
            'products.*.reorder_level' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $branchId = $request->branch_id;

        // Verify access
        $branch = Branch::findOrFail($branchId);
        if ($user->role === 'superadmin') {
            // allowed
        } elseif ($user->role === 'business_admin' || $user->role === 'manager') {
            if (empty($user->branch_id) || (int)$user->branch_id !== (int)$branchId) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            if ($branch->business_id !== $user->business_id) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        } else {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $assignedCount = 0;
            $errors = [];
            
            foreach ($request->products as $index => $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $stockQuantity = $productData['quantity_of_boxes'] * $productData['quantity_per_box'];
                
                // Check if this is a new assignment or update
                $branchProduct = BranchProduct::where([
                    'product_id' => $productData['product_id'],
                    'branch_id' => $branchId,
                ])->first();

                $oldQuantity = $branchProduct ? $branchProduct->stock_quantity : 0;
                $quantityDifference = $stockQuantity - $oldQuantity;

                // Check if product has enough available units for new assignments or increases
                if ($quantityDifference > 0) {
                    if (!$product->hasAvailableUnits($quantityDifference)) {
                        $errors[] = "Product '{$product->name}': Cannot assign {$quantityDifference} units. Only {$product->available_units} units available in inventory.";
                        continue; // Skip this product
                    }
                }

                // Create or update branch product
                if (!$branchProduct) {
                    $branchProduct = new BranchProduct();
                    $branchProduct->product_id = $productData['product_id'];
                    $branchProduct->branch_id = $branchId;
                }

                // Update quantities
                $branchProduct->stock_quantity = $stockQuantity;
                $branchProduct->quantity_of_boxes = $productData['quantity_of_boxes'];
                $branchProduct->quantity_per_box = $productData['quantity_per_box'];

                // Always set reorder level (default to 10 if not provided)
                $branchProduct->reorder_level = $productData['reorder_level'] ?? 10;

                // Update price if provided, or set default for new records
                if (isset($productData['selling_price']) && $productData['selling_price'] !== '' && $productData['selling_price'] !== null) {
                    $branchProduct->price = floatval($productData['selling_price']);
                } elseif (!$branchProduct->exists) {
                    // Price is required for new records
                    $branchProduct->price = 0.00;
                }

                // Update cost price if provided
                if (isset($productData['cost_price']) && $productData['cost_price'] !== '' && $productData['cost_price'] !== null) {
                    $branchProduct->cost_price = floatval($productData['cost_price']);
                }

                $branchProduct->save();
                
                // Update product's assigned units
                if ($quantityDifference > 0) {
                    $product->assignUnits($quantityDifference);
                } elseif ($quantityDifference < 0) {
                    $product->unassignUnits(abs($quantityDifference));
                }
                
                $assignedCount++;
            }

            // Prepare response
            $response = [
                'success' => true,
                'message' => "{$assignedCount} products assigned to branch successfully!",
                'assigned_count' => $assignedCount,
            ];
            
            if (!empty($errors)) {
                $response['warnings'] = $errors;
                $response['message'] .= " Some products were skipped due to insufficient inventory.";
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk assignment failed: ' . $e->getMessage());
            return response()->json(['error' => 'Assignment failed: ' . $e->getMessage()], 500);
        }
    }

    
}
