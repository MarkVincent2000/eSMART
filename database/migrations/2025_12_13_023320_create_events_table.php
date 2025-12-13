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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Event Information
            // ============================================
            $table->string('title');
            $table->text('description')->nullable();

            // ============================================
            // Event Type and Category
            // ============================================
            $table->enum('event_type', [
                'academic',
                'social',
                'sports',
                'cultural',
                'workshop',
                'seminar',
                'conference',
                'ceremony',
                'meeting',
                'other'
            ])->default('other');
            $table->string('category')->nullable();

            // ============================================
            // Date and Time
            // ============================================
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            // ============================================
            // Location
            // ============================================
            $table->string('location')->nullable();

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
            // Event Status
            // ============================================
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'published',
                'cancelled',
                'completed',
                'postponed'
            ])->default('draft');

            // ============================================
            // Media
            // ============================================
            $table->string('image')->nullable();

            // ============================================
            // Metadata
            // Flexible JSON storage for additional data
            // ============================================
            $table->json('metadata')->nullable();

            // ============================================
            // Creator and Approval
            // ============================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // ============================================
            // Timestamps
            // ============================================
            $table->timestamps();
            $table->softDeletes();

            // ============================================
            // Indexes for Performance
            // ============================================
            
            // Single column indexes
            $table->index('start_date');
            $table->index('end_date');
            $table->index('event_type');
            $table->index('status');
            $table->index('created_by');
            $table->index('approved_at');

            // Composite indexes for common query patterns
            $table->index(['semester_id', 'start_date']);
            $table->index(['section_id', 'start_date']);
            $table->index(['event_type', 'start_date']);
            $table->index(['status', 'start_date']);
            $table->index(['semester_id', 'section_id', 'start_date']);
            $table->index(['created_by', 'start_date']);
            $table->index(['status', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
