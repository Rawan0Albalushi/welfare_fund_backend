<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Program;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class UpdateDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف جميع البيانات الموجودة بطريقة آمنة
        Program::query()->delete();
        Campaign::query()->delete();
        Category::query()->delete();

        // إنشاء فئات برامج الدعم الطلابي (4 فئات فقط)
        $supportCategories = [
            [
                'name_ar' => 'الإعانة الشهرية',
                'name_en' => 'Monthly Allowance',
            ],
            [
                'name_ar' => 'السكن والنقل',
                'name_en' => 'Housing and Transportation',
            ],
            [
                'name_ar' => 'فرص التعليم العالي',
                'name_en' => 'Higher Education Opportunities',
            ],
            [
                'name_ar' => 'رسوم الاختبارات',
                'name_en' => 'Examination Fees',
            ],
        ];

        foreach ($supportCategories as $category) {
            Category::create([
                'name_ar' => $category['name_ar'],
                'name_en' => $category['name_en'],
                'status' => 'active',
            ]);
        }

        // إنشاء فئات حملات التبرع الخيرية
        $campaignCategories = [
            [
                'name_ar' => 'حملات الطوارئ',
                'name_en' => 'Emergency Campaigns',
            ],
            [
                'name_ar' => 'حملات التعليم',
                'name_en' => 'Education Campaigns',
            ],
            [
                'name_ar' => 'حملات الصحة',
                'name_en' => 'Health Campaigns',
            ],
            [
                'name_ar' => 'حملات الإغاثة',
                'name_en' => 'Relief Campaigns',
            ],
            [
                'name_ar' => 'حملات البناء',
                'name_en' => 'Construction Campaigns',
            ],
        ];

        foreach ($campaignCategories as $category) {
            Category::create([
                'name_ar' => $category['name_ar'],
                'name_en' => $category['name_en'],
                'status' => 'active',
            ]);
        }

        // إنشاء برامج الدعم الطلابي (4 برامج فقط كما في الشرح)
        $supportPrograms = [
            [
                'category_name' => 'الإعانة الشهرية',
                'title' => 'برنامج الإعانة الشهرية',
                'description' => 'برنامج مخصص لتقديم دعم مالي شهري للطلاب المحتاجين. يساعد الطلاب في تغطية المصاريف الأساسية مثل الطعام والملابس والكتب الدراسية.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
                'goal_amount' => 100000,
                'raised_amount' => 75000,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(6),
            ],
            [
                'category_name' => 'السكن والنقل',
                'title' => 'برنامج السكن والنقل',
                'description' => 'برنامج يساعد الطلاب في توفير سكن مناسب ووسائل النقل. يشمل دعم إيجار السكن وتذاكر المواصلات للطلاب المحتاجين.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'goal_amount' => 150000,
                'raised_amount' => 120000,
                'start_date' => now()->subMonths(4),
                'end_date' => now()->addMonths(8),
            ],
            [
                'category_name' => 'فرص التعليم العالي',
                'title' => 'برنامج فرص التعليم العالي',
                'description' => 'برنامج يهدف إلى توفير فرص تعليمية للطلاب المتفوقين. يشمل منح دراسية وبرامج تطوير المهارات والتدريب المهني.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
                'goal_amount' => 200000,
                'raised_amount' => 160000,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
            ],
            [
                'category_name' => 'رسوم الاختبارات',
                'title' => 'رسوم الاختبارات',
                'description' => 'برنامج لتغطية رسوم الاختبارات والامتحانات للطلاب المحتاجين. يساعد الطلاب في دفع رسوم الاختبارات الدولية والمحلية.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
                'goal_amount' => 80000,
                'raised_amount' => 60000,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
            ],
        ];

        foreach ($supportPrograms as $programData) {
            $category = Category::where('name_ar', $programData['category_name'])->first();
            
            if ($category) {
                Program::create([
                    'category_id' => $category->id,
                    'title' => $programData['title'],
                    'description' => $programData['description'],
                    'image' => $programData['image'],
                    'status' => 'active',
                ]);
            }
        }

        // إنشاء حملات التبرع الخيرية (للصفحة الرئيسية)
        $donationCampaigns = [
            [
                'category_name' => 'حملات الطوارئ',
                'title' => 'حملة إغاثة ضحايا الزلزال',
                'description' => 'حملة عاجلة لإغاثة ضحايا الزلزال الذي ضرب المنطقة. نحتاج لتوفير المأوى والطعام والدواء للمتضررين.',
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&h=600&fit=crop',
                'goal_amount' => 200000,
                'raised_amount' => 150000,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'target_donors' => 1000,
                'impact_description' => 'ستساعد هذه الحملة في توفير المأوى والطعام والدواء لـ 500 عائلة متضررة من الزلزال.',
                'campaign_highlights' => [
                    'توفير مأوى مؤقت لـ 500 عائلة',
                    'توزيع 2000 وجبة يومية',
                    'توفير الأدوية والرعاية الطبية',
                    'إعادة بناء 50 منزل'
                ],
            ],
            [
                'category_name' => 'حملات الطوارئ',
                'title' => 'حملة إغاثة ضحايا الفيضانات',
                'description' => 'حملة عاجلة لإغاثة ضحايا الفيضانات التي اجتاحت المنطقة. نحتاج لتوفير المأوى والطعام والدواء.',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=600&fit=crop',
                'goal_amount' => 150000,
                'raised_amount' => 95000,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(17),
                'target_donors' => 800,
                'impact_description' => 'ستساعد في إغاثة 300 عائلة متضررة من الفيضانات.',
                'campaign_highlights' => [
                    'توفير مأوى مؤقت لـ 300 عائلة',
                    'توزيع 1500 وجبة يومية',
                    'توفير الأدوية والرعاية الطبية',
                    'تنظيف المنازل المتضررة'
                ],
            ],
            [
                'category_name' => 'حملات التعليم',
                'title' => 'حملة بناء مدرسة في القرية النائية',
                'description' => 'حملة لبناء مدرسة في قرية نائية تفتقر للمرافق التعليمية. المدرسة ستخدم 200 طالب وطالبة.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
                'goal_amount' => 500000,
                'raised_amount' => 320000,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'target_donors' => 2000,
                'impact_description' => 'ستوفر المدرسة التعليم لـ 200 طالب وطالبة في القرية النائية.',
                'campaign_highlights' => [
                    'بناء مدرسة من 6 فصول دراسية',
                    'توفير مكتبة ومختبر علوم',
                    'إنشاء ملعب رياضي',
                    'توفير وسائل النقل للطلاب'
                ],
            ],
            [
                'category_name' => 'حملات الصحة',
                'title' => 'حملة علاج الأطفال المصابين بالسرطان',
                'description' => 'حملة لعلاج 50 طفل مصاب بالسرطان من الأسر المحتاجة. الحملة تغطي تكاليف العلاج والعمليات الجراحية.',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop',
                'goal_amount' => 800000,
                'raised_amount' => 450000,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'target_donors' => 3000,
                'impact_description' => 'ستساعد في علاج 50 طفل مصاب بالسرطان وتوفير الأمل لعائلاتهم.',
                'campaign_highlights' => [
                    'علاج 50 طفل مصاب بالسرطان',
                    'توفير الأدوية والعلاج الكيميائي',
                    'إجراء العمليات الجراحية المطلوبة',
                    'توفير الرعاية النفسية للعائلات'
                ],
            ],
            [
                'category_name' => 'حملات الإغاثة',
                'title' => 'حملة إغاثة اللاجئين',
                'description' => 'حملة لتوفير المساعدات الأساسية للاجئين في المخيمات. تشمل الطعام والملابس والرعاية الصحية.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop',
                'goal_amount' => 300000,
                'raised_amount' => 180000,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'target_donors' => 1500,
                'impact_description' => 'ستساعد في توفير المساعدات الأساسية لـ 1000 لاجئ في المخيمات.',
                'campaign_highlights' => [
                    'توفير الطعام لـ 1000 لاجئ',
                    'توزيع الملابس والبطاطين',
                    'توفير الرعاية الصحية الأساسية',
                    'إنشاء مراكز تعليمية مؤقتة'
                ],
            ],
            [
                'category_name' => 'حملات البناء',
                'title' => 'حملة بناء مسجد في الحي الفقير',
                'description' => 'حملة لبناء مسجد في حي فقير يفتقر للمرافق الدينية. المسجد سيكون مركزاً للعبادة والتعليم.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'goal_amount' => 400000,
                'raised_amount' => 280000,
                'start_date' => now()->subDays(20),
                'end_date' => now()->addDays(40),
                'target_donors' => 1800,
                'impact_description' => 'سيوفر المسجد مكاناً للعبادة والتعليم لـ 500 أسرة في الحي.',
                'campaign_highlights' => [
                    'بناء مسجد يتسع لـ 500 مصلي',
                    'إنشاء مكتبة إسلامية',
                    'توفير فصول لتعليم القرآن',
                    'إنشاء مركز اجتماعي'
                ],
            ],
            [
                'category_name' => 'حملات التعليم',
                'title' => 'حملة توفير أجهزة حاسوب للطلاب المحتاجين',
                'description' => 'حملة لتوفير أجهزة حاسوب محمولة للطلاب المحتاجين لتمكينهم من التعليم عن بعد.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
                'goal_amount' => 250000,
                'raised_amount' => 160000,
                'start_date' => now()->subDays(25),
                'end_date' => now()->addDays(35),
                'target_donors' => 1200,
                'impact_description' => 'ستوفر أجهزة حاسوب لـ 200 طالب محتاج لتمكينهم من التعليم عن بعد.',
                'campaign_highlights' => [
                    'توفير 200 جهاز حاسوب محمول',
                    'توفير الإنترنت للطلاب',
                    'تدريب الطلاب على استخدام الأجهزة',
                    'توفير الدعم التقني'
                ],
            ],
            [
                'category_name' => 'حملات الصحة',
                'title' => 'حملة فحص طبي مجاني للفقراء',
                'description' => 'حملة لتوفير فحص طبي مجاني شامل للفقراء والمحتاجين في المناطق النائية.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
                'goal_amount' => 120000,
                'raised_amount' => 75000,
                'start_date' => now()->subDays(12),
                'end_date' => now()->addDays(28),
                'target_donors' => 900,
                'impact_description' => 'ستوفر فحص طبي مجاني لـ 1000 شخص من الفقراء والمحتاجين.',
                'campaign_highlights' => [
                    'فحص طبي مجاني لـ 1000 شخص',
                    'توفير الأدوية المجانية',
                    'إحالة الحالات الحرجة للمستشفيات',
                    'توفير الرعاية الوقائية'
                ],
            ],
        ];

        foreach ($donationCampaigns as $campaignData) {
            $category = Category::where('name_ar', $campaignData['category_name'])->first();
            
            if ($category) {
                Campaign::create([
                    'category_id' => $category->id,
                    'title_ar' => $campaignData['title_ar'],
                    'title_en' => $campaignData['title_en'],
                    'description_ar' => $campaignData['description_ar'],
                    'description_en' => $campaignData['description_en'],
                    'image' => $campaignData['image'],
                    'goal_amount' => $campaignData['goal_amount'],
                    'raised_amount' => $campaignData['raised_amount'],
                    'status' => 'active',
                    'start_date' => $campaignData['start_date'],
                    'end_date' => $campaignData['end_date'],
                    'target_donors' => $campaignData['target_donors'],
                    'impact_description_ar' => $campaignData['impact_description_ar'],
                    'impact_description_en' => $campaignData['impact_description_en'],
                    'campaign_highlights' => $campaignData['campaign_highlights'],
                ]);
            }
        }

        echo "تم تحديث البيانات بنجاح!\n";
        echo "برامج الدعم الطلابي: " . Program::count() . " برامج\n";
        echo "حملات التبرع الخيرية: " . Campaign::count() . " حملات\n";
        echo "الفئات: " . Category::count() . " فئات\n";
    }
}
