<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use Carbon\Carbon;

class MembershipService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Renew or create a membership for a member with a specific plan.
     */
    public function renewMembership(Member $member, Plan $plan): Membership
    {
        // Check for active membership to handle overlap
        $currentActive = $member->memberships()
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->first();

        $startsAt = $currentActive ? $currentActive->expires_at : now();
        $expiresAt = $this->calculateExpiry($plan, $startsAt);

        $membership = Membership::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => 'ACTIVE',
        ]);

        $member->update(['status' => 'active']);

        return $membership;
    }

    /**
     * Calculate expiry date based on plan duration.
     */
    public function calculateExpiry(Plan $plan, ?Carbon $startDate = null): Carbon
    {
        $start = $startDate ? $startDate->copy() : now();
        return $start->addDays($plan->duration_days);
    }

    /**
     * Update member and membership status based on current date and grace period.
     */
    public function updateStatusWithGrace(Member $member): void
    {
        $latest = $member->memberships()->latest('expires_at')->first();

        if (!$latest) {
            $member->update(['status' => 'inactive']);
            return;
        }

        if ($latest->expires_at > now()) {
            $latest->update(['status' => 'ACTIVE']);
            $member->update(['status' => 'active']);
            return;
        }

        // Check grace period
        $graceDays = $latest->plan->grace_days_override 
            ?? $this->settings->get('grace_days_default', 0);
        
        $graceExpiry = $latest->expires_at->copy()->addDays($graceDays);

        if (now() <= $graceExpiry) {
            $latest->update(['status' => 'IN_GRACE']);
            $member->update(['status' => 'active']); // Still treated as active for access
        } else {
            $latest->update(['status' => 'EXPIRED']);
            $member->update(['status' => 'expired']);
        }
    }
}
