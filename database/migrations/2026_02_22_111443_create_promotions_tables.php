<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // PERCENT, FIXED, BUNDLE
            $table->decimal('value', 10, 2)->nullable();
            $table->integer('bundle_x')->nullable();
            $table->integer('bundle_y')->nullable();
            $table->integer('priority')->default(0);
            $table->string('stacking_mode')->default('STACKABLE'); // STACKABLE, EXCLUSIVE, EXCLUSIVE_GROUP
            $table->string('exclusive_group')->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promotion_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->string('field'); // MEMBER_CATEGORY, PLAN_ID, MIN_QTY
            $table->string('operator'); // EQUALS, IN, GREATER_THAN
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('promotion_incompatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('incompatible_promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_incompatibilities');
        Schema::dropIfExists('promotion_conditions');
        Schema::dropIfExists('promotions');
    }
};
