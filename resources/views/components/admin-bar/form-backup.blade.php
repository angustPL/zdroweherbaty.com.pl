{{-- Kopia zapasowa formularza do testów --}}
<div class="space-y-6">
    {{-- Podstawowe informacje --}}
    <div class="border border-gray-200 rounded-lg bg-white">
        <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
            Podstawowe informacje</flux:heading>
        <div class="p-4 pt-3 space-y-3">
            <flux:field>
                <flux:label>Nazwa</flux:label>
                <flux:input type="text" wire:model="name" required />
            </flux:field>

            <flux:field>
                <flux:label>Opis</flux:label>
                <flux:textarea wire:model="description" rows="3" />
            </flux:field>
        </div>
    </div>

    {{-- Ustawienia --}}
    <div class="border border-gray-200 rounded-lg bg-white">
        <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
            Ustawienia</flux:heading>
        <div class="p-4 pt-3 space-y-3">
            <flux:checkbox wire:model="is_active" label="Aktywne" />
            <flux:checkbox wire:model="can_combine" label="Można łączyć" />
        </div>
    </div>
</div>
