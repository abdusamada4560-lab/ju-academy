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
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('work_package_id')->nullable()->constrained('work_packages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action_type', 100);
            $table->string('old_value', 500)->nullable();
            $table->string('new_value', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('project_id');
            $table->index('work_package_id');
            $table->index('user_id');
            $table->index('action_type');
            $table->index('created_at');
            $table->index(['project_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
