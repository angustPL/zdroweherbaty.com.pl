<?php

use function Livewire\Volt\{mount};

mount(function () {
    // Komponent tylko do renderowania modala
});

?>

<flux:modal name="promotions-modal" flyout position="left" class="md:w-[1000px] m-0! rounded-none! h-screen!">
    <div class="flex flex-col h-full">
        <div class="shrink-0 pb-4 border-b">
            <flux:heading size="lg">Promocje</flux:heading>
            <flux:subheading>Zarządzanie promocjami w sklepie</flux:subheading>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <livewire:components.promotions-list />
        </div>

        <div class="shrink-0 flex justify-end space-x-2 rtl:space-x-reverse pt-4 border-t bg-white sticky bottom-0">
            <flux:modal.close>
                <flux:button variant="primary">Zamknij</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
