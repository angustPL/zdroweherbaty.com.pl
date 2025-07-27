<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Group extends EnovaModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Features';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    public $incrementing = false;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        $prefix = config('enova.features.product_group_prefix');

        static::addGlobalScope('isGroup', function (Builder $builder) use ($prefix) {
            $builder->where('Name', config('enova.features.product_group'))
                    ->where('Data', 'like', $prefix . '%')
                    ->where('Data', '!=', $prefix);
        });
    }

    /**
     * Get the cleaned group name.
     *
     * @return string
     */
    public function getCleanNameAttribute(): string
    {
        $prefix = config('enova.features.product_group_prefix');
        // Remove prefix
        $name = Str::after($this->Data, $prefix);
        // Remove trailing slash
        return rtrim($name, '\\');
    }
}
