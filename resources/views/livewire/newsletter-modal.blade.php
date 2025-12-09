<!-- Newsletter Modal - tylko wizualnie -->
<flux:modal wire:model="showModal" class="p-0 border-0 w-full max-w-3xl" style="padding: 0 !important;">
    <div class="relative text-center text-white w-full"
        style="padding: 0 !important; background-image: url('/img/newsletter-bg-opt.jpg'); background-position: center; background-size: cover; min-height: 400px;">

        <div class="absolute bottom-0 left-0 right-0 p-8" style="padding-top: 30vh !important;">
            <h2 class="text-2xl font-bold mb-4">Lubisz niespodzianki?</h2>
            <p class="text-lg mb-6">Zapisz się na nasz Newsletter</p>

            <flux:input.group>
                <flux:input type="email" placeholder="Twój adres e-mail" />
                <flux:button variant="primary">Zapisz się</flux:button>
                <flux:button wire:click="$set('showModal', false)">Nie dzisiaj</flux:button>
            </flux:input.group>
        </div>
    </div>
</flux:modal>
