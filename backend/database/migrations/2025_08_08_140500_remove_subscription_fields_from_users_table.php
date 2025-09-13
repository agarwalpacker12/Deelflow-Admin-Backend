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
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_subscription_tier_subscription_status_index');
            $table->dropColumn(['subscription_tier', 'subscription_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_tier', 50)->default('starter');
            $table->string('subscription_status', 50)->default('active');
            $table->index(['subscription_tier', 'subscription_status']);
        });
    }
};
