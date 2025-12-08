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

<flux:modal name="user-form-modal" flyout position="left" class="md:w-[600px] m-0! rounded-none! h-screen!"
    x-data="{ userId: null }" 
    @edit-user.window="userId = $event.detail.id">
    <div class="flex flex-col h-full overflow-hidden">
        <div class="shrink-0 pb-4 border-b">
            <flux:heading size="lg" x-text="userId ? 'Edytuj użytkownika' : 'Dodaj użytkownika'">
            </flux:heading>
            <flux:subheading x-text="userId ? 'Zaktualizuj dane użytkownika' : 'Utwórz nowego użytkownika'">
            </flux:subheading>
        </div>

        <div class="flex-1 overflow-y-auto min-h-0 py-4">
            <div x-init="$watch('userId', (value) => {
                setTimeout(() => {
                    const wireId = $el.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                    if (wireId) {
                        const component = Livewire.find(wireId);
                        if (component) {
                            component.set('userId', value);
                            component.call('loadUserData');
                        }
                    }
                }, 100);
            });">
                <livewire:components.user-form />
            </div>
        </div>
    </div>
</flux:modal>

