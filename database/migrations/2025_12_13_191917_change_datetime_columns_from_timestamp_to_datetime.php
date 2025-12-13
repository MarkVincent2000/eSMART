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
        // Change attendances table columns from timestamp to time
        Schema::table('attendances', function (Blueprint $table) {
            // Change start_time from timestamp to time (time only, no date)
            DB::statement('ALTER TABLE attendances MODIFY start_time TIME NULL');
            
            // Change end_time from timestamp to time (time only, no date)
            DB::statement('ALTER TABLE attendances MODIFY end_time TIME NULL');
            
            // Keep locked_at as datetime (it needs full date-time)
            DB::statement('ALTER TABLE attendances MODIFY locked_at DATETIME NULL');
        });

        // Change student_attendances table columns from timestamp to time
        Schema::table('student_attendances', function (Blueprint $table) {
            // Change check_in_time from timestamp to time (time only, date comes from attendance->date)
            DB::statement('ALTER TABLE student_attendances MODIFY check_in_time TIME NULL');
            
            // Change check_out_time from timestamp to time (time only, date comes from attendance->date)
            DB::statement('ALTER TABLE student_attendances MODIFY check_out_time TIME NULL');
            
            // Keep approved_at as datetime (it needs full date-time)
            DB::statement('ALTER TABLE student_attendances MODIFY approved_at DATETIME NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert attendances table columns from time back to timestamp
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement('ALTER TABLE attendances MODIFY start_time TIMESTAMP NULL');
            DB::statement('ALTER TABLE attendances MODIFY end_time TIMESTAMP NULL');
            DB::statement('ALTER TABLE attendances MODIFY locked_at TIMESTAMP NULL');
        });

        // Revert student_attendances table columns from time back to timestamp
        Schema::table('student_attendances', function (Blueprint $table) {
            DB::statement('ALTER TABLE student_attendances MODIFY check_in_time TIMESTAMP NULL');
            DB::statement('ALTER TABLE student_attendances MODIFY check_out_time TIMESTAMP NULL');
            DB::statement('ALTER TABLE student_attendances MODIFY approved_at TIMESTAMP NULL');
        });
    }
};
