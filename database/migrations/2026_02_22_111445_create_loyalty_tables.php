<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('buy_x');
            $table->integer('get_y');
            $table->json('eligible_plan_ids')->nullable();
            $table->integer('grace_days_override')->nullable();
            $table->string('reward_type')->default('FREE_RENEWAL');
            $table->boolean('exclusive_with_promotions')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_program_id')->constrained()->onDelete('cascade');
            $table->integer('current_count')->default(0);
            $table->dateTime('cycle_started_at');
            $table->dateTime('last_payment_at')->nullable();
            $table->dateTime('reset_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_progress');
        Schema::dropIfExists('loyalty_programs');
    }
};
