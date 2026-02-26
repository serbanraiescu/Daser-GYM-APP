<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'grace_days_override',
        'features',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'duration_days' => 'integer',
        'grace_days_override' => 'integer',
        'features' => 'array',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
