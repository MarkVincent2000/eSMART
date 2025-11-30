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
        Schema::table('student_infos', function (Blueprint $table) {
            // Drop the unique index on student_number
            $table->dropUnique(['student_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_infos', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique('student_number');
        });
    }
};
