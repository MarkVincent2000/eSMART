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
                'is_active' => false,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2025-2026',
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-30',
                'is_active' => false,
            ],
            [
                'name' => '1st Semester',
                'school_year' => '2026-2027',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2026-2027',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '1st Semester',
                'school_year' => '2027-2028',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2027-2028',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '1st Semester',
                'school_year' => '2028-2029',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2028-2029',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '1st Semester',
                'school_year' => '2029-2030',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => false,
            ],
            [
                'name' => '2nd Semester',
                'school_year' => '2029-2030',
                'start_date' => '2025-11-30',
                'end_date' => '2025-11-30',
                'is_active' => true,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::firstOrCreate(
                [
                    'name' => $semester['name'],
                    'school_year' => $semester['school_year']
                ],
                $semester
            );
        }
    }
}

