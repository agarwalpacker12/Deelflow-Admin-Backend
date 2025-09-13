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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Contact information
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('alternate_phone', 20)->nullable();
            
            // Property information
            $table->string('property_address', 500)->nullable();
            $table->string('property_city', 100)->nullable();
            $table->string('property_state', 2)->nullable();
            $table->string('property_zip', 10)->nullable();
            $table->string('property_type', 50)->nullable();
            
            // Lead scoring
            $table->integer('ai_score')->default(0);
            $table->integer('motivation_score')->default(0);
            $table->integer('urgency_score')->default(0);
            $table->integer('financial_score')->default(0);
            
            // Lead details
            $table->string('source', 100)->nullable();
            $table->jsonb('source_details')->default('{}');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->decimal('mortgage_balance', 12, 2)->nullable();
            $table->decimal('asking_price', 12, 2)->nullable();
            
            // Status
            $table->string('status', 50)->default('new');
            $table->string('disposition', 100)->nullable();
            
            // Communication preferences
            $table->string('preferred_contact_method', 50)->nullable();
            $table->string('best_time_to_call', 100)->nullable();
            
            // AI insights
            $table->jsonb('ai_insights')->default('{}');
            $table->text('conversation_summary')->nullable();
            $table->string('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            
            // Timestamps
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('ai_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
