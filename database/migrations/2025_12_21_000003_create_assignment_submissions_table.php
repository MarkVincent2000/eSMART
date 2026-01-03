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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Assignment and Student Relations
            // ============================================
            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->onDelete('cascade');
            
            $table->foreignId('student_info_id')
                ->constrained('student_infos')
                ->onDelete('cascade');
            
            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null'); // User ID who submitted (for group assignments)

            // ============================================
            // Submission Content
            // ============================================
            $table->longText('content')->nullable();

            // ============================================
            // Submission Status
            // ============================================
            $table->enum('status', [
                'draft',
                'submitted',
                'late',
                'graded',
                'returned'
            ])->default('draft');

            // ============================================
            // Submission Timestamps
            // ============================================
            $table->dateTime('submitted_at')->nullable();

            // ============================================
            // Grading Information
            // ============================================
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            $table->decimal('points_earned', 8, 2)->nullable();
            $table->text('feedback')->nullable();

            // ============================================
            // Late Submission Tracking
            // ============================================
            $table->boolean('is_late')->default(false);
            $table->decimal('late_penalty_applied', 5, 2)->nullable()->default(0); // Percentage penalty applied

            // ============================================
            // Metadata
            // ============================================
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // Indexes
            // ============================================
            $table->index('assignment_id');
            $table->index('student_info_id');
            $table->index('submitted_by');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('is_late');
            
            // Unique constraint: one submission per student per assignment
            $table->unique(['assignment_id', 'student_info_id'], 'unique_assignment_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};

