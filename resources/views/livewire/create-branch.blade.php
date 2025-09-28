<div class="space-y-4">
    <x-select
        label="Business"
        :options="$businesses->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->toArray()"
        option-label="name"
        option-value="id"
        wire:model="business_id"
    />
    <x-input label="Branch name" wire:model.defer="name" />
    <x-input label="Location" wire:model.defer="location" />
    <x-input label="Contact" wire:model.defer="contact" />
    <x-button primary label="Create Branch" wire:click="save" style="color:aqua"/>

    <x-notifications />
</div>


