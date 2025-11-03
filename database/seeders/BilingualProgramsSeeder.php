<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Program;
use Illuminate\Database\Seeder;

class BilingualProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف البرامج الموجودة
        Program::query()->delete();

        // بيانات البرامج باللغتين العربية والإنجليزية
        $programs = [
            [
                'category_name' => 'الإعانة الشهرية',
                'title_ar' => 'برنامج الإعانة الشهرية',
                'title_en' => 'Monthly Allowance Program',
                'description_ar' => 'برنامج مخصص لتقديم دعم مالي شهري للطلاب المحتاجين. يساعد الطلاب في تغطية المصاريف الأساسية مثل الطعام والملابس والكتب الدراسية.',
                'description_en' => 'A program dedicated to providing monthly financial support to needy students. Helps students cover basic expenses such as food, clothing, and textbooks.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
                'status' => 'active',
            ],
            [
                'category_name' => 'السكن والنقل',
                'title_ar' => 'برنامج السكن والنقل',
                'title_en' => 'Housing and Transportation Program',
                'description_ar' => 'برنامج يساعد الطلاب في توفير سكن مناسب ووسائل النقل. يشمل دعم إيجار السكن وتذاكر المواصلات للطلاب المحتاجين.',
                'description_en' => 'A program that helps students provide adequate housing and transportation. Includes housing rent support and transportation tickets for needy students.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'status' => 'active',
            ],
            [
                'category_name' => 'فرص التعليم العالي',
                'title_ar' => 'برنامج فرص التعليم العالي',
                'title_en' => 'Higher Education Opportunities Program',
                'description_ar' => 'برنامج يهدف إلى توفير فرص تعليمية للطلاب المتفوقين. يشمل منح دراسية وبرامج تطوير المهارات والتدريب المهني.',
                'description_en' => 'A program aimed at providing educational opportunities for outstanding students. Includes scholarships, skills development programs, and vocational training.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
                'status' => 'active',
            ],
            [
                'category_name' => 'رسوم الاختبارات',
                'title_ar' => 'برنامج رسوم الاختبارات',
                'title_en' => 'Examination Fees Program',
                'description_ar' => 'برنامج لتغطية رسوم الاختبارات والامتحانات للطلاب المحتاجين. يساعد الطلاب في دفع رسوم الاختبارات الدولية والمحلية.',
                'description_en' => 'A program to cover examination and test fees for needy students. Helps students pay for international and local examination fees.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
                'status' => 'active',
            ],
        ];

        foreach ($programs as $programData) {
            $category = Category::where('name_ar', $programData['category_name'])->first();
            
            if ($category) {
                Program::create([
                    'category_id' => $category->id,
                    'title_ar' => $programData['title_ar'],
                    'title_en' => $programData['title_en'],
                    'description_ar' => $programData['description_ar'],
                    'description_en' => $programData['description_en'],
                    'image' => $programData['image'],
                    'status' => $programData['status'],
                ]);
            }
        }

        echo "تم إنشاء البرامج بنجاح!\n";
        echo "عدد البرامج: " . Program::count() . " برامج\n";
    }
}

