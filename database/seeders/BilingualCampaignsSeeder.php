<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class BilingualCampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف الحملات الموجودة
        Campaign::query()->delete();

        // بيانات الحملات باللغتين العربية والإنجليزية
        $campaigns = [
            [
                'category_name' => 'حملات الطوارئ',
                'title_ar' => 'حملة إغاثة ضحايا الزلزال',
                'title_en' => 'Earthquake Victims Relief Campaign',
                'description_ar' => 'حملة عاجلة لإغاثة ضحايا الزلزال الذي ضرب المنطقة. نحتاج لتوفير المأوى والطعام والدواء للمتضررين.',
                'description_en' => 'Urgent campaign to relief earthquake victims in the region. We need to provide shelter, food, and medicine for those affected.',
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&h=600&fit=crop',
                'goal_amount' => 200000,
                'raised_amount' => 150000,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'target_donors' => 1000,
                'impact_description_ar' => 'ستساعد هذه الحملة في توفير المأوى والطعام والدواء لـ 500 عائلة متضررة من الزلزال.',
                'impact_description_en' => 'This campaign will help provide shelter, food, and medicine for 500 families affected by the earthquake.',
                'campaign_highlights' => [
                    'توفير مأوى مؤقت لـ 500 عائلة',
                    'توزيع 2000 وجبة يومية',
                    'توفير الأدوية والرعاية الطبية',
                    'إعادة بناء 50 منزل'
                ],
            ],
            [
                'category_name' => 'حملات الطوارئ',
                'title_ar' => 'حملة إغاثة ضحايا الفيضانات',
                'title_en' => 'Flood Victims Relief Campaign',
                'description_ar' => 'حملة عاجلة لإغاثة ضحايا الفيضانات التي اجتاحت المنطقة. نحتاج لتوفير المأوى والطعام والدواء.',
                'description_en' => 'Urgent campaign to relief flood victims in the region. We need to provide shelter, food, and medicine.',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=600&fit=crop',
                'goal_amount' => 150000,
                'raised_amount' => 95000,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(17),
                'target_donors' => 800,
                'impact_description_ar' => 'ستساعد في إغاثة 300 عائلة متضررة من الفيضانات.',
                'impact_description_en' => 'Will help relief 300 families affected by floods.',
                'campaign_highlights' => [
                    'توفير مأوى مؤقت لـ 300 عائلة',
                    'توزيع 1500 وجبة يومية',
                    'توفير الأدوية والرعاية الطبية',
                    'تنظيف المنازل المتضررة'
                ],
            ],
            [
                'category_name' => 'حملات التعليم',
                'title_ar' => 'حملة بناء مدرسة في القرية النائية',
                'title_en' => 'Build a School in Remote Village Campaign',
                'description_ar' => 'حملة لبناء مدرسة في قرية نائية تفتقر للمرافق التعليمية. المدرسة ستخدم 200 طالب وطالبة.',
                'description_en' => 'Campaign to build a school in a remote village lacking educational facilities. The school will serve 200 male and female students.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9e1?w=800&h=600&fit=crop',
                'goal_amount' => 500000,
                'raised_amount' => 320000,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'target_donors' => 2000,
                'impact_description_ar' => 'ستوفر المدرسة التعليم لـ 200 طالب وطالبة في القرية النائية.',
                'impact_description_en' => 'The school will provide education for 200 male and female students in the remote village.',
                'campaign_highlights' => [
                    'بناء مدرسة من 6 فصول دراسية',
                    'توفير مكتبة ومختبر علوم',
                    'إنشاء ملعب رياضي',
                    'توفير وسائل النقل للطلاب'
                ],
            ],
            [
                'category_name' => 'حملات الصحة',
                'title_ar' => 'حملة علاج الأطفال المصابين بالسرطان',
                'title_en' => 'Childhood Cancer Treatment Campaign',
                'description_ar' => 'حملة لعلاج 50 طفل مصاب بالسرطان من الأسر المحتاجة. الحملة تغطي تكاليف العلاج والعمليات الجراحية.',
                'description_en' => 'Campaign to treat 50 children with cancer from needy families. The campaign covers treatment costs and surgical operations.',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop',
                'goal_amount' => 800000,
                'raised_amount' => 450000,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'target_donors' => 3000,
                'impact_description_ar' => 'ستساعد في علاج 50 طفل مصاب بالسرطان وتوفير الأمل لعائلاتهم.',
                'impact_description_en' => 'Will help treat 50 children with cancer and provide hope for their families.',
                'campaign_highlights' => [
                    'علاج 50 طفل مصاب بالسرطان',
                    'توفير الأدوية والعلاج الكيميائي',
                    'إجراء العمليات الجراحية المطلوبة',
                    'توفير الرعاية النفسية للعائلات'
                ],
            ],
            [
                'category_name' => 'حملات الإغاثة',
                'title_ar' => 'حملة إغاثة اللاجئين',
                'title_en' => 'Refugee Relief Campaign',
                'description_ar' => 'حملة لتوفير المساعدات الأساسية للاجئين في المخيمات. تشمل الطعام والملابس والرعاية الصحية.',
                'description_en' => 'Campaign to provide basic assistance to refugees in camps. Includes food, clothing, and healthcare.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop',
                'goal_amount' => 300000,
                'raised_amount' => 180000,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'target_donors' => 1500,
                'impact_description_ar' => 'ستساعد في توفير المساعدات الأساسية لـ 1000 لاجئ في المخيمات.',
                'impact_description_en' => 'Will help provide basic assistance for 1000 refugees in camps.',
                'campaign_highlights' => [
                    'توفير الطعام لـ 1000 لاجئ',
                    'توزيع الملابس والبطاطين',
                    'توفير الرعاية الصحية الأساسية',
                    'إنشاء مراكز تعليمية مؤقتة'
                ],
            ],
            [
                'category_name' => 'حملات البناء',
                'title_ar' => 'حملة بناء مسجد في الحي الفقير',
                'title_en' => 'Build a Mosque in Poor Neighborhood Campaign',
                'description_ar' => 'حملة لبناء مسجد في حي فقير يفتقر للمرافق الدينية. المسجد سيكون مركزاً للعبادة والتعليم.',
                'description_en' => 'Campaign to build a mosque in a poor neighborhood lacking religious facilities. The mosque will be a center for worship and education.',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'goal_amount' => 400000,
                'raised_amount' => 280000,
                'start_date' => now()->subDays(20),
                'end_date' => now()->addDays(40),
                'target_donors' => 1800,
                'impact_description_ar' => 'سيوفر المسجد مكاناً للعبادة والتعليم لـ 500 أسرة في الحي.',
                'impact_description_en' => 'The mosque will provide a place for worship and education for 500 families in the neighborhood.',
                'campaign_highlights' => [
                    'بناء مسجد يتسع لـ 500 مصلي',
                    'إنشاء مكتبة إسلامية',
                    'توفير فصول لتعليم القرآن',
                    'إنشاء مركز اجتماعي'
                ],
            ],
            [
                'category_name' => 'حملات التعليم',
                'title_ar' => 'حملة توفير أجهزة حاسوب للطلاب المحتاجين',
                'title_en' => 'Provide Computers for Needy Students Campaign',
                'description_ar' => 'حملة لتوفير أجهزة حاسوب محمولة للطلاب المحتاجين لتمكينهم من التعليم عن بعد.',
                'description_en' => 'Campaign to provide laptops for needy students to enable them for remote learning.',
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
                'goal_amount' => 250000,
                'raised_amount' => 160000,
                'start_date' => now()->subDays(25),
                'end_date' => now()->addDays(35),
                'target_donors' => 1200,
                'impact_description_ar' => 'ستوفر أجهزة حاسوب لـ 200 طالب محتاج لتمكينهم من التعليم عن بعد.',
                'impact_description_en' => 'Will provide computers for 200 needy students to enable them for remote learning.',
                'campaign_highlights' => [
                    'توفير 200 جهاز حاسوب محمول',
                    'توفير الإنترنت للطلاب',
                    'تدريب الطلاب على استخدام الأجهزة',
                    'توفير الدعم التقني'
                ],
            ],
            [
                'category_name' => 'حملات الصحة',
                'title_ar' => 'حملة فحص طبي مجاني للفقراء',
                'title_en' => 'Free Medical Checkup for the Poor Campaign',
                'description_ar' => 'حملة لتوفير فحص طبي مجاني شامل للفقراء والمحتاجين في المناطق النائية.',
                'description_en' => 'Campaign to provide comprehensive free medical checkup for the poor and needy in remote areas.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
                'goal_amount' => 120000,
                'raised_amount' => 75000,
                'start_date' => now()->subDays(12),
                'end_date' => now()->addDays(28),
                'target_donors' => 900,
                'impact_description_ar' => 'ستوفر فحص طبي مجاني لـ 1000 شخص من الفقراء والمحتاجين.',
                'impact_description_en' => 'Will provide free medical checkup for 1000 poor and needy people.',
                'campaign_highlights' => [
                    'فحص طبي مجاني لـ 1000 شخص',
                    'توفير الأدوية المجانية',
                    'إحالة الحالات الحرجة للمستشفيات',
                    'توفير الرعاية الوقائية'
                ],
            ],
        ];

        foreach ($campaigns as $campaignData) {
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

        echo "تم إنشاء الحملات بنجاح!\n";
        echo "عدد الحملات: " . Campaign::count() . " حملة\n";
    }
}

