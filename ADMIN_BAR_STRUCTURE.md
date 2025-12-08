# Struktura Paska Admina - Dokumentacja do odtworzenia

## Lokalizacja
`resources/views/layouts/app.blade.php` - linie 58-115

## Struktura HTML

```blade
@auth
    <!-- Pływająca belka administracyjna po lewej stronie -->
    <div class="fixed left-0 top-1/2 -translate-y-1/2 z-[10000]">
        <div class="bg-black text-white rounded-r-lg shadow-lg overflow-hidden">
            <div class="flex flex-col gap-0">
                @stack('admin-bar-actions')
                
                <flux:tooltip content="Promocje" position="right">
                    <a href="{{ route('promotions') }}"
                        class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                    </a>
                </flux:tooltip>
                
                @if (Auth::user()->hasRole('admin'))
                    <flux:modal.trigger name="users-modal">
                        <flux:tooltip content="Użytkownicy" position="right">
                            <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </button>
                        </flux:tooltip>
                    </flux:modal.trigger>
                    
                    <flux:tooltip content="Ustawienia" position="right">
                        <a href="{{ route('settings.profile') }}"
                            class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                    </flux:tooltip>
                @endif
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:tooltip content="Wyloguj się" position="right">
                        <button type="submit"
                            class="p-2 hover:bg-gray-800 transition-colors block w-full cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </flux:tooltip>
                </form>
            </div>
        </div>
    </div>
@endauth
```

## Modal Użytkowników
Lokalizacja: `resources/views/layouts/app.blade.php` - linie 253-273

```blade
@if (Auth::check() && Auth::user()->hasRole('admin'))
    <flux:modal name="users-modal" flyout position="left" class="md:w-[800px] m-0! rounded-none! h-screen!">
        <form class="flex flex-col h-full">
            <div class="shrink-0 p-6 border-b">
                <flux:heading size="lg">Użytkownicy</flux:heading>
                <flux:subheading>Lista wszystkich użytkowników systemu</flux:subheading>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <livewire:components.users-list />
            </div>

            <div class="shrink-0 flex justify-end space-x-2 rtl:space-x-reverse p-6 border-t bg-white sticky bottom-0">
                <flux:modal.close>
                    <flux:button variant="primary">Zamknij</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>
@endif
```

## Akcje dodawane przez komponenty (@push('admin-bar-actions'))

### 1. Content Editor (edycja treści/SEO)
Lokalizacja: `resources/views/livewire/components/content-editor.blade.php` - linie 84-95

```blade
@push('admin-bar-actions')
    <flux:modal.trigger name="content-editor-modal-{{ $identifier }}">
        <flux:tooltip content="{{ $tooltipText }}" position="right">
            <button type="button" class="p-2 hover:bg-gray-800 transition-colors block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
        </flux:tooltip>
    </flux:modal.trigger>
@endpush
```

### 2. Cache Grupy (tylko admin)
Lokalizacja: `resources/views/livewire/pages/group.blade.php` - linie 110-121

```blade
@if (Auth::user()->hasRole('admin'))
    @push('admin-bar-actions')
        <flux:modal.trigger name="confirm-clear-group-cache">
            <flux:tooltip content="Odśwież cache grupy" position="right">
                <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </flux:tooltip>
        </flux:modal.trigger>
    @endpush
@endif
```

### 3. Cache Produktu (tylko admin)
Lokalizacja: `resources/views/livewire/pages/product.blade.php` - linie 201-212

```blade
@if (Auth::check() && Auth::user()->hasRole('admin') && $product)
    @push('admin-bar-actions')
        <flux:modal.trigger name="confirm-clear-product-cache">
            <flux:tooltip content="Odśwież cache produktu" position="right">
                <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </flux:tooltip>
        </flux:modal.trigger>
    @endpush
@endif
```

## Założenia i reguły

### Role i uprawnienia
- **Admin**: Pełny dostęp (cache, ustawienia, użytkownicy, edycja treści)
- **Editor**: Tylko edycja treści/SEO (bez cache, ustawień, użytkowników)

### Dostępność akcji
- **Promocje**: Wszyscy zalogowani użytkownicy
- **Użytkownicy**: Tylko admin (`Auth::user()->hasRole('admin')`)
- **Ustawienia**: Tylko admin (`Auth::user()->hasRole('admin')`)
- **Wyloguj się**: Wszyscy zalogowani użytkownicy
- **Cache clearing**: Tylko admin
- **Edycja treści/SEO**: Admin i editor (`Auth::user()->hasRole('admin') || Auth::user()->hasRole('editor')`)

### Style
- Wszystkie przyciski: `p-2 hover:bg-gray-800 transition-colors block cursor-pointer`
- Ikony: `w-6 h-6`
- Tooltip: `position="right"`

## Komponenty

### Users List
Lokalizacja: `resources/views/livewire/components/users-list.blade.php`

Komponent Volt (bez osobnego pliku PHP), który:
- Wymaga roli `admin`
- Wyświetla listę wszystkich użytkowników z rolami
- Pokazuje: imię, email, role (admin/editor), datę rejestracji
- Używa `User::with('roles')->orderBy('name')->get()`

## Zależności
- Spatie Laravel Permission (role: admin, editor)
- Flux UI (modals, tooltips, buttons)
- Livewire (komponenty)
- User model z metodą `initials()` (zwraca inicjały użytkownika)

