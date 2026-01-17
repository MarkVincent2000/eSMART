<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Icons & Logos
            [
                'key' => 'site.favicon',
                'name' => 'Favicon (ICO Icon)',
                'value' => 'build/images/favicon.ico',
                'type' => 'file',
                'group' => 'Branding',
                'is_locked' => false,
            ],
            [
                'key' => 'site.sidebar_logo',
                'name' => 'Sidebar Logo',
                'value' => 'build/images/smart-logo-sm3.png',
                'type' => 'file',
                'group' => 'Branding',
                'is_locked' => false,
            ],
            [
                'key' => 'site.login_logo',
                'name' => 'Login Page Logo',
                'value' => 'build/images/smart-logo-sm3.png',
                'type' => 'file',
                'group' => 'Branding',
                'is_locked' => false,
            ],
            [
                'key' => 'site.landing_logo_dark',
                'name' => 'Landing Page Logo (Dark)',
                'value' => 'build/images/smart-logo-dark.png',
                'type' => 'file',
                'group' => 'Branding',
                'is_locked' => false,
            ],
            [
                'key' => 'site.landing_logo_light',
                'name' => 'Landing Page Logo (Light)',
                'value' => 'build/images/smart-logo-light.png',
                'type' => 'file',
                'group' => 'Branding',
                'is_locked' => false,
            ],

            // Site Information
            [
                'key' => 'site.name',
                'name' => 'Site Name',
                'value' => 'eSMART Campus',
                'type' => 'text',
                'group' => 'General',
                'is_locked' => false,
            ],
            [
                'key' => 'site.short_name',
                'name' => 'Site Short Name',
                'value' => 'smart',
                'type' => 'text',
                'group' => 'General',
                'is_locked' => false,
            ],
            [
                'key' => 'site.tagline',
                'name' => 'Site Tagline',
                'value' => 'The better way to manage your campus',
                'type' => 'text',
                'group' => 'General',
                'is_locked' => false,
            ],
            [
                'key' => 'site.description',
                'name' => 'Site Description',
                'value' => 'eSMART Campus is a comprehensive student management system designed to streamline campus operations, student enrollment, attendance tracking, and academic management.',
                'type' => 'textarea',
                'group' => 'General',
                'is_locked' => false,
            ],

            // Footer
            [
                'key' => 'site.footer_text',
                'name' => 'Footer Text',
                'value' => 'eSMART Campus. Crafted with ❤️ by eSMART Campus Team',
                'type' => 'text',
                'group' => 'General',
                'is_locked' => false,
            ],

            // Login/Register Pages
            [
                'key' => 'auth.welcome_message',
                'name' => 'Login Welcome Message',
                'value' => 'Welcome Back !',
                'type' => 'text',
                'group' => 'Authentication',
                'is_locked' => false,
            ],
            [
                'key' => 'auth.login_subtitle',
                'name' => 'Login Page Subtitle',
                'value' => 'Sign in to continue to eSMART Campus.',
                'type' => 'text',
                'group' => 'Authentication',
                'is_locked' => false,
            ],
            [
                'key' => 'auth.register_title',
                'name' => 'Register Page Title',
                'value' => 'Create New Account',
                'type' => 'text',
                'group' => 'Authentication',
                'is_locked' => false,
            ],
            [
                'key' => 'auth.register_subtitle',
                'name' => 'Register Page Subtitle',
                'value' => 'Get your free eSMART Campus account now',
                'type' => 'text',
                'group' => 'Authentication',
                'is_locked' => false,
            ],

            // Landing Page Quotes
            [
                'key' => 'landing.quote_1',
                'name' => 'Landing Page Quote 1',
                'value' => 'Sa Manongol High, Gaganda ang Buhay!',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.quote_2',
                'name' => 'Landing Page Quote 2',
                'value' => 'Manage your campus tasks in one unified place.',
                'type' => 'text',
                'group' => 'Landing Page',
                'is_locked' => false,
            ],
            [
                'key' => 'landing.quote_3',
                'name' => 'Landing Page Quote 3',
                'value' => 'SMART Campus keeps everyone connected and informed.',
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
