<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateBranch extends Component
{
    public ?int $business_id = null;
    public string $name = '';
    public string $location = '';
    public string $contact = '';

    public function mount(): void
    {
        // if owner, default to their first business
        $this->business_id = Business::where('owner_id', Auth::id())->value('id');
    }

    protected function rules(): array
    {
        return [
            'business_id' => ['required', 'exists:businesses,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        try {
            Branch::create($validated);
            
            $this->reset(['name', 'location', 'contact']);
            $this->dispatch('notify', type: 'success', message: 'Branch created successfully!');
        } catch (\Throwable $e) {
            \Log::error('CreateBranch save failed: '.$e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Failed to create branch: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.branch.create-branch', [
            'businesses' => Business::orderBy('name')->get(['id','name'])
        ]);
    }
}
