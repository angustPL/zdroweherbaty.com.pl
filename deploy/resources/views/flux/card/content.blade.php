@props([
    'class' => '',
])

<div class="p-6 {{ $class }}">
    {{ $slot }}
</div>
