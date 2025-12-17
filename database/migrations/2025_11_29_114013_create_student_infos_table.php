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
        Schema::create('student_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Core student identifiers
            $table->string('student_number')->unique();     // official ID number
            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('restrict');
            $table->unsignedTinyInteger('year_level');      // 1–4
            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');
            // Store semester details as JSON instead of a foreign key
            $table->json('semester')->nullable();           // e.g. { "id": 1, "name": "1st Semester", "school_year": "2025-2026" }
            $table->string('school_year');                  // e.g. 2025-2026

            // Status / meta
            $table->enum('status', ['pending', 'enrolled', 'inactive', 'graduated'])
                  ->default('pending');
            $table->date('enrolled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_infos');
    }
};
