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
        Schema::create('attendance_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');
            $table->foreignId('section_id')
                ->constrained('sections')
                ->onDelete('cascade');
            $table->timestamps();

            // Add unique constraint to prevent duplicate attendance-section combinations
            $table->unique(['attendance_id', 'section_id']);

            // Add indexes for better query performance
            $table->index('attendance_id');
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_section');
    }
};
