@props([
    'class' => '',
])

<button @click="openItem = openItem === $el.dataset.item ? null : $el.dataset.item"
    class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-50 transition-colors {{ $class }}"
    data-item="{{ $attributes->get('data-item', 'default') }}">
    <h3 class="text-lg font-semibold text-gray-900">{{ $slot }}</h3>
    <span class="text-gray-500 text-sm">
        <span x-show="openItem !== $el.dataset.item">Kliknij aby rozwinąć</span>
        <span x-show="openItem === $el.dataset.item">Kliknij aby ukryć</span>
    </span>
</button>
