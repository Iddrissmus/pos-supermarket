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
        // Default to first owned branch if available
        $this->branch_id = Branch::whereHas('business', function ($q) {
            $q->where('owner_id', Auth::id());
        })->value('id');
        $this->stock_quantity = $this->stock_quantity ?? 0;
        $this->reorder_level = $this->reorder_level ?? 0;
    }

    public function assign(): void
    {
        $validated = $this->validate();
        $branch = Branch::findOrFail($validated['branch_id']);

        $branch->products()->syncWithoutDetaching([
            $validated['product_id'] => [
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'] ?? 0,
                'stock_quantity' => $validated['stock_quantity'],
                'reorder_level' => $validated['reorder_level'] ?? 0,
            ],
        ]);

        $this->reset(['product_id', 'price', 'cost_price', 'stock_quantity', 'reorder_level']);
        $this->dispatch('notify', type: 'success', message: 'Product assigned to branch');
    }

    public function updatePivot(int $productId): void
    {
        $this->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);
        if (!$this->branch_id) return;
        $branch = Branch::findOrFail($this->branch_id);
        $branch->products()->updateExistingPivot($productId, array_filter([
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock_quantity' => $this->stock_quantity,
            'reorder_level' => $this->reorder_level,
        ], fn($v) => $v !== null));
        $this->dispatch('notify', type: 'success', message: 'Assignment updated');
    }

    public function detach(int $productId): void
    {
        if (!$this->branch_id) return;
        $branch = Branch::findOrFail($this->branch_id);
        $branch->products()->detach($productId);
        $this->dispatch('notify', type: 'success', message: 'Product removed from branch');
    }

    public function render()
    {
        $ownerId = Auth::id();
        $branches = Branch::with('business')
            ->whereHas('business', fn($q) => $q->where('owner_id', $ownerId))
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name']);

        $businessId = Business::where('owner_id', $ownerId)->value('id');
        $products = Product::when($businessId, fn($q) => $q->where('business_id', $businessId))
            ->orderBy('name')
            ->limit(200)
            ->get(['id','name']);

        $assigned = collect();
        if ($this->branch_id) {
            $assigned = Branch::with(['products' => function ($q) {
                $q->orderBy('name');
            }])->find($this->branch_id)?->products ?? collect();
        }

        return view('livewire.branch-products.assign', [
            'branches' => $branches,
            'products' => $products,
            'assigned' => $assigned,
        ]);
    }
}


