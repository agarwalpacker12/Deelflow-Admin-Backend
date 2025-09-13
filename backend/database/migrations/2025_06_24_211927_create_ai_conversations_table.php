<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            
            // Conversation details
            $table->string('channel', 50);
            $table->string('external_id')->nullable();
            
            // Messages stored as JSONB array
            $table->jsonb('messages')->default('[]');
            
            // AI Analysis
            $table->integer('sentiment_score')->nullable();
            $table->integer('urgency_score')->nullable();
            $table->integer('motivation_score')->nullable();
            $table->integer('qualification_score')->nullable();
            
            // Extracted data
            $table->jsonb('extracted_data')->default('{}');
            $table->jsonb('identified_pain_points')->default('[]');
            $table->jsonb('detected_keywords')->default('[]');
            
            // Status
            $table->string('status', 50)->default('active');
            $table->boolean('transferred_to_human')->default(false);
            $table->string('transfer_reason')->nullable();
            
            // Outcomes
            $table->string('outcome', 100)->nullable();
            $table->text('next_steps')->nullable();
            
            $table->timestamps();

            $table->index('channel');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
