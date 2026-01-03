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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            
            // Subject identification
            $table->string('code')->unique(); // e.g. "MATH101", "ENG101"
            $table->string('name'); // e.g. "Mathematics", "English"
            $table->text('description')->nullable();
            
            // Academic details
            $table->decimal('units', 5, 2)->default(0); // Credit units (e.g. 3.00)
            $table->unsignedTinyInteger('year_level')->nullable(); // 1-4, null for all levels
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Metadata for additional information
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('year_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
