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
        Schema::create('subject_grades', function (Blueprint $table) {
            $table->id();

            // Foreign key to student_info_grade
            $table->foreignId('student_info_grade_id')
                ->constrained('student_info_grades')
                ->onDelete('cascade');

            // Subject information
            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->onDelete('set null');
            $table->string('subject_name')->nullable();

            // Semester information
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');
            $table->string('semester_type')->nullable();

            // Grade details (migrated to JSON grade_type in modify_subject_grades_grade_type_to_json)
            $table->string('grade_type')->nullable();
            $table->boolean('is_quarter')->default(false);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_info_grade_id');
            $table->index('subject_id');
            $table->index('semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_grades');
    }
};
