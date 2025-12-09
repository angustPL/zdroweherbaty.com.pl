<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $promotionId;

    public $name;

    public $code;

    public $description;

    public $type;

    public $discount_type;

    public $discount_value;

    public $max_discount_amount;

    public $min_order_amount;

    public $valid_from;

    public $valid_to;

    public $restriction_type;

    public $selected_groups;

    public $selected_products;

    public $available_groups;

    public $available_products;

    public $is_active;

    public $can_combine_with_others;

    public $always_applicable;

    public $priority;

    public $showSuccessMessage;

    public function mount($promotionId = NULL)
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function addGroup($groupPath, $groupName)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('addGroup'))->execute(...$arguments);
    }

    public function removeGroup($groupPath)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('removeGroup'))->execute(...$arguments);
    }

    public function addProduct($productId, $productName)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('addProduct'))->execute(...$arguments);
    }

    public function removeProduct($productId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('removeProduct'))->execute(...$arguments);
    }

    public function updatedRestrictionType()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updatedRestrictionType'))->execute(...$arguments);
    }

    public function flattenGroups($groups)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('flattenGroups'))->execute(...$arguments);
    }

    public function loadPromotionData()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('loadPromotionData'))->execute(...$arguments);
    }

    public function save()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('save'))->execute(...$arguments);
    }

};