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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Student and Academic Relations
            // ============================================
            $table->foreignId('student_info_id')
                ->constrained('student_infos')
                ->onDelete('cascade');
            
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');
            
            $table->foreignId('quarter_id')
                ->nullable()
                ->constrained('quarters')
                ->onDelete('set null');
            
            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');

            // ============================================
            // Subject and Assignment Relations
            // ============================================
            // Polymorphic subject relationship
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable(); // For polymorphic relationship
            
            $table->foreignId('assignment_id')
                ->nullable()
                ->constrained('assignments')
                ->onDelete('set null'); // Link to assignment if grade is from an assignment

            // ============================================
            // DepEd K-12 Component Category
            // ============================================
            $table->enum('component_category', [
                'written_works',
                'performance_tasks',
                'quarterly_assessment'
            ])->nullable();

            // ============================================
            // Grade Type
            // ============================================
            $table->enum('grade_type', [
                'quiz',
                'exam',
                'midterm',
                'final',
                'assignment',
                'project',
                'laboratory',
                'participation',
                'attendance',
                'homework',
                'activity',
                'other'
            ])->nullable();

            // ============================================
            // Grade Values
            // ============================================
            $table->decimal('grade_value', 8, 2)->nullable(); // Raw grade value
            $table->decimal('percentage', 5, 2)->nullable(); // Percentage (0-100)
            $table->string('letter_grade', 5)->nullable(); // Letter grade (O, VS, S, FS, D)
            $table->decimal('points_earned', 8, 2)->nullable();
            $table->decimal('points_possible', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // Weight percentage for this grade within its component

            // ============================================
            // Grade Status
            // ============================================
            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'finalized',
                'locked'
            ])->default('draft');

            // ============================================
            // Comments and Remarks
            // ============================================
            $table->text('remarks')->nullable();
            $table->text('comments')->nullable();

            // ============================================
            // Grading Timestamps
            // ============================================
            $table->dateTime('graded_at')->nullable();
            
            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            $table->dateTime('approved_at')->nullable();

            // ============================================
            // Metadata
            // ============================================
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // Indexes
            // ============================================
            $table->index('student_info_id');
            $table->index('semester_id');
            $table->index('quarter_id');
            $table->index('section_id');
            $table->index(['subject_id', 'subject_type']);
            $table->index('assignment_id');
            $table->index('component_category');
            $table->index('grade_type');
            $table->index('status');
            $table->index('graded_by');
            $table->index('approved_by');
            
            // Composite indexes for common queries
            $table->index(['student_info_id', 'semester_id', 'quarter_id']);
            $table->index(['student_info_id', 'subject_id', 'component_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

