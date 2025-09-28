<div class="space-y-4">
    <x-input label="Business name" wire:model.defer="name" />
    <x-input type="file" label="Logo" wire:model="logo" />
    <x-button primary label="Create Business" wire:click="save" />

    <x-notifications />
</div>


