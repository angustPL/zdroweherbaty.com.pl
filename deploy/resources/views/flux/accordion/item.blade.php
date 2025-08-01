@props([
    'class' => '',
])

<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ $class }}">
    {{ $slot }}
</div>
