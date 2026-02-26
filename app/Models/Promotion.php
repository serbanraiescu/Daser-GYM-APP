<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'bundle_x',
        'bundle_y',
        'priority',
        'stacking_mode',
        'exclusive_group',
        'active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'bundle_x' => 'integer',
        'bundle_y' => 'integer',
        'priority' => 'integer',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(PromotionCondition::class);
    }

    public function incompatibilities(): HasMany
    {
        return $this->hasMany(PromotionIncompatibility::class);
    }
}
