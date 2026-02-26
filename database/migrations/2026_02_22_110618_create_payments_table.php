<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade')->index();
            $table->foreignId('membership_id')->nullable()->constrained()->onDelete('set null')->index();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('PENDING')->index(); // PENDING, PAID, FAILED, REFUNDED
            $table->dateTime('paid_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
