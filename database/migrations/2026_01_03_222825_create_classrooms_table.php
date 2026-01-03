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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();

            // ============================================
            // Class Information
            // ============================================
            $table->string('name'); // Class name (required)
            $table->string('class_code', 20)->unique(); // Unique class code (like Google Classroom)
            $table->text('description')->nullable();
            
            // ============================================
            // Academic Relations
            // ============================================
            // Subject relationship (polymorphic for flexibility)
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
            
            // ============================================
            // Class Settings
            // ============================================
            $table->string('room')->nullable(); // Room number/location
            $table->enum('status', [
                'active',
                'archived',
                'completed',
                'cancelled'
            ])->default('active');
            
            // ============================================
            // Enrollment Settings
            // ============================================
            $table->boolean('allow_student_posts')->default(true);
            $table->boolean('allow_student_comments')->default(true);
            $table->boolean('students_can_see_each_other')->default(true);
            $table->boolean('guardians_can_see_updates')->default(false);
            
            // ============================================
            // Creator and Timestamps
            // ============================================
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->dateTime('archived_at')->nullable();
            $table->dateTime('completed_at')->nullable();

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
            $table->index('status');
            $table->index('created_by');
            $table->index('class_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
