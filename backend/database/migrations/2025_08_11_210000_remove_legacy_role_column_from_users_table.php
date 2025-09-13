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
            // Remove the index on role column first
            $table->dropIndex('users_role_index');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Remove the legacy role column
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the legacy role column
            $table->string('role', 50)->default('admin')->after('company_name');
            
            // Add back the index
            $table->index('role');
        });
    }
};
