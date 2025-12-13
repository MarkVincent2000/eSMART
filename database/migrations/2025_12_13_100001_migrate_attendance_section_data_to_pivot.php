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
        // Migrate existing section_id data to pivot table
        DB::statement("
            INSERT INTO attendance_section (attendance_id, section_id, created_at, updated_at)
            SELECT id, section_id, created_at, updated_at
            FROM attendances
            WHERE section_id IS NOT NULL
            ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)
        ");

        // Remove section_id column and related indexes from attendances table
        Schema::table('attendances', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['section_id']);
        });
        
        // Drop indexes using raw SQL (try-catch to handle if they don't exist)
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_section_id_date_index');
        } catch (\Exception $e) {
            // Index might not exist or have different name, ignore
        }
        
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_semester_id_section_id_date_index');
        } catch (\Exception $e) {
            // Index might not exist or have different name, ignore
        }
        
        // Drop the section_id column
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add section_id column back to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('section_id')
                ->nullable()
                ->after('semester_id')
                ->constrained('sections')
                ->onDelete('set null');
            
            // Re-add indexes
            $table->index(['section_id', 'date']);
            $table->index(['semester_id', 'section_id', 'date']);
        });

        // Migrate data back from pivot table to attendances table
        // Note: This will only work if each attendance has exactly one section
        // If an attendance has multiple sections, only the first one will be restored
        DB::statement("
            UPDATE attendances a
            INNER JOIN (
                SELECT attendance_id, section_id
                FROM attendance_section
                GROUP BY attendance_id
                HAVING COUNT(*) = 1
            ) AS pivot ON a.id = pivot.attendance_id
            SET a.section_id = pivot.section_id
        ");
    }
};
