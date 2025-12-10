<?php

use function Livewire\Volt\{mount};

mount(function () {
    // Komponent tylko do renderowania modala
});

?>

<x-admin-bar.edit-modal name="promotion-form-modal" title="Dodaj promocję" subtitle="Utwórz nową promocję" min-width="600px"
    max-width="600px" x-data="{ promotionId: null }" @edit-promotion.window="promotionId = $event.detail.id"
    x-on:show="setTimeout(() => {
        const wireId = $el.querySelector('[wire\\:id]')?.getAttribute('wire:id');
        if (wireId) {
            const component = Livewire.find(wireId);
            if (component) {
                component.set('promotionId', promotionId);
                component.call('loadPromotionData');
            }
        }
    }, 100);">
    <livewire:components.admin-bar.promotion-form />
</x-admin-bar.edit-modal>
