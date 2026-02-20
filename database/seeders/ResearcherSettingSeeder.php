<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class ResearcherSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            // Landing Page Settings (Researcher Section)
            [
                'key' => 'landing.researcher_title',
                'name' => 'Researcher Section Title',
                'value' => 'Our',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_title_highlight',
                'name' => 'Researcher Section Title Highlight Text',
                'value' => 'Researchers',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_description',
                'name' => 'Researcher Section Description',
                'value' => 'Our dedicated researchers drive innovation and explore new possibilities. They are passionate about uncovering insights that shape the future.',
                'type' => 'textarea',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_1_image',
                'name' => 'Researcher 1 Image',
                'value' => 'build/images/users/avatar-2.jpg',
                'type' => 'file',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_1_name',
                'name' => 'Researcher 1 Name',
                'value' => 'Jane Doe',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_1_role',
                'name' => 'Researcher 1 Role',
                'value' => 'Lead Researcher',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_1_email',
                'name' => 'Researcher 1 Email',
                'value' => 'mailto:jane@example.com',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_2_image',
                'name' => 'Researcher 2 Image',
                'value' => 'build/images/users/mark.png',
                'type' => 'file',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_2_name',
                'name' => 'Researcher 2 Name',
                'value' => 'John Smith',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_2_role',
                'name' => 'Researcher 2 Role',
                'value' => 'Data Scientist',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_2_email',
                'name' => 'Researcher 2 Email',
                'value' => 'mailto:john@example.com',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_3_image',
                'name' => 'Researcher 3 Image',
                'value' => 'build/images/users/kit.png',
                'type' => 'file',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_3_name',
                'name' => 'Researcher 3 Name',
                'value' => 'Alice Johnson',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_3_role',
                'name' => 'Researcher 3 Role',
                'value' => 'Research Assistant',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_3_email',
                'name' => 'Researcher 3 Email',
                'value' => 'mailto:alice@example.com',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_4_image',
                'name' => 'Researcher 4 Image',
                'value' => 'build/images/users/cuyag.png',
                'type' => 'file',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_4_name',
                'name' => 'Researcher 4 Name',
                'value' => 'Bob Williams',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_4_role',
                'name' => 'Researcher 4 Role',
                'value' => 'Analyst',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.researcher_4_email',
                'name' => 'Researcher 4 Email',
                'value' => 'mailto:bob@example.com',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
