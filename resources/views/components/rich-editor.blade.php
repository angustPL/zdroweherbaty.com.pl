@props([
    'name' => 'content',
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'minHeight' => '400px',
])

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div wire:ignore>
        <textarea x-ref="editor" name="{{ $name }}" x-init="initTinyMCE(@js($name), @js($value), $el)" class="w-full" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
    </div>

    <flux:error name="{{ $name }}" />
</flux:field>
