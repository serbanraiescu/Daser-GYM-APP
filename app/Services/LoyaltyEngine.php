<?php

namespace App\Services;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyProgress;
use App\Models\Payment;
use App\Models\Membership;
use Carbon\Carbon;

class LoyaltyEngine
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Process a payment to update loyalty progress.
     */
    public function processPayment(Payment $payment): void
    {
        if ($payment->status !== 'PAID') return;

        $member = $payment->member;
        $membership = $payment->membership;
        
        if (!$membership) return;

        $programs = LoyaltyProgram::where('active', true)->get();

        foreach ($programs as $program) {
            // 1. Eligibility check (Plan ID)
            if ($program->eligible_plan_ids && !in_array($membership->plan_id, $program->eligible_plan_ids)) {
                continue;
            }

            $progress = LoyaltyProgress::firstOrCreate(
                ['member_id' => $member->id, 'loyalty_program_id' => $program->id],
                ['cycle_started_at' => now(), 'current_count' => 0]
            );

            // 2. Grace reset check
            if ($this->shouldResetProgress($progress, $program, $membership)) {
                $progress->update([
                    'current_count' => 0,
                    'reset_at' => now(),
                    'cycle_started_at' => now(),
                ]);
            }

            // 3. Increment count
            $progress->increment('current_count');
            $progress->update(['last_payment_at' => now()]);

            // 4. Check for reward
            if ($progress->current_count >= $program->buy_x) {
                $this->applyReward($progress, $program, $member);
                $progress->update(['current_count' => 0]);
            }
        }
    }

    protected function shouldResetProgress(LoyaltyProgress $progress, LoyaltyProgram $program, Membership $membership): bool
    {
        if (!$progress->last_payment_at) return false;

        // If membership is expired and grace period passed since last payment or expiry
        if ($membership->status === 'EXPIRED') {
            $graceDays = $program->grace_days_override 
                ?? $this->settings->get('loyalty_grace_days', 0);
            
            $expiryWithGrace = $membership->expires_at->copy()->addDays($graceDays);
            
            return now() > $expiryWithGrace;
        }

        return false;
    }

    protected function applyReward(LoyaltyProgress $progress, LoyaltyProgram $program, $member): void
    {
        // For Milestone D: Logic to "mark next renewal free"
        // We can store this in a 'credits' table or just log it in 'notes' for now.
        // As per spec: "mark next renewal free or create credit"
        
        if ($program->reward_type === 'FREE_RENEWAL') {
            // Implementation detail: create a $0.00 'credit' payment record or a flag
            $member->notes .= "\n[Loyalty Reward]: Earned 1 FREE RENEWAL on " . now()->toDateTimeString();
            $member->save();
        }
    }
}
