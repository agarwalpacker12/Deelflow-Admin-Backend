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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('campaign_type', 50);
            $table->string('channel', 50);
            
            // Targeting
            $table->jsonb('target_criteria')->default('{}');
            $table->jsonb('geofence_center')->nullable();
            $table->integer('geofence_radius')->nullable(); // meters
            
            // Content
            $table->string('subject_line', 500)->nullable();
            $table->string('preview_text', 500)->nullable();
            $table->text('email_content')->nullable();
            $table->text('sms_content')->nullable();
            $table->text('voice_script')->nullable();
            $table->bigInteger('landing_page_id')->nullable();
            
            // AI settings
            $table->boolean('use_ai_personalization')->default(true);
            $table->string('ai_tone', 50)->default('professional');
            
            // Schedule
            $table->string('status', 50)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Performance
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->integer('response_count')->default(0);
            $table->integer('conversion_count')->default(0);
            
            // Budget
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('spent', 10, 2)->default(0);
            
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
