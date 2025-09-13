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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Address information
            $table->string('address', 500);
            $table->string('unit', 50)->nullable();
            $table->string('city', 100);
            $table->string('state', 2);
            $table->string('zip', 10);
            $table->string('county', 100)->nullable();
            $table->jsonb('location')->nullable(); // Using JSONB for location for now
            
            // Property details
            $table->string('property_type', 50)->default('single_family');
            $table->integer('bedrooms')->nullable();
            $table->decimal('bathrooms', 3, 1)->nullable();
            $table->integer('square_feet')->nullable();
            $table->decimal('lot_size', 10, 2)->nullable();
            $table->integer('year_built')->nullable();
            $table->integer('stories')->nullable();
            $table->integer('garage_spaces')->nullable();
            
            // Financial information
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('arv', 12, 2);
            $table->decimal('repair_estimate', 12, 2)->default(0);
            $table->decimal('holding_costs', 12, 2)->default(0);
            
            // AI Analysis
            $table->integer('ai_score')->default(0);
            $table->jsonb('ai_analysis')->default('{}');
            $table->jsonb('market_analysis')->default('{}');
            $table->jsonb('repair_analysis')->default('{}');
            
            // Transaction details
            $table->string('transaction_type', 50);
            $table->decimal('escrow_amount', 12, 2)->nullable();
            $table->decimal('assignment_fee', 12, 2)->nullable();
            
            // Status and dates
            $table->string('status', 50)->default('draft');
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            
            // Blockchain
            $table->string('blockchain_hash')->nullable();
            $table->string('smart_contract_address')->nullable();
            
            // Additional data
            $table->text('description')->nullable();
            $table->text('seller_notes')->nullable();
            $table->jsonb('images')->default('[]');
            $table->jsonb('documents')->default('[]');
            $table->jsonb('seller_info')->default('{}');
            $table->jsonb('property_condition')->default('{}');
            $table->jsonb('neighborhood_data')->default('{}');
            
            // Metrics
            $table->integer('view_count')->default(0);
            $table->integer('save_count')->default(0);
            $table->integer('inquiry_count')->default(0);
            
            $table->timestamps();

            $table->index('status');
            $table->index('purchase_price');
            $table->index('ai_score');
            $table->index(['city', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
