<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionIncompatibility extends Model
{
    protected $fillable = [
        'promotion_id',
        'incompatible_promotion_id',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function incompatiblePromotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'incompatible_promotion_id');
    }
}
