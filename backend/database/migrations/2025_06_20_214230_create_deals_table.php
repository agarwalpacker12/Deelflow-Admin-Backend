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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            
            // Parties involved
            $table->foreignId('wholesaler_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('funder_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Deal type and details
            $table->string('deal_type', 50);
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('assignment_fee', 12, 2)->nullable();
            
            // Funding details
            $table->decimal('funding_amount', 12, 2)->nullable();
            $table->decimal('funding_fee', 12, 2)->nullable();
            $table->integer('funding_duration')->nullable(); // days
            
            // Contract details
            $table->date('contract_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->integer('inspection_period')->default(10); // days
            $table->decimal('earnest_money', 12, 2)->nullable();
            
            // Status tracking
            $table->string('status', 50)->default('draft');
            $table->string('substatus', 100)->nullable();
            
            // Blockchain
            $table->string('blockchain_contract_address')->nullable();
            $table->string('escrow_transaction_hash')->nullable();
            
            // Documents and terms
            $table->jsonb('contract_terms')->default('{}');
            $table->jsonb('documents')->default('[]');
            $table->jsonb('contingencies')->default('[]');
            
            // Important dates
            $table->timestamp('contract_signed_at')->nullable();
            $table->timestamp('escrow_deposited_at')->nullable();
            $table->timestamp('funding_received_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Notes
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();

            $table->index('status');
            $table->index('deal_type');
            $table->index('closing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
