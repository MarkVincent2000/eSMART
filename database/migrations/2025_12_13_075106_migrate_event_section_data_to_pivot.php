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
        // Migrate existing section_id data to the pivot table
        $events = DB::table('events')->whereNotNull('section_id')->get();
        
        foreach ($events as $event) {
            DB::table('event_section')->insert([
                'event_id' => $event->id,
                'section_id' => $event->section_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the section_id column from events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['section_id']); // Drop foreign key first if exists
            $table->dropColumn('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add section_id column back to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('semester_id')->constrained('sections');
        });

        // Migrate data back from pivot table (take first section if multiple exist)
        $pivotData = DB::table('event_section')
            ->select('event_id', DB::raw('MIN(section_id) as section_id'))
            ->groupBy('event_id')
            ->get();

        foreach ($pivotData as $data) {
            DB::table('events')
                ->where('id', $data->event_id)
                ->update(['section_id' => $data->section_id]);
        }
    }
};
