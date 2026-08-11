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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['Planning', 'Active', 'On Hold', 'Closed', 'Archived'])->default('Planning');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_leader_id')->constrained('users')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->decimal('budget', 12, 2)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('created_by');
            $table->index('project_leader_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
