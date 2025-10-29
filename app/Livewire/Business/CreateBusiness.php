<?php

namespace App\Livewire\Business;

use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            $businessAdminId = Auth::id();
            if (!$businessAdminId) {
                throw new \RuntimeException('Not authenticated');
            }

            $data = [
                'name' => $this->name,
                'business_admin_id' => $businessAdminId,
            ];

            if ($this->logo) {
                $data['logo'] = $this->logo->store('logos', 'public');
            }

            $business = Business::create($data);
            
            // Update user's business_id
            Auth::user()->update(['business_id' => $business->id]);

            $this->reset(['name', 'logo']);
            $this->dispatch('notify', type: 'success', message: 'Business created successfully!');
        } catch (\Throwable $e) {
            Log::error('CreateBusiness save failed: '.$e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Failed to create business: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.business.create-business', [
            'myBusinesses' => Business::where('business_admin_id', Auth::id())->latest()->limit(50)->get(),
        ]);
    }
}


