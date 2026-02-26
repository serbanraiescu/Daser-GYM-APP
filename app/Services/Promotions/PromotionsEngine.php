<?php

namespace App\Services\Promotions;

use App\DTOs\PromotionResult;
use App\DTOs\TransactionRequest;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionsEngine
{
    public function calculate(TransactionRequest $request): PromotionResult
    {
        $member = $request->member ?? Member::findOrFail($request->member_id);
        $plan = $request->plan ?? Plan::findOrFail($request->plan_id);
        $basePrice = $plan->price * $request->quantity;

        $applied = collect();
        $explanation = [];
        $totalDiscount = 0;

        $promotions = Promotion::where('active', true)
            ->where(function ($q) use ($request) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $request->getDate());
            })
            ->where(function ($q) use ($request) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $request->getDate());
            })
            ->orderBy('priority', 'DESC')
            ->get();

        foreach ($promotions as $promo) {
            $check = $this->canApply($promo, $request, $member, $applied);

            if ($check['can']) {
                $discount = $this->calculateDiscount($promo, $basePrice - $totalDiscount, $request->quantity, $plan);
                
                $applied->push($promo);
                $totalDiscount += $discount;
                $explanation[] = "Applied [{$promo->name}]: Discounted {$discount}";

                // If exclusive, stop here
                if ($promo->stacking_mode === 'EXCLUSIVE') {
                    $explanation[] = "Stopping further calculation: [{$promo->name}] is EXCLUSIVE.";
                    break;
                }
            } else {
                $explanation[] = "Rejected [{$promo->name}]: {$check['reason']}";
            }
        }

        return new PromotionResult(
            applied_promotions: $applied,
            total_discount: $totalDiscount,
            explanation_log: $explanation
        );
    }

    protected function canApply(Promotion $promo, TransactionRequest $request, Member $member, Collection $applied): array
    {
        // 1. Stacking rules check
        if ($applied->contains(fn($p) => $p->stacking_mode === 'EXCLUSIVE')) {
            return ['can' => false, 'reason' => 'Already applied an EXCLUSIVE promotion.'];
        }

        if ($promo->stacking_mode === 'EXCLUSIVE' && $applied->isNotEmpty()) {
            return ['can' => false, 'reason' => 'Promotion is EXCLUSIVE and other promos already applied.'];
        }

        if ($promo->stacking_mode === 'EXCLUSIVE_GROUP' && $applied->contains('exclusive_group', $promo->exclusive_group)) {
            return ['can' => false, 'reason' => "Exclusive group [{$promo->exclusive_group}] already used."];
        }

        // 2. Incompatibility check
        $incompatibleIds = $promo->incompatibilities->pluck('incompatible_promotion_id');
        if ($applied->whereIn('id', $incompatibleIds)->isNotEmpty()) {
            return ['can' => false, 'reason' => 'Incompatible with an already applied promotion.'];
        }

        // 3. Conditions check
        foreach ($promo->conditions as $condition) {
            if (!$this->checkCondition($condition, $request, $member)) {
                return ['can' => false, 'reason' => "Condition [{$condition->field} {$condition->operator}] not met."];
            }
        }

        return ['can' => true, 'reason' => ''];
    }

    protected function checkCondition($condition, TransactionRequest $request, Member $member): bool
    {
        $targetValue = match ($condition->field) {
            'MEMBER_CATEGORY' => $member->category,
            'PLAN_ID' => $request->plan_id,
            'MIN_QTY' => $request->quantity,
            default => null
        };

        return match ($condition->operator) {
            'EQUALS' => $targetValue == $condition->value,
            'IN' => in_array($targetValue, (array) $condition->value),
            'GREATER_THAN' => $targetValue > $condition->value,
            default => false
        };
    }

    protected function calculateDiscount(Promotion $promo, float $currentPrice, int $quantity, Plan $plan): float
    {
        return match ($promo->type) {
            'PERCENT' => round($currentPrice * ($promo->value / 100), 2),
            'FIXED' => min($currentPrice, (float) $promo->value),
            'BUNDLE' => $this->calculateBundle($promo, $plan, $quantity),
            default => 0
        };
    }

    protected function calculateBundle(Promotion $promo, Plan $plan, int $quantity): float
    {
        // Logic: Buy X Get Y. (e.g. 5+1 free means pay for 5, get 6).
        // Total price for quantity Q should be: floor(Q / (X+Y)) * (X * price) + (remainder * price)
        // Discount = Base Price - Bundle Price
        if (!$promo->bundle_x || !$promo->bundle_y) return 0;
        
        $bundleSet = $promo->bundle_x + $promo->bundle_y;
        $numBundles = floor($quantity / $bundleSet);
        $remainder = $quantity % $bundleSet;

        $bundledPrice = ($numBundles * $promo->bundle_x * $plan->price) + ($remainder * $plan->price);
        $basePrice = $quantity * $plan->price;

        return max(0, $basePrice - $bundledPrice);
    }
}
