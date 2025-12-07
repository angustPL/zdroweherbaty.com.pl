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

    <input type="hidden" id="trix-{{ $name }}" wire:model="{{ $name }}" value="{{ $value }}">

    <div class="flex flex-col border border-gray-300 rounded-md overflow-hidden" x-data="{
        syncValue(value) {
            @this.set('{{ $name }}', value);
        }
    }">
        <trix-toolbar id="trix-toolbar-{{ $name }}"></trix-toolbar>
        <div class="overflow-y-auto max-h-[calc(100vh-400px)]">
            <trix-editor input="trix-{{ $name }}" toolbar="trix-toolbar-{{ $name }}"
                placeholder="{{ $placeholder }}" style="min-height: {{ $minHeight }};"></trix-editor>
        </div>
    </div>

    <script>
        (function() {
            const trixInputId = 'trix-{{ $name }}';
            const propertyName = '{{ $name }}';

            function initializeTrix() {
                const trixEditor = document.querySelector(`trix-editor[input="${trixInputId}"]`);
                if (!trixEditor) return;

                const content = @js($value ?? '');
                if (content) {
                    trixEditor.editor.loadHTML(content);
                }
            }

            function syncWithLivewire(value) {
                const hiddenInput = document.getElementById(trixInputId);
                if (!hiddenInput) return;

                hiddenInput.value = value;
                // Wywołaj event input dla Livewire
                hiddenInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }

            document.addEventListener('trix-initialize', function(event) {
                if (event.target && event.target.input === trixInputId) {
                    initializeTrix();
                }
            });

            document.addEventListener('trix-change', function(event) {
                if (event.target && event.target.input === trixInputId) {
                    syncWithLivewire(event.target.value);
                }
            });

            // Inicjalizacja po załadowaniu Livewire
            document.addEventListener('livewire:init', function() {
                setTimeout(initializeTrix, 100);
            });
        })();
    </script>

    <flux:error name="{{ $name }}" />
</flux:field>
