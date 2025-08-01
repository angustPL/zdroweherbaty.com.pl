@props([
    'class' => '',
])

<h3 class="text-lg font-semibold text-gray-900 {{ $class }}">
    {{ $slot }}
</h3>
