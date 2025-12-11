@props([
    'name' => 'edit-modal',
    'title' => 'Edytuj',
    'subtitle' => null,
    'width' => null, // deprecated
    'minWidth' => '400px', // minimalna szerokość
    'maxWidth' => '50vw', // maks. 50% szerokości okna
    'saveAction' => null,
    'editorId' => 'editingContent',
    'showSuccess' => false,
    'successMessage' => 'Zmiany zostały zapisane.',
])

@php
    logger('Edit-modal showSuccess: ' . ($showSuccess ? 'true' : 'false'));
    // Unikalny identyfikator dla każdej instancji komponentu
    $instanceId = 'edit-modal-' . uniqid();
@endphp

<flux:modal name="{{ $name }}" flyout position="left" class="m-0! rounded-none! h-screen! flex flex-col p-0!"
    {{ $attributes }}>
    {{-- Komunikat sukcesu na środku całego modala --}}
    @if ($showSuccess)
        <div class="absolute left-1/2 top-1/2 z-50 -translate-x-1/2 -translate-y-1/2 bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg"
            x-data="{ show: true }" x-init="setTimeout(() => {
                show = false;
                @this.set('saved', false)
            }, 3000)" x-show="show" x-transition.duration.1000ms>
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">
                    {{ $successMessage }}
                </p>
            </div>
        </div>
    @endif

    <form class="flex flex-col h-full"
        @if ($saveAction) wire:submit.prevent="{{ $saveAction }}" @endif>
        <div class="shrink-0 p-6 pb-6 border-b">
            <flux:heading size="lg">{{ $title }}</flux:heading>
            @if ($subtitle)
                <flux:subheading>{{ $subtitle }}</flux:subheading>
            @endif
        </div>

        <div class="flex-1 p-6 overflow-y-auto relative">
            {{ $slot }}
        </div>

        <div
            class="shrink-0 flex justify-between space-x-2 rtl:space-x-reverse p-6 pt-6 border-t bg-white sticky bottom-0">
            <!-- Dodatkowe akcje (slot) - po lewej stronie - tylko jeśli nie puste -->
            @if (!empty($extraActions))
                <div class="flex space-x-2">
                    {{ $extraActions }}
                </div>
            @endif

            <!-- Buttony akcji - po prawej stronie -->
            <div class="flex space-x-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Zamknij</flux:button>
                </flux:modal.close>
                @if ($saveAction)
                    <flux:button type="submit" variant="primary">Zapisz</flux:button>
                @else
                    <flux:button type="button" variant="primary" wire:click="saveContent">Zapisz</flux:button>
                @endif
            </div>
        </div>
    </form>
</flux:modal>
