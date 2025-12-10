@props([
    'name' => 'editingContent',
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'minHeight' => '400px',
])

@php
    // Użyj przekazanej wartości zamiast wire:model
    $modelValue = $value;
@endphp

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div wire:ignore>
        <textarea x-ref="editor" name="{{ $name }}" x-init="console.log('TinyMCE init for:', @js($name));
        initTinyMCE(@js($name), @js($modelValue), $el)" class="w-full" placeholder="{{ $placeholder }}">{{ $modelValue }}</textarea>
    </div>

    <flux:error name="{{ $name }}" />
</flux:field>
