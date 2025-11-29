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
        Schema::table('semesters', function (Blueprint $table) {
            // Check and add school_year if it doesn't exist
            if (!Schema::hasColumn('semesters', 'school_year')) {
                $table->string('school_year')->after('name');
            }
            
            // Check and add start_date if it doesn't exist
            if (!Schema::hasColumn('semesters', 'start_date')) {
                $table->date('start_date')->nullable()->after('school_year');
            }
            
            // Check and add end_date if it doesn't exist
            if (!Schema::hasColumn('semesters', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
        
        // Rename active to is_active if it exists and is_active doesn't
        if (Schema::hasColumn('semesters', 'active') && !Schema::hasColumn('semesters', 'is_active')) {
            DB::statement('ALTER TABLE semesters CHANGE active is_active BOOLEAN DEFAULT FALSE');
        }
        
        // Update is_active default if column exists
        if (Schema::hasColumn('semesters', 'is_active')) {
            DB::statement('ALTER TABLE semesters MODIFY is_active BOOLEAN DEFAULT FALSE');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['school_year', 'start_date', 'end_date']);
        });
        
        // Rename is_active back to active using raw SQL
        if (Schema::hasColumn('semesters', 'is_active') && !Schema::hasColumn('semesters', 'active')) {
            DB::statement('ALTER TABLE semesters CHANGE is_active active BOOLEAN DEFAULT TRUE');
        }
    }
};
