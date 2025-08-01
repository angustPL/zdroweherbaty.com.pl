{{-- Logika: app/Livewire/Components/AddToCartButton.php --}}
<div x-data="{ isHovered: false }" @mouseenter="isHovered = true" @mouseleave="isHovered = false">
    @if ($isInCart)
        <flux:button href="{{ route('koszyk') }}" variant="filled" class="flex w-full items-center justify-center">
            <span wire:loading.remove x-text="isHovered ? 'Do koszyka' : 'W koszyku'">
            </span>
            <span wire:loading>Dodawanie...</span>
            <svg x-show="isHovered" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17">
                </path>
            </svg>
        </flux:button>
    @else
        <flux:button variant="filled" wire:click="addToCart" class="flex w-full items-center justify-center"
            wire:loading.attr="disabled">
            <span wire:loading.remove>
                Dodaj do koszyka
            </span>
            <span wire:loading>Dodawanie...</span>
        </flux:button>
    @endif
</div>
