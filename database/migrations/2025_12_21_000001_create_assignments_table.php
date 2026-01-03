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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Assignment Information
            // ============================================
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->enum('assignment_type', [
                'assignment',
                'project',
                'homework',
                'activity',
                'laboratory',
                'research',
                'presentation',
                'other'
            ])->default('assignment');

            // ============================================
            // Academic Relations
            // ============================================
            // Polymorphic subject relationship
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable(); // For polymorphic relationship
            
            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');
            
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');
            
            $table->foreignId('quarter_id')
                ->nullable()
                ->constrained('quarters')
                ->onDelete('set null');

            // ============================================
            // DepEd K-12 Component Category
            // ============================================
            $table->enum('component_category', [
                'written_works',
                'performance_tasks'
            ])->nullable();

            // ============================================
            // Grading Information
            // ============================================
            $table->decimal('points_possible', 8, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // Weight percentage within the component

            // ============================================
            // Due Date and Time
            // ============================================
            $table->date('due_date')->nullable();
            $table->dateTime('due_time')->nullable();

            // ============================================
            // Late Submission Settings
            // ============================================
            $table->boolean('allow_late_submission')->default(false);
            $table->decimal('late_penalty_percentage', 5, 2)->nullable()->default(0); // Percentage deduction per day late
            $table->unsignedInteger('max_late_days')->nullable(); // Maximum days after due date that submission is accepted

            // ============================================
            // Assignment Settings
            // ============================================
            $table->enum('status', [
                'draft',
                'published',
                'closed',
                'cancelled'
            ])->default('draft');
            
            $table->boolean('is_required')->default(true);
            $table->boolean('is_group_assignment')->default(false);
            $table->unsignedTinyInteger('max_group_size')->nullable();

            // ============================================
            // Creator and Timestamps
            // ============================================
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->dateTime('published_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            // ============================================
            // Metadata
            // ============================================
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // Indexes
            // ============================================
            $table->index(['subject_id', 'subject_type']);
            $table->index('section_id');
            $table->index('semester_id');
            $table->index('quarter_id');
            $table->index('component_category');
            $table->index('status');
            $table->index('created_by');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

