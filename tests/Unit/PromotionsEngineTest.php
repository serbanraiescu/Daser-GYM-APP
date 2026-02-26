<?php

namespace Tests\Unit;

use App\DTOs\TransactionRequest;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use App\Services\Promotions\PromotionsEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionsEngineTest extends TestCase
{
    use RefreshDatabase;

    private PromotionsEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new PromotionsEngine();
    }

    public function test_exclusive_promotion_conflicts()
    {
        $plan = Plan::create(['name' => 'Plan', 'price' => 100, 'duration_days' => 30]);
        $member = Member::create(['first_name' => 'A', 'last_name' => 'B', 'status' => 'active']);

        Promotion::create([
            'name' => 'Exclusive 1',
            'type' => 'PERCENT',
            'value' => 50,
            'priority' => 100,
            'stacking_mode' => 'EXCLUSIVE',
            'active' => true
        ]);

        Promotion::create([
            'name' => 'Stackable 2',
            'type' => 'PERCENT',
            'value' => 10,
            'priority' => 50,
            'stacking_mode' => 'STACKABLE',
            'active' => true
        ]);

        $request = new TransactionRequest($member->id, $plan->id, 1, now(), $member, $plan);
        $result = $this->engine->calculate($request);

        $this->assertCount(1, $result->applied_promotions);
        $this->assertEquals(50, $result->total_discount);
        $this->assertContains('Stopping further calculation: [Exclusive 1] is EXCLUSIVE.', $result->explanation_log);
    }

    public function test_stacking_promotions()
    {
        $plan = Plan::create(['name' => 'Plan', 'price' => 100, 'duration_days' => 30]);
        $member = Member::create(['first_name' => 'A', 'last_name' => 'B', 'status' => 'active']);

        Promotion::create(['name' => 'P1', 'type' => 'FIXED', 'value' => 10, 'priority' => 10, 'stacking_mode' => 'STACKABLE', 'active' => true]);
        Promotion::create(['name' => 'P2', 'type' => 'FIXED', 'value' => 5, 'priority' => 5, 'stacking_mode' => 'STACKABLE', 'active' => true]);

        $request = new TransactionRequest($member->id, $plan->id, 1, now(), $member, $plan);
        $result = $this->engine->calculate($request);

        $this->assertCount(2, $result->applied_promotions);
        $this->assertEquals(15, $result->total_discount);
    }

    public function test_priority_order()
    {
        $plan = Plan::create(['name' => 'Plan', 'price' => 100, 'duration_days' => 30]);
        $member = Member::create(['first_name' => 'A', 'last_name' => 'B', 'status' => 'active']);

        Promotion::create(['name' => 'Low', 'type' => 'FIXED', 'value' => 5, 'priority' => 1, 'stacking_mode' => 'STACKABLE', 'active' => true]);
        Promotion::create(['name' => 'High', 'type' => 'FIXED', 'value' => 10, 'priority' => 100, 'stacking_mode' => 'STACKABLE', 'active' => true]);

        $request = new TransactionRequest($member->id, $plan->id, 1, now(), $member, $plan);
        $result = $this->engine->calculate($request);

        $this->assertEquals('High', $result->applied_promotions->first()->name);
    }
}
