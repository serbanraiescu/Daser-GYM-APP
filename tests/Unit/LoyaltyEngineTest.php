<?php

namespace Tests\Unit;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyProgress;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\LoyaltyEngine;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyEngineTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new LoyaltyEngine(new SettingsService());
    }

    public function test_punchcard_reaches_reward()
    {
        $plan = Plan::create(['name' => 'Plan', 'price' => 100, 'duration_days' => 30]);
        $member = Member::create(['first_name' => 'Loyal', 'last_name' => 'Member', 'status' => 'active', 'notes' => '']);
        $membership = Membership::create(['member_id' => $member->id, 'plan_id' => $plan->id, 'starts_at' => now(), 'expires_at' => now()->addDays(30), 'status' => 'ACTIVE']);
        
        $program = LoyaltyProgram::create([
            'name' => '5+1',
            'buy_x' => 5,
            'get_y' => 1,
            'reward_type' => 'FREE_RENEWAL',
            'active' => true
        ]);

        // Process 4 payments
        for ($i = 0; $i < 4; $i++) {
            $payment = Payment::create(['member_id' => $member->id, 'membership_id' => $membership->id, 'amount' => 100, 'status' => 'PAID']);
            $this->engine->processPayment($payment);
        }

        $progress = LoyaltyProgress::where('member_id', $member->id)->first();
        $this->assertEquals(4, $progress->current_count);

        // 5th payment triggers reward and reset
        $payment = Payment::create(['member_id' => $member->id, 'membership_id' => $membership->id, 'amount' => 100, 'status' => 'PAID']);
        $this->engine->processPayment($payment);

        $progress->refresh();
        $this->assertEquals(0, $progress->current_count);
        $this->assertStringContainsString('[Loyalty Reward]', $member->refresh()->notes);
    }

    public function test_grace_reset_scenario()
    {
        $plan = Plan::create(['name' => 'Plan', 'price' => 100, 'duration_days' => 30]);
        $member = Member::create(['first_name' => 'Lapsed', 'last_name' => 'Member', 'status' => 'expired']);
        
        // Membership expired long ago
        $membership = Membership::create([
            'member_id' => $member->id, 
            'plan_id' => $plan->id, 
            'starts_at' => now()->subDays(60), 
            'expires_at' => now()->subDays(30), 
            'status' => 'EXPIRED'
        ]);

        $program = LoyaltyProgram::create([
            'name' => '5+1',
            'buy_x' => 5,
            'get_y' => 1,
            'reward_type' => 'FREE_RENEWAL',
            'active' => true
        ]);

        // Existing progress from long ago
        $progress = LoyaltyProgress::create([
            'member_id' => $member->id,
            'loyalty_program_id' => $program->id,
            'current_count' => 3,
            'cycle_started_at' => now()->subDays(70),
            'last_payment_at' => now()->subDays(60)
        ]);

        // New payment after long gap (beyond default 7 days grace)
        $payment = Payment::create(['member_id' => $member->id, 'membership_id' => $membership->id, 'amount' => 100, 'status' => 'PAID']);
        $this->engine->processPayment($payment);

        $progress->refresh();
        // Should have reset to 0 then incremented to 1
        $this->assertEquals(1, $progress->current_count);
    }
}
