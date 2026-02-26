<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyProgress extends Model
{
    protected $table = 'loyalty_progress';

    protected $fillable = [
        'member_id',
        'loyalty_program_id',
        'current_count',
        'cycle_started_at',
        'last_payment_at',
        'reset_at',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'cycle_started_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'reset_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }
}
