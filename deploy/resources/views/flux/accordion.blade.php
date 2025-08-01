@props([
    'class' => '',
])

<div class="space-y-4 {{ $class }}" x-data="{ openItem: null }">
    {{ $slot }}
</div>
