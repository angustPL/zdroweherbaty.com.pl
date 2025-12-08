<?php

use function Livewire\Volt\{mount};
use Illuminate\Support\Facades\Auth;

mount(function () {
    // Sprawdź czy użytkownik jest zalogowany
    if (!Auth::check() || (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('editor'))) {
        abort(403, 'Brak dostępu');
    }
});

?>

<div class="flex justify-end space-x-2 rtl:space-x-reverse w-full">
    <flux:modal.close>
        <flux:button type="button" variant="ghost">Anuluj</flux:button>
    </flux:modal.close>
    <flux:button type="submit" variant="primary" form="promotion-form">Zapisz</flux:button>
</div>
