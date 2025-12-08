<?php

use function Livewire\Volt\{state, mount};
use App\Models\User;
use Illuminate\Support\Facades\Auth;

state(['users' => []]);

mount(function () {
    // Sprawdź czy użytkownik jest adminem
    if (!Auth::check() || !Auth::user()->hasRole('admin')) {
        abort(403, 'Brak dostępu');
    }

    // Pobierz wszystkich użytkowników z ich rolami
    $this->users = User::with('roles')->orderBy('name')->get();
});

$refreshUsers = function () {
    // Pobierz wszystkich użytkowników z ich rolami
    $this->users = User::with('roles')->orderBy('name')->get();
};

?>

<div x-data @refresh-users.window="$wire.call('refreshUsers')" class="space-y-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Lista użytkowników</h2>
        <flux:button variant="primary" type="button" wire:off x-data=""
            @click.stop="
                $dispatch('edit-user', { id: null });
                $flux.modal('users-modal').close();
                setTimeout(() => {
                    $flux.modal('user-form-modal').show();
                }, 300);
            ">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Dodaj użytkownika
        </flux:button>
    </div>

    @if ($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Użytkownik
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rola
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data rejestracji
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-medium">
                                            {{ $user->initials() }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($user->roles as $role)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $role->name === 'admin' ? 'Administrator' : 'Edytor' }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500">Brak roli</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <flux:button size="sm" type="button" wire:off x-data=""
                                    @click.stop="
                                        $dispatch('edit-user', { id: {{ $user->id }} });
                                        $flux.modal('users-modal').close();
                                        setTimeout(() => {
                                            $flux.modal('user-form-modal').show();
                                        }, 300);
                                    ">
                                    Edytuj
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z">
                </path>
            </svg>
            <h3 class="text-xl font-medium mb-2">Brak użytkowników</h3>
            <p class="text-gray-500">Nie znaleziono żadnych użytkowników w systemie.</p>
        </div>
    @endif
</div>
