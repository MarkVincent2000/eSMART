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
        Schema::create('student_info_grades', function (Blueprint $table) {
            $table->id();

            // Foreign key to student_info
            $table->foreignId('student_info_id')
                ->constrained('student_infos')
                ->onDelete('cascade');

            // Student information fields
            $table->string('name');
            $table->string('school_year');
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('lrn')->nullable();
            $table->integer('grade')->nullable();
            $table->string('section')->nullable();
            $table->date('date_of_birth')->nullable();

            // Teacher information
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->onDelete('set null');
            $table->string('teacher_name')->nullable();

            // Grade issuance and eligibility
            $table->date('date_issued')->nullable();
            $table->boolean('eligible_to_advance_grade')->default(false);
            $table->boolean('has_advance_unit_in')->default(false);
            $table->boolean('has_lacking_unit_in')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_info_id');
            $table->index('school_year');
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_info_grades');
    }
};
