<?php

namespace App\Livewire\Products;

use App\Models\Business;
use App\Models\BranchProduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ManageProducts extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public ?int $editingId = null;
    public string $name = '';
    public $image = null;
    public ?string $description = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $product = Product::findOrFail($id);
        $this->name = $product->name;
        $this->image = $product->image;
        $this->description = (string) ($product->description ?? '');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description', 'image']);
    }

    public function save(): void
    {
        $this->validate();

        $businessId = Business::where('business_admin_id', Auth::id())->value('id');
        if (!$businessId) {
            $this->dispatch('notify', type: 'error', message: 'Create a business first');
            return;
        }

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update([
                'name' => $this->name,
                'description' => $this->description,
                'image' => $this->image,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Product updated');
        } else {
            Product::create([
                'name' => $this->name,
                'description' => $this->description,
                'business_id' => $businessId,
                'image' => $this->image->store('product-images', 'public'),
            ]);
            $this->dispatch('notify', type: 'success', message: 'Product created');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Product::whereKey($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Product deleted');
    }

    public function render()
    {
        $query = BranchProduct::query()
            ->whereHas('branch.business', fn($q) => $q->where('business_admin_id', Auth::id()))
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn($p) => 
                    $p->where('name', 'like', "%{$this->search}%")
                );
            })
            ->with(['product:id,name', 'branch:id,name,business_id'])
            ->latest();

        return view('layouts.product', [
            'products' => $query->paginate(10),
        ]);
    }
}


