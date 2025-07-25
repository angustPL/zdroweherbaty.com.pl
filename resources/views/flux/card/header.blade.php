@props([
    'class' => '',
])

<div class="p-6 border-b border-gray-200 {{ $class }}">
    {{ $slot }}
</div>
