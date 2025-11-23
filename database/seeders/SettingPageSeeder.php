<?php

namespace Database\Seeders;

use App\Models\SettingPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'key' => 'privacy_policy',
                'title_ar' => 'سياسة الخصوصية',
                'title_en' => 'Privacy Policy',
                'content_ar' => 'هذا محتوى سياسة الخصوصية باللغة العربية. يمكنك تعديل هذا المحتوى من لوحة الإدارة.',
                'content_en' => 'This is the privacy policy content in English. You can edit this content from the admin panel.',
            ],
            [
                'key' => 'about_app',
                'title_ar' => 'حول التطبيق',
                'title_en' => 'About App',
                'content_ar' => 'هذا محتوى صفحة حول التطبيق باللغة العربية. يمكنك تعديل هذا المحتوى من لوحة الإدارة.',
                'content_en' => 'This is the about app content in English. You can edit this content from the admin panel.',
            ],
            [
                'key' => 'security',
                'title_ar' => 'الأمان والخصوصية',
                'title_en' => 'Security & Privacy',
                'content_ar' => 'هذا محتوى صفحة الأمان والخصوصية باللغة العربية. يمكنك تعديل هذا المحتوى من لوحة الإدارة.',
                'content_en' => 'This is the security and privacy content in English. You can edit this content from the admin panel.',
            ],
            [
                'key' => 'contact_us',
                'title_ar' => 'تواصل معنا',
                'title_en' => 'Contact Us',
                'content_ar' => 'هذا محتوى صفحة التواصل معنا باللغة العربية. يمكنك تعديل هذا المحتوى من لوحة الإدارة.',
                'content_en' => 'This is the contact us content in English. You can edit this content from the admin panel.',
            ],
        ];

        foreach ($pages as $page) {
            SettingPage::updateOrCreate(
                ['key' => $page['key']],
                $page
            );
        }
    }
}
