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
        Schema::create('work_package_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_work_package_id')->constrained('work_packages')->cascadeOnDelete();
            $table->foreignId('target_work_package_id')->constrained('work_packages')->cascadeOnDelete();
            $table->enum('relation_type', ['blocks', 'blocked_by', 'precedes', 'follows']);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['source_work_package_id', 'target_work_package_id', 'relation_type']);
            
            // Indexes
            $table->index('source_work_package_id');
            $table->index('target_work_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_package_relations');
    }
};
