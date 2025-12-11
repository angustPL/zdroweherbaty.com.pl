@props([
    'show' => false,
    'message' => 'Zapisano.',
    'type' => 'success', // success, error, warning
    'overlay' => true,
    'showBackground' => true, // Czy pokazywać czarne tło w overlay
    'timeout' => 3000,
    'wireProperty' => 'show', // Nazwa property w Livewire do ustawiania
])

@if ($show && $overlay)
    <div class="fixed inset-0 z-50 flex items-center justify-center @if ($showBackground) bg-black bg-opacity-50 @endif"
        x-data x-init="setTimeout(() => $wire.set('{{ $wireProperty }}', false), {{ $timeout }})">
        <div
            class="p-6 rounded-lg shadow-xl max-w-sm mx-4 @if ($type === 'success') bg-green-50 border border-green-200 @elseif($type === 'error') bg-red-50 border-red-200 @elseif($type === 'warning') bg-yellow-50 border-yellow-200 @endif">
            <div class="flex items-center">
                @if ($type === 'success')
                    <svg class="w-6 h-6 text-green-600 mr-3 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ $message }}</p>
                @elseif($type === 'error')
                    <svg class="w-6 h-6 text-red-600 mr-3 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                @elseif($type === 'warning')
                    <svg class="w-6 h-6 text-yellow-600 mr-3 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <p class="text-sm font-medium text-yellow-800">{{ $message }}</p>
                @endif
            </div>
        </div>
    </div>
@elseif($show && !$overlay)
    <div class="mb-4 p-4 rounded-lg border @if ($type === 'success') bg-green-50 border-green-200 @elseif($type === 'error') bg-red-50 border-red-200 @elseif($type === 'warning') bg-yellow-50 border-yellow-200 @endif"
        x-data x-init="setTimeout(() => $wire.set('{{ $wireProperty }}', false), {{ $timeout }})">
        <div class="flex items-center">
            @if ($type === 'success')
                <svg class="w-5 h-5 text-green-600 mr-2 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ $message }}</p>
            @elseif($type === 'error')
                <svg class="w-5 h-5 text-red-600 mr-2 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ $message }}</p>
            @elseif($type === 'warning')
                <svg class="w-5 h-5 text-yellow-600 mr-2 shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <p class="text-sm font-medium text-yellow-800">{{ $message }}</p>
            @endif
        </div>
    </div>
@endif
