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
        // Seed only two semesters: 1st and 2nd Semester for a single school year
        $semesters = [
            [
                'name' => '1st Semester',
                'school_year' => '2025-2026',
                'start_date' => '2025-08-15',
                'end_date' => '2025-12-20',
                'is_active' => true,
                'is_display' => true,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2025-2026',
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-20',
                'is_active' => false,
                'is_display' => true,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::firstOrCreate(
                [
                    'name' => $semester['name'],
                    'school_year' => $semester['school_year'],
                ],
                $semester
            );
        }
    }
}
