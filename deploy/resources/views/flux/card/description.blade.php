@props([
    'class' => '',
])

<p class="text-gray-600 {{ $class }}">
    {{ $slot }}
</p>
