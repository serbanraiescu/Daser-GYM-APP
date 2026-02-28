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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('meta_description')->nullable();
            
            // AI / SEO Optimization Fields
            $table->string('schema_type')->default('WebPage');
            $table->json('faq_data')->nullable(); // For injecting FAQ QA pairs for AI
            
            // Visibility Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_footer')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
