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
        Schema::table('student_info_grades', function (Blueprint $table) {
            $table->string('general_average_remark', 50)->nullable()->after('general_average');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_info_grades', function (Blueprint $table) {
            $table->dropColumn('general_average_remark');
        });
    }
};
