<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance\AttendanceCategory;

class AttendanceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Class',
                'slug' => 'class',
                'description' => 'Regular classroom sessions',
                'color' => '#3b82f6',
                'icon' => 'mdi-school',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Laboratory',
                'slug' => 'laboratory',
                'description' => 'Laboratory and practical sessions',
                'color' => '#8b5cf6',
                'icon' => 'mdi-flask',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Lecture',
                'slug' => 'lecture',
                'description' => 'Large lecture sessions',
                'color' => '#06b6d4',
                'icon' => 'mdi-presentation',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Exam',
                'slug' => 'exam',
                'description' => 'Examination sessions',
                'color' => '#ef4444',
                'icon' => 'mdi-file-document-edit',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Event',
                'slug' => 'event',
                'description' => 'Special events and activities',
                'color' => '#f59e0b',
                'icon' => 'mdi-calendar-star',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Meeting',
                'slug' => 'meeting',
                'description' => 'Meetings and discussions',
                'color' => '#10b981',
                'icon' => 'mdi-account-multiple',
                'is_active' => true,
                'display_order' => 6,
            ],
            [
                'name' => 'Workshop',
                'slug' => 'workshop',
                'description' => 'Workshop and training sessions',
                'color' => '#6366f1',
                'icon' => 'mdi-tools',
                'is_active' => true,
                'display_order' => 7,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other attendance types',
                'color' => '#6b7280',
                'icon' => 'mdi-dots-horizontal',
                'is_active' => true,
                'display_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            AttendanceCategory::create($category);
        }
    }
}
