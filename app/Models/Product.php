<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends EnovaModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Towary';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('hasGroup', function (Builder $builder) {
            $builder->whereHas('group');
        });
    }

    /**
     * Get the feature representing the product group.
     */
    public function group()
    {
        return $this->hasOne(Group::class, 'Parent', 'ID');
    }

    /**
     * Get the feature representing the product name.
     */
    public function productNameFeature()
    {
        return $this->hasOne(Feature::class, 'Parent', 'ID')
            ->where('Name', config('enova.features.product_name'))
            ->select(['Parent', 'Data as Name']);
    }

    /**
     * Scope a query to only include products of a given group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $cleanGroupName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereGroupIs($query, $cleanGroupName)
    {
        $prefix = config('enova.features.product_group_prefix');
        $fullGroupName = $prefix . $cleanGroupName . '\\';

        return $query->whereHas('group', function ($groupQuery) use ($fullGroupName) {
            $groupQuery->where('Data', $fullGroupName);
        });
    }
}
