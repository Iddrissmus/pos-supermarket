<?php

namespace App\Livewire\BranchProducts;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Assign extends Component
{
    public ?int $branch_id = null;
    public ?int $product_id = null;
    public ?float $price = null;
    public ?float $cost_price = null;
    public ?int $stock_quantity = null;
    public ?int $reorder_level = null;
    
    // For updating specific products
    public ?int $updating_product_id = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $user = Auth::user();
        
        // STRICT: Only Business Admins allowed
        if ($user->role !== 'business_admin') {
            abort(403, 'Unauthorized. Only Business Admins can manually assign stock.');
        }
        
        $this->stock_quantity = $this->stock_quantity ?? 0;
        $this->reorder_level = $this->reorder_level ?? 0;
    }

    public function assign(): void
    {
        $validated = $this->validate();
        $branch = Branch::with('business')->findOrFail($validated['branch_id']);
        $product = Product::with('business')->findOrFail($validated['product_id']);

        // 1. Validate Central Stock Availability
        $requestQuantity = (int)$validated['stock_quantity'];
        
        if ($requestQuantity > 0) {
            if (!$product->hasAvailableUnits($requestQuantity)) {
                $this->dispatch('notify', type: 'error', message: 'Insufficient central stock. Available: ' . $product->available_units);
                return;
            }
        }

        // Check if product is already assigned to this branch
        $existingBranchProduct = \App\Models\BranchProduct::where([
            'branch_id' => $validated['branch_id'],
            'product_id' => $validated['product_id']
        ])->first();

        if ($existingBranchProduct) {
            // Product already exists in this branch
            $newCostPrice = $validated['cost_price'] ?? 0;
            
            if ($existingBranchProduct->cost_price == $newCostPrice) {
                // Same cost price - add to existing stock
                
                // DEDUCT FROM CENTRAL STOCK
                if ($requestQuantity > 0) {
                    $product->assignUnits($requestQuantity);
                }

                $existingBranchProduct->adjustStock(
                    $requestQuantity, 
                    'stock_addition', 
                    'Stock added via product assignment'
                );
                
                // Update price and reorder level if different
                $existingBranchProduct->update([
                    'price' => $validated['price'],
                    'reorder_level' => $validated['reorder_level'] ?? 0,
                ]);
                
                $message = 'Stock added to existing product in ' . $branch->display_label . ' (+' . $requestQuantity . ' units)';
            } else {
                // Different cost price - update/overwrite
                // We need to account for stock changes accurately.
                
                $oldStock = $existingBranchProduct->stock_quantity;
                
                // Return old stock to central
                if ($oldStock > 0) {
                    $product->unassignUnits($oldStock);
                }
                
                // Take new stock from central
                // We must re-check availability because we just returned some, but maybe not enough if we are asking for MORE
                // Actually, unassignUnits runs immediately so available units increase.
                
                // Refresh product to get updated available units
                $product->refresh();
                
                if ($requestQuantity > 0) {
                     if (!$product->hasAvailableUnits($requestQuantity)) {
                         // Rollback: Re-assign the old stock back to central effectively (undo the unassign)
                         $product->assignUnits($oldStock);
                         
                         $this->dispatch('notify', type: 'error', message: 'Insufficient central stock for new quantity.');
                         return;
                     }
                     $product->assignUnits($requestQuantity);
                }

                $existingBranchProduct->update([
                    'price' => $validated['price'],
                    'cost_price' => $newCostPrice,
                    'stock_quantity' => $requestQuantity,
                    'reorder_level' => $validated['reorder_level'] ?? 0,
                ]);
                
                $message = 'Product assignment updated in ' . $branch->display_label . ' (different cost price detected)';
            }
        } else {
            // New Assignment
            
            // DEDUCT FROM CENTRAL STOCK
            if ($requestQuantity > 0) {
                $product->assignUnits($requestQuantity);
            }

            $branch->products()->attach($validated['product_id'], [
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'stock_quantity' => $requestQuantity,
                'reorder_level' => $validated['reorder_level'] ?? 0,
            ]);
            
            $message = 'Product assigned to ' . $branch->display_label;
        }

        // Trigger reorder check
        try {
             (new \App\Services\StockReorderService())->checkItem(
                 $validated['branch_id'], 
                 $validated['product_id']
             );
         } catch (\Throwable $e) {
             logger()->error('StockReorderService failed: ' . $e->getMessage());
         }

        $this->reset(['product_id', 'price', 'cost_price', 'stock_quantity', 'reorder_level']);
        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function startUpdate($productId)
    {
        // Load the current values into the form
        $branchProduct = $this->getBranchProduct($productId);
        if ($branchProduct) {
            $this->updating_product_id = $productId;
            $this->product_id = $productId;
            $this->price = $branchProduct->pivot->price;
            $this->cost_price = $branchProduct->pivot->cost_price;
            $this->stock_quantity = $branchProduct->pivot->stock_quantity;
            $this->reorder_level = $branchProduct->pivot->reorder_level;
            
            $this->dispatch('notify', type: 'info', message: 'Product loaded for updating. Modify values and click "Update Product".');
        }
    }

    public function cancelUpdate()
    {
        $this->updating_product_id = null;
        $this->reset(['product_id', 'price', 'cost_price', 'stock_quantity', 'reorder_level']);
    }

    public function updatePivot(int $productId): void
    {
        // Manual edit
        $this->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);
        
        if (!$this->branch_id) return;
        
        $branch = Branch::with('business')->findOrFail($this->branch_id);
        $product = Product::findOrFail($productId);
        
        // Handle Stock Changes Logic
        if ($this->stock_quantity !== null) {
             $currentPivot = $branch->products()->where('product_id', $productId)->first()->pivot;
             $currentStock = $currentPivot->stock_quantity;
             $newStock = $this->stock_quantity;
             
             $diff = $newStock - $currentStock;
             
             if ($diff > 0) {
                 // Trying to increase branch stock -> Deduct from Central
                 if (!$product->hasAvailableUnits($diff)) {
                     $this->dispatch('notify', type: 'error', message: 'Insufficient central stock to increase by ' . $diff);
                     return;
                 }
                 $product->assignUnits($diff);
             } elseif ($diff < 0) {
                 // Trying to decrease branch stock -> Return to Central
                 $product->unassignUnits(abs($diff));
             }
        }
        
        $branch->products()->updateExistingPivot($productId, array_filter([
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock_quantity' => $this->stock_quantity,
            'reorder_level' => $this->reorder_level,
        ], fn($v) => $v !== null));
        
        // Reset form after successful update
        $this->updating_product_id = null;
        $this->reset(['product_id', 'price', 'cost_price', 'stock_quantity', 'reorder_level']);
        
        $this->dispatch('notify', type: 'success', message: 'Assignment updated for ' . $branch->display_label);
    }

    private function getBranchProduct($productId)
    {
        if (!$this->branch_id) return null;
        
        return Branch::with(['products' => function($q) use ($productId) {
            $q->where('product_id', $productId);
        }])->find($this->branch_id)?->products->first();
    }

    public function detach(int $productId): void
    {
        if (!$this->branch_id) return;
        $branch = Branch::with('business')->findOrFail($this->branch_id);
        
        // When detaching, we should probably return stock to central?
        // Assuming detach means "remove product from branch", the stock should go back to warehouse.
        $pivot = $branch->products()->where('product_id', $productId)->first()?->pivot;
        if ($pivot && $pivot->stock_quantity > 0) {
             $product = Product::find($productId);
             if ($product) {
                 $product->unassignUnits($pivot->stock_quantity);
             }
        }

        $branch->products()->detach($productId);
        $this->dispatch('notify', type: 'success', message: 'Product removed from ' . $branch->display_label . ' and stock returned to warehouse.');
    }

    public function render()
    {
        $user = Auth::user();
        $branches = collect();
        $products = collect();
        $selectedBranch = null;
        $assigned = collect();

        // Business admin can see all branches and products in their business
        // We know it is admin because of mount check
        $branches = Branch::where('business_id', $user->business_id)
            ->with('business')
            ->orderBy('name')
            ->get();
        $products = Product::where('business_id', $user->business_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        if ($this->branch_id) {
            $selectedBranch = Branch::with([
                'business',
                'products' => function ($q) {
                    $q->orderBy('name');
                }
            ])->find($this->branch_id);
            $assigned = $selectedBranch?->products ?? collect();
        } else {
            // If no branch selected, show all product assignments across business branches
            $assigned = Branch::where('business_id', $user->business_id)
                ->with(['products', 'business'])
                ->whereHas('products')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.branch-products.assign', [
            'branches' => $branches,
            'products' => $products,
            'assigned' => $assigned,
            'selectedBranch' => $selectedBranch,
            'isAdmin' => true,
        ]);
    }
}
