<!-- Newsletter Modal - tylko wizualnie -->
<flux:modal wire:model="showModal" class="p-0 border-0 w-full max-w-3xl" style="padding: 0 !important;">
    <div class="relative text-center text-white w-full"
        style="padding: 0 !important; background-image: url('/img/newsletter-bg-opt.jpg'); background-position: center; background-size: cover; min-height: 400px;">

        <div class="absolute bottom-0 left-0 right-0 p-8" style="padding-top: 30vh !important;">

            @if ($isSubscribed)
                <div class="text-center">
                    <h2 class="text-4xl font-bold mb-4">Dziękujemy!</h2>
                    <p class="text-xl mb-6">Zapisaliśmy Twój e-mail do naszego Newslettera</p>
                    <flux:button variant="outline" wire:click="closeModal">Zamknij</flux:button>
                </div>
            @else
                <h2 class="text-4xl font-bold mb-4">Lubisz niespodzianki?</h2>
                <p class="text-2xl mb-6">Zapisz się na nasz Newsletter</p>
                <flux:input.group>
                    <flux:input type="email" wire:model="email" placeholder="Twój adres e-mail" />
                    <flux:button variant="primary" wire:click="subscribe">Zapisz się</flux:button>
                    <flux:button wire:click="hideForToday">Nie dzisiaj</flux:button>
                </flux:input.group>

                @if ($errorMessage)
                    <div class="text-red-300 text-sm mt-2">{{ $errorMessage }}</div>
                @endif
            @endif
        </div>
    </div>
</flux:modal>
