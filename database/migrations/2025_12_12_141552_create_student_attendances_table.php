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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();

            // Attendance Session Relation
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');

            // User and Student Info Relations
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('student_info_id')
                ->nullable()
                ->constrained('student_infos')
                ->onDelete('cascade');

            // Attendance Status
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'excused',
                'partial',
                'leave'
            ])->default('present');

            // Time Tracking
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_excused')->default(false);
            $table->text('excuse_reason')->nullable();

            // Notes and Remarks
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();

            // Location Data
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ip_address')->nullable();
            $table->text('device_info')->nullable();

            // Metadata for flexible data storage
            $table->json('metadata')->nullable();

            // Approval System
            $table->foreignId('marked_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['attendance_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['attendance_id', 'status']);
            $table->index(['status']);
            $table->index(['is_late']);
            $table->index(['is_excused']);

            // Unique constraint to prevent duplicate attendance records per session
            $table->unique(['attendance_id', 'user_id'], 'unique_student_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
