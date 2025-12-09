<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $userId;

    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $role;

    public $showSuccessMessage;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadUserData()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('loadUserData'))->execute(...$arguments);
    }

    public function save()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('save'))->execute(...$arguments);
    }

};