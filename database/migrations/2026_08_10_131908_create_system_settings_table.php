<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the system_settings table for storing
     * global configuration values for the ICT-PMS application.
     * Module: System Settings
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Setting key-value pairs
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('category', 100)->nullable(); // e.g., 'email', 'notifications', 'scheduling', etc.
            $table->text('description')->nullable();
            
            // Setting type for proper value casting
            $table->enum('data_type', ['string', 'integer', 'boolean', 'json', 'decimal'])->default('string');
            
            // Audit trail
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_system')->default(false); // true = cannot be modified from UI
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('category');
            $table->index('setting_key');
            $table->index('is_system');
            $table->index('is_active');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
