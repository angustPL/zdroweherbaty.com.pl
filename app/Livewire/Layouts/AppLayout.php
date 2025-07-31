<?php

namespace App\Livewire\Layouts;

use Livewire\Component;

class AppLayout extends Component
{
    protected $listeners = [
        'cart-updated' => 'refreshCartIcon'
    ];

    public function refreshCartIcon()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.layouts.app')->layout('layouts.app-base');
    }
}
