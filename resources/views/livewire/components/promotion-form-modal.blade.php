<?php

use function Livewire\Volt\{mount};

mount(function () {
    // Komponent tylko do renderowania modala
});

?>

<flux:modal name="promotion-form-modal" flyout position="left" class="md:w-[600px] m-0! rounded-none! h-screen!"
    x-data="{ promotionId: null }" @edit-promotion.window="promotionId = $event.detail.id">
    <div class="flex flex-col h-full overflow-hidden">
        <div class="shrink-0 pb-4 border-b">
            <flux:heading size="lg" x-text="promotionId ? 'Edytuj promocję' : 'Dodaj promocję'">
            </flux:heading>
            <flux:subheading x-text="promotionId ? 'Zaktualizuj dane promocji' : 'Utwórz nową promocję'">
            </flux:subheading>
        </div>

        <div class="flex-1 overflow-y-auto min-h-0 py-4">
            <div x-init="$watch('promotionId', (value) => {
                setTimeout(() => {
                    const wireId = $el.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                    if (wireId) {
                        const component = Livewire.find(wireId);
                        if (component) {
                            component.set('promotionId', value);
                            component.call('loadPromotionData');
                        }
                    }
                }, 100);
            });">
                <livewire:components.promotion-form />
            </div>
        </div>
    </div>
</flux:modal>
