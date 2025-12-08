<?php

use function Livewire\Volt\{mount};
use Illuminate\Support\Facades\Auth;

mount(function () {
    // Sprawdź czy użytkownik jest adminem
    if (!Auth::check() || !Auth::user()->hasRole('admin')) {
        abort(403, 'Brak dostępu');
    }
});

?>

<flux:modal name="users-modal" flyout position="left" class="md:w-[800px] m-0! rounded-none! h-screen!">
    <div class="flex flex-col h-full">
        <div class="shrink-0 pb-4 border-b">
            <flux:heading size="lg">Użytkownicy</flux:heading>
            <flux:subheading>Lista wszystkich użytkowników systemu</flux:subheading>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <livewire:components.users-list />
        </div>

        <div class="shrink-0 flex justify-end space-x-2 rtl:space-x-reverse pt-4 border-t bg-white sticky bottom-0">
            <flux:modal.close>
                <flux:button variant="primary">Zamknij</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
