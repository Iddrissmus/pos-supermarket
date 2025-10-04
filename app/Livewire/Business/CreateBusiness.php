<?php

namespace App\Livewire\Business;

use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
// use WireUi\Traits\Actions;

class CreateBusiness extends Component
{
    use WithFileUploads;
    // use Actions;
    public string $name = '';
    public $logo = null; // temporary uploaded file

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $this->validate();
        try {
            $ownerId = Auth::id();
            if (!$ownerId) {
                throw new \RuntimeException('Not authenticated');
            }

            $data = [
                'name' => $this->name,
                'owner_id' => $ownerId,
            ];

            if ($this->logo) {
                $data['logo'] = $this->logo->store('logos', 'public');
            }

            Business::create($data);

            $this->reset(['name', 'logo']);
            $this->dispatch('notify', type: 'success', message: 'Business created successfully!');
        } catch (\Throwable $e) {
            \Log::error('CreateBusiness save failed: '.$e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Failed to create business: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.business.create-business', [
            'myBusinesses' => Business::where('owner_id', Auth::id())->latest()->limit(50)->get(),
        ]);
    }
}


