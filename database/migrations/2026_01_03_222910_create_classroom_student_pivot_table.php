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
        Schema::create('classroom_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->onDelete('cascade');
            $table->foreignId('student_info_id')
                ->constrained('student_infos')
                ->onDelete('cascade');
            $table->enum('role', [
                'student',
                'co-teacher',
                'guardian'
            ])->default('student');
            $table->enum('status', [
                'active',
                'enrolled',
                'invited',
                'left',
                'removed'
            ])->default('invited');
            $table->dateTime('enrolled_at')->nullable();
            $table->timestamps();

            // Add unique constraint to prevent duplicate classroom-student combinations
            $table->unique(['classroom_id', 'student_info_id']);

            // Add indexes for better query performance
            $table->index('classroom_id');
            $table->index('student_info_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_student');
    }
};
