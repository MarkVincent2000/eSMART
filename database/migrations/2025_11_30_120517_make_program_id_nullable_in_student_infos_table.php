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
        Schema::table('student_infos', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['program_id']);
        });
        
        // Modify the column to be nullable
        DB::statement('ALTER TABLE student_infos MODIFY program_id BIGINT UNSIGNED NULL');
        
        Schema::table('student_infos', function (Blueprint $table) {
            // Re-add the foreign key constraint (now nullable)
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, set any NULL values to a default program (if needed)
        // Or you can choose to delete records with NULL program_id
        // For now, we'll just make it required again
        
        Schema::table('student_infos', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['program_id']);
        });
        
        // Make the column NOT NULL (this will fail if there are NULL values)
        DB::statement('ALTER TABLE student_infos MODIFY program_id BIGINT UNSIGNED NOT NULL');
        
        Schema::table('student_infos', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->onDelete('restrict');
        });
    }
};
