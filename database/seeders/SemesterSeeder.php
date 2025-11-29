<?php

namespace Database\Seeders;

use App\Models\StudentDetails\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Seed the semesters table with standard semesters.
     */
    public function run(): void
    {
        $semesters = [
            [
                'name' => '1st Semester',
                'school_year' => '2025-2026',
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-15',
                'is_active' => true,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2025-2026',
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-30',
                'is_active' => false,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::firstOrCreate(
                ['name' => $semester['name']],
                $semester
            );
        }
    }
}

