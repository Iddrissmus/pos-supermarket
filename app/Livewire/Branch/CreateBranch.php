<?php

namespace App\Livewire\Branch;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateBranch extends Component
{
    public ?int $business_id = null;
    public string $name = '';
    public string $contact = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $address = null;

    public function mount(): void
    {
        $this->business_id = Business::where('owner_id', Auth::id())->value('id');
    }

    protected function rules(): array
    {
        return [
            'business_id' => ['required', 'exists:businesses,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['manager_id'] = Auth::id();
        Branch::create($validated);
        $this->reset(['name', 'contact', 'latitude', 'longitude', 'address']);
        $this->dispatch('notify', type: 'success', message: 'Branch created');
    }

    public function render()
    {
        return view('livewire.branch.create-branch', [
            'businesses' => Business::orderBy('name')->limit(100)->get(['id','name'])
        ]);
    }
}


