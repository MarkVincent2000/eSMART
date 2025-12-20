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
        Schema::create('program_year_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('cascade');
            $table->unsignedTinyInteger('year_level');
            $table->timestamps();

            // Add unique constraint to prevent duplicate program-year_level combinations
            $table->unique(['program_id', 'year_level']);

            // Add indexes for better query performance
            $table->index('program_id');
            $table->index('year_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_year_level');
    }
};
