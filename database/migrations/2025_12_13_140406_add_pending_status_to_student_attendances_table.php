<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum column to include 'pending'
        // Note: MySQL doesn't support direct enum modification, so we use raw SQL
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'excused', 'partial', 'leave', 'pending') DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending' from the enum (revert to original)
        DB::statement("ALTER TABLE student_attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'excused', 'partial', 'leave') DEFAULT 'present'");
        
        // Update any records with 'pending' status to 'absent' before removing the enum value
        DB::table('student_attendances')
            ->where('status', 'pending')
            ->update(['status' => 'absent']);
    }
};
