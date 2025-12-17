<?php

namespace Database\Seeders;

use App\Models\StudentDetails\Quarter;
use App\Models\StudentDetails\Semester;
use Illuminate\Database\Seeder;

class QuarterSeeder extends Seeder
{
    /**
     * Seed the quarters table with static quarters for 1st and 2nd semester only.
     *
     *  - 1st Semester -> Quarter 1 and Quarter 2
     *  - 2nd Semester -> Quarter 3 and Quarter 4
     */
    public function run(): void
    {
        // Find the two semesters we expect from SemesterSeeder
        $firstSemester = Semester::where('name', '1st Semester')->first();
        $secondSemester = Semester::where('name', '2nd Semester')->first();

        if (!$firstSemester || !$secondSemester) {
            $this->command->warn('Semesters not found. Please run SemesterSeeder first.');
            return;
        }

        // 1st Semester: Quarter 1 and Quarter 2
        $firstSemesterQuarters = [
            [
                'value' => 1,
                'name' => '1st Quarter',
                'description' => '1st Quarter of 1st Semester',
                'is_active' => false,
            ],
            [
                'value' => 2,
                'name' => '2nd Quarter',
                'description' => '2nd Quarter of 1st Semester',
                'is_active' => false,
            ],
        ];

        foreach ($firstSemesterQuarters as $quarter) {
            Quarter::firstOrCreate(
                [
                    'name' => $quarter['name'],
                    'semester_id' => $firstSemester->id,
                ],
                [
                    'name' => $quarter['name'],
                    'description' => $quarter['description'],
                    'semester_id' => $firstSemester->id,
                    'is_active' => $quarter['is_active'],
                ]
            );
        }

        // 2nd Semester: Quarter 3 and Quarter 4
        $secondSemesterQuarters = [
            [
                'value' => 3,
                'name' => '3rd Quarter',
                'description' => '3rd Quarter of 2nd Semester',
                'is_active' => false,
            ],
            [
                'value' => 4,
                'name' => '4th Quarter',
                'description' => '4th Quarter of 2nd Semester',
                'is_active' => false,
            ],
        ];

        foreach ($secondSemesterQuarters as $quarter) {
            Quarter::firstOrCreate(
                [
                    'name' => $quarter['name'],
                    'semester_id' => $secondSemester->id,
                ],
                [
                    'name' => $quarter['name'],
                    'description' => $quarter['description'],
                    'semester_id' => $secondSemester->id,
                    'is_active' => $quarter['is_active'],
                ]
            );
        }

        $this->command->info('Quarters for 1st and 2nd Semester seeded successfully.');
    }
}
