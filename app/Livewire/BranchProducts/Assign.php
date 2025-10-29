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
        
        // Only auto-select branch for managers, not admins
        if ($user->role === 'manager' && $user->branch_id) {
            $this->branch_id = $user->branch_id;
        }
        
        $this->stock_quantity = $this->stock_quantity ?? 0;
        $this->reorder_level = $this->reorder_level ?? 0;
    }

    public function assign(): void
    {
        $validated = $this->validate();
        $branch = Branch::with('business')->findOrFail($validated['branch_id']);

        // Check if product is already assigned to this branch
        $existingBranchProduct = \App\Models\BranchProduct::where([
            'branch_id' => $validated['branch_id'],
            'product_id' => $validated['product_id']
        ])->first();

        if ($existingBranchProduct) {
            // Product already exists in this branch
            $newCostPrice = $validated['cost_price'] ?? 0;
            
            if ($existingBranchProduct->cost_price == $newCostPrice) {
                // Same cost price - add to existing stock using adjustStock method
                $existingBranchProduct->adjustStock(
                    $validated['stock_quantity'], 
                    'stock_addition', 
                    'Stock added via product assignment'
                );
                
                // Update price and reorder level if different
                $existingBranchProduct->update([
                    'price' => $validated['price'],
                    'reorder_level' => $validated['reorder_level'] ?? 0,
                ]);
                
                $message = 'Stock added to existing product in ' . $branch->display_label . ' (+' . $validated['stock_quantity'] . ' units)';
            } else {
                // Different cost price - update all values (replace record)
                $oldStock = $existingBranchProduct->stock_quantity;
                $existingBranchProduct->update([
                    'price' => $validated['price'],
                    'cost_price' => $newCostPrice,
                    'stock_quantity' => $validated['stock_quantity'],
                    'reorder_level' => $validated['reorder_level'] ?? 0,
                ]);
                
                // Trigger reorder check after stock update
                try {
                    (new \App\Services\StockReorderService())->checkItem(
                        $existingBranchProduct->branch_id, 
                        $existingBranchProduct->product_id
                    );
                } catch (\Throwable $e) {
                    logger()->error('StockReorderService failed: ' . $e->getMessage());
                }
                
                $message = 'Product assignment updated in ' . $branch->display_label . ' (different cost price detected)';
            }
        } else {
            // Product doesn't exist in this branch - create new assignment
            $branch->products()->attach($validated['product_id'], [
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'stock_quantity' => $validated['stock_quantity'],
                'reorder_level' => $validated['reorder_level'] ?? 0,
            ]);
            
            // Trigger reorder check for newly assigned product
            try {
                (new \App\Services\StockReorderService())->checkItem(
                    $validated['branch_id'], 
                    $validated['product_id']
                );
            } catch (\Throwable $e) {
                logger()->error('StockReorderService failed: ' . $e->getMessage());
            }
            
            $message = 'Product assigned to ' . $branch->display_label;
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
        // Manual edit - sets exact values (doesn't add to existing stock)
        $this->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);
        
        if (!$this->branch_id) return;
        
        $branch = Branch::with('business')->findOrFail($this->branch_id);
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
        $branch->products()->detach($productId);
    $this->dispatch('notify', type: 'success', message: 'Product removed from ' . $branch->display_label);
    }

    public function render()
    {
        $user = Auth::user();
        $branches = collect();
        $products = collect();
        $selectedBranch = null;
        $assigned = collect();

        if ($user->role === 'business_admin') {
            // Business admin can see all branches and products in their business
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
            
        } elseif ($user->role === 'manager' && $user->branch_id) {
            // Manager can only see their own branch
            $userBranch = Branch::find($user->branch_id);
            if ($userBranch) {
                $branches = Branch::with('business')
                    ->where('id', $user->branch_id)
                    ->get();
                $products = Product::where('business_id', $userBranch->business_id)
                    ->orderBy('name')
                    ->get(['id', 'name']);
                
                // Manager's branch should already be selected from mount()
                if ($this->branch_id) {
                    $selectedBranch = Branch::with([
                        'business',
                        'products' => function ($q) {
                            $q->orderBy('name');
                        }
                    ])->find($this->branch_id);
                    $assigned = $selectedBranch?->products ?? collect();
                }
            }
        }

        return view('livewire.branch-products.assign', [
            'branches' => $branches,
            'products' => $products,
            'assigned' => $assigned,
            'selectedBranch' => $selectedBranch,
            'isAdmin' => $user->role === 'business_admin',
        ]);
    }
}


