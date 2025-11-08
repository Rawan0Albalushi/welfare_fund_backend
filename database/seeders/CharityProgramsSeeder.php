<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Program;
use Illuminate\Database\Seeder;

class CharityProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الفئات إذا لم تكن موجودة
        $categories = [
            [
                'name_ar' => 'الإعانة الشهرية',
                'name_en' => 'Monthly Allowance',
            ],
            [
                'name_ar' => 'السكن والنقل',
                'name_en' => 'Housing and Transport',
            ],
            [
                'name_ar' => 'فرص التعليم',
                'name_en' => 'Education Opportunities',
            ],
            [
                'name_ar' => 'الرعاية الصحية',
                'name_en' => 'Healthcare',
            ],
            [
                'name_ar' => 'مساعدة الأسر المحتاجة',
                'name_en' => 'Family Support',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name_ar' => $categoryData['name_ar']],
                [
                    'name_ar' => $categoryData['name_ar'],
                    'name_en' => $categoryData['name_en'],
                    'status' => 'active',
                ]
            );
        }

        // برامج التبرعات الخيرية
        $programs = [
            [
                'title_ar' => 'مساعدة كبار السن',
                'title_en' => 'Elderly Support',
                'description_ar' => 'برنامج لدعم كبار السن في الحصول على الرعاية الصحية والاحتياجات الأساسية. نساعد في توفير الأدوية والرعاية الطبية والاحتياجات اليومية.',
                'description_en' => 'A program to support the elderly in obtaining healthcare and basic needs. We help provide medications, medical care, and daily necessities.',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'مساعدة الأسر المحتاجة',
                'title_en' => 'Support for Needy Families',
                'description_ar' => 'برنامج لدعم الأسر المحتاجة في توفير السكن المناسب ووسائل النقل. نساعد في دفع الإيجارات وتوفير وسائل النقل للطلاب والعائلات.',
                'description_en' => 'A program to support needy families in providing adequate housing and transportation. We help pay rents and provide transportation for students and families.',
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'منح دراسية للطلاب المتفوقين',
                'title_en' => 'Scholarships for Outstanding Students',
                'description_ar' => 'برنامج لتوفير منح دراسية للطلاب المتفوقين من الأسر المحتاجة. نساعد في تغطية الرسوم الدراسية والكتب والمستلزمات التعليمية.',
                'description_en' => 'A program to provide scholarships for outstanding students from needy families. We help cover tuition fees, books, and educational supplies.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'علاج الأطفال المرضى',
                'title_en' => 'Treatment for Sick Children',
                'description_ar' => 'برنامج لمساعدة الأطفال المرضى في الحصول على العلاج الطبي المناسب. نساعد في تغطية تكاليف العمليات الجراحية والأدوية والرعاية الطبية.',
                'description_en' => 'A program to help sick children obtain appropriate medical treatment. We help cover costs for surgeries, medications, and medical care.',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'توفير الطعام للأسر الفقيرة',
                'title_en' => 'Food Support for Poor Families',
                'description_ar' => 'برنامج لتوفير الطعام والمواد الغذائية الأساسية للأسر الفقيرة. نساعد في توزيع السلال الغذائية وتوفير الوجبات اليومية.',
                'description_en' => 'A program to provide food and essential food items for poor families. We help distribute food baskets and provide daily meals.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'دعم الأرامل والأيتام',
                'title_en' => 'Support for Widows and Orphans',
                'description_ar' => 'برنامج لدعم الأرامل والأيتام في توفير الاحتياجات الأساسية. نساعد في دفع الإيجارات والطعام والملابس والتعليم.',
                'description_en' => 'A program to support widows and orphans in providing basic needs. We help pay for rent, food, clothing, and education.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'تطوير المهارات المهنية',
                'title_en' => 'Professional Skills Development',
                'description_ar' => 'برنامج لمساعدة الشباب في تطوير المهارات المهنية والحصول على التدريب المهني. نساعد في توفير دورات تدريبية ومعدات العمل.',
                'description_en' => 'A program to help youth develop professional skills and obtain vocational training. We help provide training courses and work equipment.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
            ],
            [
                'title_ar' => 'توفير سكن للطلاب الجامعيين',
                'title_en' => 'Housing for University Students',
                'description_ar' => 'برنامج لتوفير سكن مناسب للطلاب الجامعيين من المناطق النائية. نساعد في دفع إيجارات السكن وتوفير بيئة مناسبة للدراسة.',
                'description_en' => 'A program to provide suitable housing for university students from remote areas. We help pay housing rents and provide a suitable environment for study.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
            ],
        ];

        foreach ($programs as $programData) {
            Program::firstOrCreate(
                [
                    'title_ar' => $programData['title_ar'],
                ],
                [
                    'title_en' => $programData['title_en'],
                    'description_ar' => $programData['description_ar'],
                    'description_en' => $programData['description_en'],
                    'image' => $programData['image'],
                    'status' => 'active',
                ]
            );
        }
    }
}
