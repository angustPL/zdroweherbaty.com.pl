<?php

use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

state([
    'userId' => null,
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => 'editor',
    'showSuccessMessage' => false,
]);

mount(function () {
    // Sprawdź czy użytkownik jest adminem
    if (!Auth::check() || !Auth::user()->hasRole('admin')) {
        abort(403, 'Brak dostępu');
    }
});

$loadUserData = function () {
    if (!$this->userId) {
        // Resetuj formularz dla nowego użytkownika
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'editor';
        return;
    }

    $user = User::with('roles')->find($this->userId);
    if (!$user) {
        return;
    }

    $this->name = $user->name;
    $this->email = $user->email;
    $this->password = '';
    $this->password_confirmation = '';
    
    // Pobierz pierwszą rolę użytkownika
    $userRole = $user->roles->first();
    $this->role = $userRole ? $userRole->name : 'editor';
};

$save = function () {
    // Walidacja
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255',
        'role' => 'required|in:admin,editor',
    ];

    // Hasło jest wymagane tylko przy tworzeniu nowego użytkownika
    if (!$this->userId) {
        $rules['password'] = 'required|string|min:8|confirmed';
    } else {
        // Przy edycji hasło jest opcjonalne
        if ($this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
    }

    // Sprawdź unikalność emaila (z wyjątkiem aktualnie edytowanego użytkownika)
    $emailRule = 'unique:users,email';
    if ($this->userId) {
        $emailRule .= ',' . $this->userId;
    }
    $rules['email'] .= '|' . $emailRule;

    $this->validate($rules);

    // Przygotuj dane użytkownika
    $userData = [
        'name' => $this->name,
        'email' => $this->email,
    ];

    // Dodaj hasło tylko jeśli zostało podane
    if ($this->password) {
        $userData['password'] = Hash::make($this->password);
    }

    // Zapisz lub zaktualizuj użytkownika
    if ($this->userId) {
        $user = User::find($this->userId);
        if ($user) {
            $user->update($userData);
        }
    } else {
        $user = User::create($userData);
        $this->userId = $user->id;
    }

    // Przypisz rolę
    $role = Role::findByName($this->role);
    $user->syncRoles([$role]);

    // Wyświetl komunikat sukcesu
    $this->showSuccessMessage = true;
    
    // Odśwież listę użytkowników
    $this->dispatch('refresh-users');
};

?>

<div>
    {{-- Komunikat sukcesu --}}
    @if ($showSuccessMessage)
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg" x-data x-init="setTimeout(() => $wire.set('showSuccessMessage', false), 3000)">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">
                    {{ $userId ? 'Użytkownik został zaktualizowany.' : 'Użytkownik został utworzony.' }}
                </p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="save" id="user-form">
        <div class="space-y-6">
            {{-- Podstawowe informacje --}}
            <div class="border border-gray-200 rounded-lg bg-white">
                <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
                    Podstawowe informacje</flux:heading>
                <div class="p-4 pt-3 space-y-3">
                    <flux:field>
                        <flux:label>Imię i nazwisko</flux:label>
                        <flux:input type="text" wire:model="name" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="email" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rola</flux:label>
                        <flux:select wire:model="role">
                            <option value="editor">Edytor</option>
                            <option value="admin">Administrator</option>
                        </flux:select>
                        <flux:description class="!mt-1">Edytor może edytować treści, administrator ma pełny dostęp</flux:description>
                    </flux:field>
                </div>
            </div>

            {{-- Hasło --}}
            <div class="border border-gray-200 rounded-lg bg-white">
                <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
                    Hasło</flux:heading>
                <div class="p-4 pt-3 space-y-3">
                    @if ($userId)
                        <flux:description class="!mb-0">Zostaw puste, jeśli nie chcesz zmieniać hasła</flux:description>
                    @endif

                    <flux:field>
                        <flux:label>Hasło</flux:label>
                        <flux:input type="password" wire:model="password" :required="!$userId" />
                        <flux:description class="!mt-1">Minimum 8 znaków</flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>Potwierdź hasło</flux:label>
                        <flux:input type="password" wire:model="password_confirmation" :required="!$userId" />
                    </flux:field>
                </div>
            </div>

            {{-- Przyciski formularza --}}
            <div class="flex justify-end space-x-2 rtl:space-x-reverse mt-6 pt-4 border-t">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Anuluj</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Zapisz</flux:button>
            </div>
        </div>
    </form>
</div>

