<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * One row per subject; grade_type stores JSON: quarters/terms + final_grade + remarks.
     */
    public function up(): void
    {
        Schema::table('subject_grades', function (Blueprint $table) {
            $table->dropColumn(['grade_type', 'final_grade', 'remarks']);
        });

        Schema::table('subject_grades', function (Blueprint $table) {
            $table->json('grade_type')->nullable()->after('semester_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_grades', function (Blueprint $table) {
            $table->dropColumn('grade_type');
        });

        Schema::table('subject_grades', function (Blueprint $table) {
            $table->string('grade_type')->nullable()->after('semester_type');
            $table->decimal('final_grade', 5, 2)->nullable()->after('is_quarter');
            $table->text('remarks')->nullable()->after('final_grade');
        });
    }
};
