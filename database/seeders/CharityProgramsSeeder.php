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
            'الإعانة الشهرية' => 'monthly_aid',
            'السكن والنقل' => 'housing_transport',
            'فرص التعليم' => 'education_opportunities',
            'الرعاية الصحية' => 'healthcare',
            'مساعدة الأسر المحتاجة' => 'family_support',
        ];

        foreach ($categories as $name => $key) {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'name' => $name,
                    'status' => 'active',
                ]
            );
        }

        // برامج التبرعات الخيرية
        $programs = [
            [
                'category_name' => 'الإعانة الشهرية',
                'title' => 'مساعدة كبار السن',
                'description' => 'برنامج لدعم كبار السن في الحصول على الرعاية الصحية والاحتياجات الأساسية. نساعد في توفير الأدوية والرعاية الطبية والاحتياجات اليومية.',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=600&fit=crop',
                'goal_amount' => 50000,
                'raised_amount' => 35000,
                'start_date' => now(),
                'end_date' => now()->addDays(59),
            ],
            [
                'category_name' => 'السكن والنقل',
                'title' => 'مساعدة الأسر المحتاجة',
                'description' => 'برنامج لدعم الأسر المحتاجة في توفير السكن المناسب ووسائل النقل. نساعد في دفع الإيجارات وتوفير وسائل النقل للطلاب والعائلات.',
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&h=600&fit=crop',
                'goal_amount' => 25000,
                'raised_amount' => 18000,
                'start_date' => now(),
                'end_date' => now()->addDays(45),
            ],
            [
                'category_name' => 'فرص التعليم',
                'title' => 'منح دراسية للطلاب المتفوقين',
                'description' => 'برنامج لتوفير منح دراسية للطلاب المتفوقين من الأسر المحتاجة. نساعد في تغطية الرسوم الدراسية والكتب والمستلزمات التعليمية.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
                'goal_amount' => 75000,
                'raised_amount' => 42000,
                'start_date' => now(),
                'end_date' => now()->addDays(90),
            ],
            [
                'category_name' => 'الرعاية الصحية',
                'title' => 'علاج الأطفال المرضى',
                'description' => 'برنامج لمساعدة الأطفال المرضى في الحصول على العلاج الطبي المناسب. نساعد في تغطية تكاليف العمليات الجراحية والأدوية والرعاية الطبية.',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop',
                'goal_amount' => 100000,
                'raised_amount' => 65000,
                'start_date' => now(),
                'end_date' => now()->addDays(120),
            ],
            [
                'category_name' => 'مساعدة الأسر المحتاجة',
                'title' => 'توفير الطعام للأسر الفقيرة',
                'description' => 'برنامج لتوفير الطعام والمواد الغذائية الأساسية للأسر الفقيرة. نساعد في توزيع السلال الغذائية وتوفير الوجبات اليومية.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop',
                'goal_amount' => 30000,
                'raised_amount' => 22000,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ],
            [
                'category_name' => 'الإعانة الشهرية',
                'title' => 'دعم الأرامل والأيتام',
                'description' => 'برنامج لدعم الأرامل والأيتام في توفير الاحتياجات الأساسية. نساعد في دفع الإيجارات والطعام والملابس والتعليم.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
                'goal_amount' => 60000,
                'raised_amount' => 38000,
                'start_date' => now(),
                'end_date' => now()->addDays(75),
            ],
            [
                'category_name' => 'فرص التعليم',
                'title' => 'تطوير المهارات المهنية',
                'description' => 'برنامج لمساعدة الشباب في تطوير المهارات المهنية والحصول على التدريب المهني. نساعد في توفير دورات تدريبية ومعدات العمل.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
                'goal_amount' => 40000,
                'raised_amount' => 28000,
                'start_date' => now(),
                'end_date' => now()->addDays(60),
            ],
            [
                'category_name' => 'السكن والنقل',
                'title' => 'توفير سكن للطلاب الجامعيين',
                'description' => 'برنامج لتوفير سكن مناسب للطلاب الجامعيين من المناطق النائية. نساعد في دفع إيجارات السكن وتوفير بيئة مناسبة للدراسة.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'goal_amount' => 80000,
                'raised_amount' => 52000,
                'start_date' => now(),
                'end_date' => now()->addDays(100),
            ],
        ];

        foreach ($programs as $programData) {
            $category = Category::where('name', $programData['category_name'])->first();
            
            if ($category) {
                Program::firstOrCreate(
                    [
                        'title' => $programData['title'],
                        'category_id' => $category->id,
                    ],
                    [
                        'category_id' => $category->id,
                        'title' => $programData['title'],
                        'description' => $programData['description'],
                        'image' => $programData['image'],
                        'goal_amount' => $programData['goal_amount'],
                        'raised_amount' => $programData['raised_amount'],
                        'status' => 'active',
                        'start_date' => $programData['start_date'],
                        'end_date' => $programData['end_date'],
                    ]
                );
            }
        }
    }
}
