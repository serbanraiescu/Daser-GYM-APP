<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    protected $fillable = [
        'name',
        'buy_x',
        'get_y',
        'eligible_plan_ids',
        'grace_days_override',
        'reward_type',
        'exclusive_with_promotions',
        'active',
    ];

    protected $casts = [
        'eligible_plan_ids' => 'array',
        'exclusive_with_promotions' => 'boolean',
        'active' => 'boolean',
        'buy_x' => 'integer',
        'get_y' => 'integer',
        'grace_days_override' => 'integer',
    ];

    public function progress(): HasMany
    {
        return $this->hasMany(LoyaltyProgress::class);
    }
}
