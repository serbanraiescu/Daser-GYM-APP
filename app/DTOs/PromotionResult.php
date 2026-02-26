<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

readonly class PromotionResult
{
    public function __construct(
        public Collection $applied_promotions,
        public float $total_discount,
        public array $explanation_log
    ) {}
}
