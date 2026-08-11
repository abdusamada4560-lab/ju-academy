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
        Schema::create('work_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('type', ['task', 'milestone', 'issue'])->default('task');
            $table->enum('status', ['To Do', 'In Progress', 'In Review', 'Done', 'Blocked'])->default('To Do');
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('work_packages')->nullOnDelete();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->decimal('actual_hours', 10, 2)->default(0.00);
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('project_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('type');
            $table->index('parent_id');
            $table->index(['due_date', 'start_date']);
            $table->index('created_by');
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_packages');
    }
};
