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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role_in_project', ['Developer', 'Team Lead'])->default('Developer');
            $table->date('assigned_date');
            $table->date('removed_date')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['project_id', 'user_id']);
            
            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index(['assigned_date', 'removed_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
