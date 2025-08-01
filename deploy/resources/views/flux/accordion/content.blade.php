@props([
    'class' => '',
])

<div x-show="openItem === $el.dataset.item" x-transition class="px-6 pb-6 {{ $class }}"
    data-item="{{ $attributes->get('data-item', 'default') }}">
    {{ $slot }}
</div>
