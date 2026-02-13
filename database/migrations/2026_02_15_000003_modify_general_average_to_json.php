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
        // Convert existing decimal values to JSON format before changing column type
        $grades = DB::table('student_info_grades')
            ->whereNotNull('general_average')
            ->get();

        foreach ($grades as $grade) {
            $value = $grade->general_average;
            if ($value !== null) {
                // Store as JSON with "overall" key for existing data
                DB::table('student_info_grades')
                    ->where('id', $grade->id)
                    ->update([
                        'general_average' => json_encode(['overall' => (float) $value]),
                        'general_average_remark' => $grade->general_average_remark 
                            ? json_encode(['overall' => $grade->general_average_remark])
                            : null,
                    ]);
            }
        }

        Schema::table('student_info_grades', function (Blueprint $table) {
            $table->json('general_average')->nullable()->change();
            $table->json('general_average_remark')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON back to decimal (take "overall" value if present)
        $grades = DB::table('student_info_grades')
            ->whereNotNull('general_average')
            ->get();

        foreach ($grades as $grade) {
            $jsonData = json_decode($grade->general_average, true);
            $remarkData = json_decode($grade->general_average_remark, true);
            
            if (is_array($jsonData) && isset($jsonData['overall'])) {
                DB::table('student_info_grades')
                    ->where('id', $grade->id)
                    ->update([
                        'general_average' => $jsonData['overall'],
                        'general_average_remark' => is_array($remarkData) && isset($remarkData['overall']) 
                            ? $remarkData['overall'] 
                            : null,
                    ]);
            }
        }

        Schema::table('student_info_grades', function (Blueprint $table) {
            $table->decimal('general_average', 5, 2)->nullable()->change();
            $table->string('general_average_remark', 50)->nullable()->change();
        });
    }
};
