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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('work_package_id')->nullable()->constrained('work_packages')->nullOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->integer('version')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('project_id');
            $table->index('work_package_id');
            $table->index('uploaded_by');
            $table->index('is_archived');
            $table->index('created_at');
            $table->index(['project_id', 'is_archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
