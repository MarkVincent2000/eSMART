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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Session Information
            // ============================================
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // ============================================
            // Academic Relations
            // ============================================
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');
            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');

            // ============================================
            // Polymorphic Relation
            // For courses, subjects, events, etc.
            // ============================================
            $table->nullableMorphs('attendable');

            // ============================================
            // Attendance Type
            // ============================================
            $table->enum('attendance_type', [
                'class',
                'laboratory',
                'lecture',
                'exam',
                'event',
                'meeting',
                'workshop',
                'other'
            ])->default('class');

            // ============================================
            // Date and Time
            // ============================================
            $table->date('date');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('scheduled_duration_minutes')->nullable();

            // ============================================
            // Location Data
            // ============================================
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // ============================================
            // Session Status
            // ============================================
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();

            // ============================================
            // Metadata
            // Flexible JSON storage for additional data
            // ============================================
            $table->json('metadata')->nullable();

            // ============================================
            // Creator
            // User who created this attendance session
            // ============================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // ============================================
            // Timestamps
            // ============================================
            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // Indexes for Performance
            // ============================================
            
            // Single column indexes
            $table->index('date');
            $table->index('attendance_type');
            $table->index('is_active');
            $table->index('is_locked');
            $table->index('created_by');
            $table->index('attendable_type');
            $table->index('attendable_id');

            // Composite indexes for common query patterns
            $table->index(['semester_id', 'date']);
            $table->index(['section_id', 'date']);
            $table->index(['attendance_type', 'date']);
            $table->index(['is_active', 'date']);
            $table->index(['semester_id', 'section_id', 'date']);
            $table->index(['created_by', 'date']);
            $table->index(['attendable_type', 'attendable_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
