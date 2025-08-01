{{-- Logika: app/Livewire/Components/CartIcon.php --}}
<div class="relative transition-all duration-300 cart-icon">
    <a href="{{ route('koszyk') }}"
        class="flex items-center gap-2 px-3 py-2 text-primary hover:text-primary transition-colors">
        <div class="relative">
            <flux:icon.shopping-basket class="w-12 h-12 -my-1" />
            @if ($itemCount > 0)
                <flux:badge size="sm" variant="solid" color="zinc" class="absolute -bottom-2 -right-2">
                    {{ $itemCount > 99 ? '99+' : $itemCount }}
                </flux:badge>
            @endif
        </div>
    </a>
</div>
