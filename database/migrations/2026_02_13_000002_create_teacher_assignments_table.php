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
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();

            // Core relation to teacher
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->onDelete('cascade');

            // Optional link to a specific classroom
            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained('classrooms')
                ->onDelete('set null');

            // Subject being handled
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable(); // keep flexible for polymorphic subjects

            // Section and semester for this load
            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');

            // Load and scheduling details
            $table->decimal('load_units', 5, 2)->nullable();
            $table->json('schedule')->nullable(); // e.g., days/times/rooms

            // Flexible JSON metadata (for grading settings, notes, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['teacher_id', 'semester_id']);
            $table->index(['subject_id', 'subject_type']);
            $table->index('section_id');
            $table->index('classroom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};

