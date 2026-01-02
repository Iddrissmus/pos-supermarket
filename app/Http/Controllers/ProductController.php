<?php

namespace App\Http\Controllers;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use App\Services\ActivityLogger;
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
        // Detect filters from query string or dedicated routes
        $lowStockOnly = request()->boolean('low_stock') || request()->routeIs('products.low-stock');
        $inStoreOnly = request()->boolean('in_store') || request()->routeIs('products.in-store');
        $outOfStockOnly = request()->boolean('out_of_stock') || request()->routeIs('products.out-of-stock');

        // In-store scope excludes low stock
        $applyInStoreScope = function ($query) {
            $query->where(function($q) {
                $q->where('stock_quantity', '>', 0)
                  ->whereColumn('stock_quantity', '>', 'reorder_level')
                  ->where('stock_quantity', '>', 10);
            });
        };

        // ensure only one scope at a time (priority: out_of_stock > low_stock > in_store)
        if ($outOfStockOnly) {
            $lowStockOnly = false;
            $inStoreOnly = false;
        } elseif ($lowStockOnly) {
            $inStoreOnly = false;
        }

        // Determine which branches the user has access to
        $branchIds = collect();
        if ($user->role === 'manager' && $user->branch_id) {
            // Managers only see their specific branch
            $branchIds = collect([$user->branch_id]);
        } else {
            // Superadmin and business_admin see all branches in their business
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

        if ($user->role === 'manager' && $user->branch_id) {
            // Manager: show only their branch's products (Keep existing logic for managers)
            $productsQuery = BranchProduct::where('branch_id', $user->branch_id)
                ->with(['product.category'])
                ->whereHas('product', function($q) {
                    $q->whereNotNull('category_id');
                });
            if ($categoryId) {
                $productsQuery->whereHas('product', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            if ($lowStockOnly) {
                $productsQuery->where(function($query) {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                          ->orWhere('stock_quantity', '<=', 10);
                });
            }
            if ($inStoreOnly) {
                $applyInStoreScope($productsQuery);
            }
            if ($outOfStockOnly) {
                $productsQuery->where('stock_quantity', '<=', 0);
            }
            
            // Get ALL products for financial metrics calculation
            $allProducts = clone $productsQuery;
            $allProductsForMetrics = $allProducts->get();
            
            // Get paginated products for display
            $products = $productsQuery->orderBy('updated_at', 'desc')->paginate(15);

            $totalProducts = BranchProduct::where('branch_id', $user->branch_id)->count();
            $inStoreProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where('stock_quantity', '>', 0)
                ->count();
            $lowStockProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where(function($query) {
                    $query->whereColumn('stock_quantity', '<=', 'reorder_level')
                          ->orWhere('stock_quantity', '<=', 10);
                })
                ->count();
            $outOfStockProducts = BranchProduct::where('branch_id', $user->branch_id)
                ->where('stock_quantity', '<=', 0)
                ->count();

        } else {
            // Business Admin / SuperAdmin: Show ALL products in the business catalog
            // This ensures products not yet assigned to a branch are still visible.
            $productsQuery = Product::where('business_id', $user->business_id)
                ->with(['category', 'branchProducts.branch']); // Eager load branch assignments

            if ($categoryId) {
                $productsQuery->where('category_id', $categoryId);
            }
            
            // For stock filters, we need to check aggregated stock or branch specific stock?
            // "Low Stock" in a catalog context usually implies "Aggregate stock is low" or "Any branch is low"?
            // Let's us aggregate stock from the 'products' table 'stock' column if it exists/is used, 
            // OR check relation.
            // Based on view logic: $product->stock ?? 0. 
            // The logic below attempts to filter based on underlying assumptions. 
            // If using `branchProducts`, we can filter products that have at least one branch low.
            // But simplifying for catalog view: we show the Product.
            
            if ($lowStockOnly) {
                 // Low Stock: 1 to 10 available units
                 $productsQuery->whereRaw('(total_units - assigned_units) <= 10')
                               ->whereRaw('(total_units - assigned_units) > 0'); 
            }
            if ($inStoreOnly) {
                // In Stock (Healthy): > 10 available units
                $productsQuery->whereRaw('(total_units - assigned_units) > 10');
            }
            if ($outOfStockOnly) {
                // Out of Stock: 0 available units
                $productsQuery->whereRaw('(total_units - assigned_units) <= 0');
            }
            
            // Get ALL products for financial metrics calculation
            $allProducts = clone $productsQuery;
            $allProductsForMetrics = $allProducts->get();
            
            // Get paginated products for display
            $products = $productsQuery->orderBy('updated_at', 'desc')->paginate(15);

            // Calculate stats for entire business catalog based on total units (Mutually Exclusive)
            $totalProducts = Product::where('business_id', $user->business_id)->count();
            // In Stock = Healthy (>10)
            $inStoreProducts = Product::where('business_id', $user->business_id)
                ->whereRaw('total_units - assigned_units > 10')->count();
            // Low Stock = 1-10
            $lowStockProducts = Product::where('business_id', $user->business_id)
                ->whereRaw('total_units - assigned_units > 0')
                ->whereRaw('total_units - assigned_units <= 10')->count();
            // Out of Stock = 0
            $outOfStockProducts = Product::where('business_id', $user->business_id)
                ->whereRaw('total_units - assigned_units <= 0')->count();
        }

        // Calculate financial metrics for ALL products (not just paginated)
        $financialMetrics = $this->calculateFinancialMetrics($allProductsForMetrics);

        $stats = [
            'total_products' => $totalProducts,
            'in_store_products' => $inStoreProducts,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts ?? ($stats['out_of_stock_products'] ?? 0),
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
            'lowStockOnly' => $lowStockOnly,
            'inStoreOnly' => $inStoreOnly,
            'outOfStockOnly' => $outOfStockOnly
        ]);
    }

    /**
     * Dedicated low stock page
     */
    public function lowStock(Request $request)
    {
        $request->merge(['low_stock' => true, 'in_store' => null, 'out_of_stock' => null]);
        return $this->index();
    }

    /**
     * Dedicated in-store page
     */
    public function inStore(Request $request)
    {
        $request->merge(['in_store' => true, 'low_stock' => null, 'out_of_stock' => null]);
        return $this->index();
    }

    /**
     * Dedicated out-of-stock page
     */
    public function outOfStock(Request $request)
    {
        $request->merge(['out_of_stock' => true, 'low_stock' => null, 'in_store' => null]);
        return $this->index();
    }

    /**
     * Calculate financial metrics for products
     */
    private function calculateFinancialMetrics($products)
    {
        $totalSellingPrice = 0;
        $totalCostPrice = 0;

        foreach($products as $item) {
            // Support both BranchProduct and Product models
            $isBranchProduct = isset($item->product_id) && method_exists($item, 'product');
            
            $sellingPrice = $item->price ?? 0;
            $costPrice = $item->cost_price ?? 0;
            
            if ($isBranchProduct) {
                $quantity = $item->stock_quantity ?? 0;
            } else {
                // For main catalog (Warehouse), only count units available for assignment
                $quantity = $item->available_units ?? 0;
            }
            
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
            'category_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === 'new') return;
                    if (!\App\Models\Category::where('id', $value)->exists()) {
                        $fail('The selected category is invalid.');
                    }
                },
            ],
            'new_category_name' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'barcode' => 'nullable|string|max:13',

            // optional branch/stock fields
            'branch_id' => 'nullable|exists:branches,id',
            'stock_quantity' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            
            // Box quantity fields (now required)
            'quantity_of_boxes' => 'required|integer|min:0',
            'quantity_per_box' => 'required|integer|min:1',
            
            // Weight-based selling fields (optional)
            'selling_mode' => 'nullable|in:unit,weight,box,both',
            'box_weight' => 'nullable|numeric|min:0',
            'price_per_kilo' => 'nullable|numeric|min:0',
            'price_per_box' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:kg,g,ton,lb,oz',
            'price_per_unit_weight' => 'nullable|numeric|min:0',
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
                // If no branch selected, assign to the user's business
                $businessId = Auth::user()->business_id ?? \App\Models\Business::first()?->id;
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

            // Handle category creation if new category name provided
            if ($request->input('category_id') === 'new' && $request->filled('new_category_name')) {
                $newCategory = \App\Models\Category::create([
                    'business_id' => $businessId,
                    'name' => $request->input('new_category_name'),
                    'description' => 'Created during product addition',
                    'is_active' => true,
                    'display_order' => 999,
                ]);
                $validatedData['category_id'] = $newCategory->id;
            }

            // Calculate total inventory from boxes and units per box
            $totalBoxes = $request->input('quantity_of_boxes', 0);
            $unitsPerBox = $request->input('quantity_per_box', 1);
            $totalUnits = $totalBoxes * $unitsPerBox;
            
            // Add inventory tracking fields
            $validatedData['total_boxes'] = $totalBoxes;
            $validatedData['total_units'] = $totalUnits;
            $validatedData['assigned_units'] = 0; // Nothing assigned yet

            // For weight-based selling, calculate default price if not provided
            $sellingMode = $request->input('selling_mode', 'unit');
            if (($sellingMode === 'weight' || $sellingMode === 'box' || $sellingMode === 'both') && empty($validatedData['price'])) {
                $boxWeight = $request->input('box_weight', 0); // Weight per box in kg
                $pricePerKilo = $request->input('price_per_kilo', 0);
                
                if ($boxWeight > 0 && $pricePerKilo > 0) {
                    // Calculate price per unit: (box_weight × price_per_kilo) / units_per_box
                    $pricePerUnit = ($boxWeight * $pricePerKilo) / $unitsPerBox;
                    $validatedData['price'] = round($pricePerUnit, 2);
                } elseif (!empty($request->input('price_per_box'))) {
                    // If price per box is provided, calculate per unit
                    $pricePerBox = $request->input('price_per_box');
                    $validatedData['price'] = round($pricePerBox / $unitsPerBox, 2);
                } elseif ($pricePerKilo > 0) {
                    // Fallback to price per kilo if no box weight
                    $validatedData['price'] = $pricePerKilo;
                }
            }

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
                
                // Use provided price or fall back to product's default price
                if ($request->filled('price')) {
                    $bpData['price'] = $request->input('price');
                } elseif ($product->price) {
                    $bpData['price'] = $product->price;
                }
                
                if ($request->filled('cost_price')) {
                    $bpData['cost_price'] = $request->input('cost_price');
                } elseif ($product->cost_price) {
                    $bpData['cost_price'] = $product->cost_price;
                }
                
                if ($request->filled('reorder_level')) $bpData['reorder_level'] = $request->input('reorder_level');
                
                // Add weight-based pricing fields (use provided or fall back to product defaults)
                if ($request->filled('price_per_kilo')) {
                    $bpData['price_per_kilo'] = $request->input('price_per_kilo');
                } elseif ($product->price_per_kilo) {
                    $bpData['price_per_kilo'] = $product->price_per_kilo;
                }
                
                if ($request->filled('price_per_box')) {
                    $bpData['price_per_box'] = $request->input('price_per_box');
                } elseif ($product->price_per_box) {
                    $bpData['price_per_box'] = $product->price_per_box;
                }
                
                if ($request->filled('weight_unit')) {
                    $bpData['weight_unit'] = $request->input('weight_unit');
                } elseif ($product->weight_unit) {
                    $bpData['weight_unit'] = $product->weight_unit;
                }
                
                if ($request->filled('price_per_unit_weight')) {
                    $bpData['price_per_unit_weight'] = $request->input('price_per_unit_weight');
                } elseif ($product->price_per_unit_weight) {
                    $bpData['price_per_unit_weight'] = $product->price_per_unit_weight;
                }
                
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
            
            // Log product creation activity
            ActivityLogger::logModel('create', $product, [], [
                'name' => $product->name,
                'stock_quantity' => $stockQty ?? 0,
                'price' => $product->price,
                'branch_id' => $branchId ?? null,
            ]);
            
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
        $user = Auth::user();
        
        // Load product with all relationships
        $product = Product::with([
            'business',
            'category',
            'primarySupplier',
            'addedBy',
            'branchProducts.branch',
            'branchProducts' => function($query) use ($user) {
                // Filter branch products based on user role
                if ($user->role === 'manager' && $user->branch_id) {
                    $query->where('branch_id', $user->branch_id);
                } elseif ($user->role === 'business_admin') {
                    $branchIds = Branch::where('business_id', $user->business_id)->pluck('id');
                    $query->whereIn('branch_id', $branchIds);
                }
            }
        ])->findOrFail($id);
        
        // Check access permissions
        if ($user->role === 'business_admin' && $product->business_id !== $user->business_id) {
            abort(403, 'You do not have permission to view this product.');
        }
        
        // Get all branches for the business (for assignment info)
        $branches = Branch::where('business_id', $product->business_id)
            ->orderBy('name')
            ->get();
        
        return view('products.show', compact('product', 'branches'));
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

        // Capture old values for logging
        $oldValues = [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
        ];

        $product->update($validator->validated());
        
        // Log product update activity
        ActivityLogger::logModel('update', $product, $oldValues, [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);
        
        return response()->json($product)->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        // Authorization check
        if ($user->role === 'business_admin' && $product->business_id !== $user->business_id) {
            return response()->json(['error' => 'You can only delete products in your own business.'], 403);
        }

        if ($user->role !== 'superadmin' && $user->role !== 'business_admin') {
            return response()->json(['error' => 'You do not have permission to delete products.'], 403);
        }
        
        // Log product deletion activity
        ActivityLogger::logModel('delete', $product, [
            'name' => $product->name,
            'stock' => $product->total_units ?? 0,
            'price' => $product->price ?? 0,
        ], []);
        
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
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
        } elseif ($user->role === 'business_admin' && $user->business_id) {
            // Business Admin sees all branches in their business
            $branches = Branch::where('business_id', $user->business_id)->orderBy('name')->get();
        } elseif ($user->role === 'manager' && $user->branch_id) {
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
     * Show bulk assignment page (Excel upload)
     */
    public function showBulkAssignment()
    {
        $user = Auth::user();
        
        $userRole = $user->role;
        $userBranchName = null;
        
        if ($user->role === 'business_admin') {
             // For Business Admins without specific branch, show generic "Business Admin" or similar
             // But for bulk assignment VIEW, we generally just need the role context.
             // If we want to show a branch name, we might just show the Business Name if no branch set
             if ($user->branch_id) {
                 $branch = Branch::find($user->branch_id);
                 $userBranchName = $branch ? $branch->name : 'Unknown';
             } else {
                 $userBranchName = 'All Branches (Business Admin)';
             }
        } elseif ($user->role === 'manager') {
            if (empty($user->branch_id)) {
                return view('errors.no-branch');
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
                $user->branch_id // This can act as a default, but the Import class might need handling for null
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
        } elseif ($user->role === 'business_admin') {
             $products = Product::where('business_id', $user->business_id)
                ->with(['category', 'branchProducts.branch'])
                ->get();
             // Allow assigning to ANY branch in the business
             $branches = Branch::where('business_id', $user->business_id)
                ->with('manager')
                ->get();
        } elseif ($user->role === 'manager') {
            if (empty($user->branch_id)) {
                return view('errors.no-branch');
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
        // Superadmin has full access to all branches
    } elseif ($user->role === 'business_admin') {
        // Business Admin can assign to any branch within their business
        if ($branch->business_id !== $user->business_id) {
            return response()->json(['error' => 'Access denied: Branch does not belong to your business'], 403);
        }
    } elseif ($user->role === 'manager') {
        // Manager can ONLY assign to their own branch
        if (empty($user->branch_id) || (int)$user->branch_id !== (int)$branchId) {
            return response()->json(['error' => 'Access denied: You are not assigned to this branch'], 403);
        }
        if ($branch->business_id !== $user->business_id) {
            return response()->json(['error' => 'Access denied: Branch context mismatch'], 403);
        }
    } else {
        return response()->json(['error' => 'Access denied: Insufficient permissions'], 403);
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

            session()->flash('success', $response['message']);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk assignment failed: ' . $e->getMessage());
            return response()->json(['error' => 'Assignment failed: ' . $e->getMessage()], 500);
        }
    }

    
}
