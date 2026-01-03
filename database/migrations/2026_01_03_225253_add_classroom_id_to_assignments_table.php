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
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('classroom_id')
                ->nullable()
                ->after('quarter_id')
                ->constrained('classrooms')
                ->onDelete('set null');
            
            $table->index('classroom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropIndex(['classroom_id']);
            $table->dropColumn('classroom_id');
        });
    }
};
