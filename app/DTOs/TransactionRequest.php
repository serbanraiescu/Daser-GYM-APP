<?php

namespace App\DTOs;

use App\Models\Member;
use App\Models\Plan;
use Carbon\Carbon;

readonly class TransactionRequest
{
    public function __construct(
        public int $member_id,
        public int $plan_id,
        public int $quantity = 1,
        public ?Carbon $date = null,
        
        // Optional pre-loaded models to avoid DB hits
        public ?Member $member = null,
        public ?Plan $plan = null
    ) {}

    public function getDate(): Carbon
    {
        return $this->date ?? now();
    }
}
