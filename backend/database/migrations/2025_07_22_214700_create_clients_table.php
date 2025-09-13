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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Wholesaler who owns this client
            $table->string('client_type', 50); // 'seller' or 'buyer'
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('alternate_phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            
            // Address Information
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('country', 100)->default('USA');
            
            // Professional Information
            $table->string('occupation', 200)->nullable();
            $table->string('employer', 200)->nullable();
            $table->decimal('annual_income', 12, 2)->nullable();
            
            // Financial Information
            $table->decimal('net_worth', 12, 2)->nullable();
            $table->decimal('liquid_assets', 12, 2)->nullable();
            $table->integer('credit_score')->nullable();
            $table->boolean('has_financing_preapproval')->default(false);
            $table->decimal('financing_amount', 12, 2)->nullable();
            
            // Investment Profile (for buyers)
            $table->jsonb('investment_criteria')->default('{}'); // property types, locations, price ranges
            $table->jsonb('investment_goals')->default('{}'); // flip, rental, wholesale, etc.
            $table->string('investment_experience', 50)->nullable(); // beginner, intermediate, expert
            
            // Property Ownership (for sellers)
            $table->jsonb('owned_properties')->default('{}'); // list of properties they own
            $table->string('selling_motivation', 200)->nullable();
            $table->string('selling_timeline', 100)->nullable();
            
            // Communication Preferences
            $table->string('preferred_contact_method', 50)->default('phone');
            $table->string('best_time_to_call', 100)->nullable();
            $table->jsonb('communication_notes')->default('{}');
            
            // Relationship Management
            $table->string('status', 50)->default('prospect'); // prospect, active, closed, inactive
            $table->string('source', 100)->nullable(); // how they were acquired
            $table->text('notes')->nullable();
            $table->integer('relationship_score')->default(0); // 0-100
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('next_followup_at')->nullable();
            
            // Metadata
            $table->jsonb('custom_fields')->default('{}');
            $table->jsonb('tags')->default('[]');
            $table->timestamps();

            // Indexes
            $table->index('client_type');
            $table->index('status');
            $table->index(['user_id', 'client_type']);
            $table->index('last_contact_at');
            $table->index('next_followup_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
