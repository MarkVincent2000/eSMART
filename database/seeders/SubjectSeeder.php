<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Seed the subjects table with common subjects.
     */
    public function run(): void
    {
        $subjects = [
            // Core Subjects - Grade 7-10
            [
                'code' => 'MATH101',
                'name' => 'Mathematics',
                'description' => 'Basic Mathematics',
                'units' => 3.00,
                'year_level' => 7, // Available for all levels
                'is_active' => true,
            ],
            [
                'code' => 'ENG101',
                'name' => 'English',
                'description' => 'English Language and Literature',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [   
                'code' => 'SCI101',
                'name' => 'Science',
                'description' => 'General Science',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'FIL101',
                'name' => 'Filipino',
                'description' => 'Filipino Language and Literature',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'SOC101',
                'name' => 'Social Studies',
                'description' => 'Social Studies and History',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'PE101',
                'name' => 'Physical Education',
                'description' => 'Physical Education and Health',
                'units' => 2.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'TLE101',
                'name' => 'Technology and Livelihood Education',
                'description' => 'TLE - Technology and Livelihood Education',
                'units' => 2.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'MAPEH101',
                'name' => 'MAPEH',
                'description' => 'Music, Arts, Physical Education, and Health',
                'units' => 2.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            
            // Senior High School Subjects
            [
                'code' => 'BIO101',
                'name' => 'Biology',
                'description' => 'General Biology',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'CHEM101',
                'name' => 'Chemistry',
                'description' => 'General Chemistry',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'PHYS101',
                'name' => 'Physics',
                'description' => 'General Physics',
                'units' => 3.00,
                'year_level' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'COMP101',
                'name' => 'Computer Science',
                'description' => 'Introduction to Computer Science',
                'units' => 3.00,
                'year_level' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'STAT101',
                'name' => 'Statistics',
                'description' => 'Statistics and Probability',
                'units' => 3.00,
                'year_level' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'RES101',
                'name' => 'Research',
                'description' => 'Research Methods',
                'units' => 2.00,
                'year_level' => 8,
                'is_active' => true,
            ],
            [
                'code' => 'PHILO101',
                'name' => 'Philosophy',
                'description' => 'Introduction to Philosophy',
                'units' => 3.00,
                'year_level' => 11,
                'is_active' => true,
            ],
            [
                'code' => 'ECON101',
                'name' => 'Economics',
                'description' => 'Basic Economics',
                'units' => 3.00,
                'year_level' => 11,
                'is_active' => true,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                [
                    'code' => $subject['code'],
                ],
                $subject
            );
        }

        $this->command->info('Subjects seeded successfully.');
    }
}
