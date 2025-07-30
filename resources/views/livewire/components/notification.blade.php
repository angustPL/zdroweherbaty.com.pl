<?php

use function Livewire\Volt\{state};

state(['notifications' => []]);

$addNotification = function ($data) {
    $id = uniqid();
    $this->notifications[$id] = [
        'id' => $id,
        'type' => $data['type'],
        'message' => $data['message'],
        'timestamp' => now(),
    ];

    // Automatycznie usuń powiadomienie po 5 sekundach
    $this->dispatch('remove-notification', $id)->delay(5000);
};

$removeNotification = function ($id) {
    if (isset($this->notifications[$id])) {
        unset($this->notifications[$id]);
    }
};

?>

<div class="fixed top-4 right-4 z-50 space-y-2" wire:on.notify="addNotification"
    wire:on.remove-notification="removeNotification">
    @foreach ($notifications as $notification)
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">

            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if ($notification['type'] === 'success')
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($notification['type'] === 'error')
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>

                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $notification['message'] }}
                        </p>
                    </div>

                    <div class="ml-4 flex-shrink-0 flex">
                        <button wire:click="removeNotification('{{ $notification['id'] }}')"
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Zamknij</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
