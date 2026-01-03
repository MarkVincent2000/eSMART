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
        Schema::create('assignment_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->onDelete('cascade');
            $table->foreignId('student_info_id')
                ->constrained('student_infos')
                ->onDelete('cascade');
            $table->timestamps();

            // Add unique constraint to prevent duplicate assignment-student combinations
            $table->unique(['assignment_id', 'student_info_id']);

            // Add indexes for better query performance
            $table->index('assignment_id');
            $table->index('student_info_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_student');
    }
};
