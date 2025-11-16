<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class DonationCampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء فئات حملات التبرع إذا لم تكن موجودة
        $campaignCategories = [
            [
                'name_ar' => 'دعم الرسوم الجامعية',
                'name_en' => 'University Tuition Support',
            ],
            [
                'name_ar' => 'منح دراسية',
                'name_en' => 'Scholarships',
            ],
            [
                'name_ar' => 'سكن طلابي',
                'name_en' => 'Student Housing',
            ],
            [
                'name_ar' => 'مواصلات طلاب',
                'name_en' => 'Student Transportation',
            ],
            [
                'name_ar' => 'مستلزمات دراسية',
                'name_en' => 'Study Supplies',
            ],
            [
                'name_ar' => 'صندوق طوارئ طلابي',
                'name_en' => 'Student Emergency Fund',
            ],
        ];

        foreach ($campaignCategories as $categoryData) {
            Category::firstOrCreate(
                ['name_ar' => $categoryData['name_ar']],
                [
                    'name_ar' => $categoryData['name_ar'],
                    'name_en' => $categoryData['name_en'],
                    'status' => 'active',
                ]
            );
        }

        // حملات لدعم طلاب جامعيين
        $campaigns = [
            [
                'category_name_ar' => 'دعم الرسوم الجامعية',
                'title_ar' => 'تسديد رسوم دراسية لـ 100 طالب جامعي محتاج',
                'title_en' => 'Pay Tuition Fees for 100 Needy University Students',
                'description_ar' => 'تغطية جزئية/كاملة للرسوم الدراسية لطلاب جامعيين غير قادرين على السداد مع أولوية للأيتام وأبناء الأسر محدودة الدخل.',
                'description_en' => 'Partial/full tuition coverage for university students unable to pay, prioritizing orphans and low-income families.',
                'image' => 'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?w=800&h=600&fit=crop',
                'goal_amount' => 300000,
                'raised_amount' => 120000,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(45),
                'target_donors' => 1500,
                'impact_description_ar' => 'تمكين 100 طالب جامعي من الاستمرار في تعليمهم الجامعي دون انقطاع بسبب الرسوم.',
                'impact_description_en' => 'Enable 100 university students to continue their education without interruption due to tuition fees.',
                'campaign_highlights' => [
                    'أولوية لطلاب السنوات النهائية',
                    'آلية تحقق شفافة مع الجامعات',
                    'تغطية جزئية بمتوسط 3000 ريال للطالب',
                    'تقارير تقدم شهرية للمتبرعين'
                ],
            ],
            [
                'category_name_ar' => 'منح دراسية',
                'title_ar' => 'منح تفوق لطلاب الهندسة والحوسبة',
                'title_en' => 'Merit Scholarships for Engineering and Computing Students',
                'description_ar' => 'تقديم منح دراسية تنافسية لطلاب متميزين أكاديميًا في كليات الهندسة والحوسبة لتغطية الرسوم والكتب.',
                'description_en' => 'Competitive scholarships for academically outstanding engineering and computing students covering tuition and books.',
                'image' => 'https://images.unsplash.com/photo-1513258496099-48168024aec0?w=800&h=600&fit=crop',
                'goal_amount' => 200000,
                'raised_amount' => 85000,
                'start_date' => now()->subDays(14),
                'end_date' => now()->addDays(60),
                'target_donors' => 900,
                'impact_description_ar' => 'تمويل 50 منحة دراسية بمتوسط 4000 ريال لتشجيع التميز العلمي.',
                'impact_description_en' => 'Fund 50 scholarships averaging 4000 SAR to encourage academic excellence.',
                'campaign_highlights' => [
                    'اختيار عبر لجنة أكاديمية',
                    'تغطية رسوم ومواد علمية',
                    'إشراك القطاع الخاص للتدريب التعاوني',
                    'نسبة مخصصة للطالبات'
                ],
            ],
            [
                'category_name_ar' => 'سكن طلابي',
                'title_ar' => 'دعم إسكان طلابي لذوي الدخل المحدود',
                'title_en' => 'Housing Support for Low-Income University Students',
                'description_ar' => 'مساعدة طلاب الجامعات في تغطية تكاليف السكن بالقرب من الحرم الجامعي لتقليل التسرب وتحسين الأداء.',
                'description_en' => 'Assist university students with accommodation costs near campus to reduce dropout and improve performance.',
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&h=600&fit=crop',
                'goal_amount' => 250000,
                'raised_amount' => 110000,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(35),
                'target_donors' => 1100,
                'impact_description_ar' => 'تأمين سكن ملائم لـ 60 طالبًا جامعيًا قريب من الجامعة لمدة فصل دراسي.',
                'impact_description_en' => 'Provide suitable housing for 60 university students near campus for one semester.',
                'campaign_highlights' => [
                    'اتفاقيات مع مجمعات سكنية',
                    'دعم الإيجار الشهري حتى 1500 ريال',
                    'أولوية للأسر البعيدة جغرافيًا',
                    'متابعة اجتماعية دورية'
                ],
            ],
            [
                'category_name_ar' => 'مواصلات طلاب',
                'title_ar' => 'بطاقات نقل عام مدعومة لطلاب الجامعات',
                'title_en' => 'Subsidized Public Transport Cards for University Students',
                'description_ar' => 'توفير بطاقات نقل عام مدعومة لتسهيل وصول الطلاب إلى الحرم الجامعي وتقليل العبء المالي.',
                'description_en' => 'Provide subsidized public transport cards to ease student commute to campus and reduce financial burden.',
                'image' => 'https://images.unsplash.com/photo-1517420704952-3b7b36a18d83?w=800&h=600&fit=crop',
                'goal_amount' => 120000,
                'raised_amount' => 40000,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'target_donors' => 700,
                'impact_description_ar' => 'تقديم 500 بطاقة نقل عام بخصم 70% طوال الفصل.',
                'impact_description_en' => 'Provide 500 public transport cards with 70% discount throughout the term.',
                'campaign_highlights' => [
                    'شراكات مع شركات النقل',
                    'بطاقات إلكترونية قابلة للشحن',
                    'أولوية للطلاب ذوي الإعاقة',
                    'نظام تتبع للاستخدام'
                ],
            ],
            [
                'category_name_ar' => 'مستلزمات دراسية',
                'title_ar' => 'حواسيب محمولة ومراجع لطلاب الجامعات المحتاجين',
                'title_en' => 'Laptops and References for Needy University Students',
                'description_ar' => 'تزويد طلاب جامعيين بأجهزة حاسوب محمولة ومراجع دراسية رقمية لتعزيز التعلم والبحث.',
                'description_en' => 'Provide laptops and digital references to needy university students to enhance learning and research.',
                'image' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&h=600&fit=crop',
                'goal_amount' => 180000,
                'raised_amount' => 60000,
                'start_date' => now()->subDays(20),
                'end_date' => now()->addDays(40),
                'target_donors' => 800,
                'impact_description_ar' => 'توفير 150 جهازًا واشتراكات رقمية للمراجع الأساسية.',
                'impact_description_en' => 'Provide 150 devices and digital subscriptions for essential references.',
                'campaign_highlights' => [
                    'أولوية للتخصصات العلمية',
                    'ورش تدريب على مهارات الدراسة الرقمية',
                    'دعم تقني خلال العام الدراسي',
                    'اتفاقيات مع مزودي المحتوى'
                ],
            ],
            [
                'category_name_ar' => 'صندوق طوارئ طلابي',
                'title_ar' => 'صندوق طوارئ لمساعدة الطلاب في الحالات العاجلة',
                'title_en' => 'Emergency Fund for Students in Urgent Cases',
                'description_ar' => 'صندوق مرن للتدخل السريع في حالات الطوارئ التي يتعرض لها الطلاب مثل فقدان معيل أو ظرف صحي مفاجئ.',
                'description_en' => 'Flexible fund for rapid intervention in student emergencies such as loss of a provider or sudden health issues.',
                'image' => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&h=600&fit=crop',
                'goal_amount' => 220000,
                'raised_amount' => 90000,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(30),
                'target_donors' => 1000,
                'impact_description_ar' => 'معالجة 200 حالة طارئة لضمان استمرار الطلاب في التعليم.',
                'impact_description_en' => 'Handle 200 emergency cases to ensure students continue their studies.',
                'campaign_highlights' => [
                    'صرف فوري خلال 72 ساعة',
                    'سقف مساعدة حتى 3000 ريال للحالة',
                    'توثيق واستحقاق واضح',
                    'تعاون مع شؤون الطلاب بالجامعات'
                ],
            ],
        ];

		$hasLegacyTitle = \Schema::hasColumn('campaigns', 'title');
		$hasLegacyDescription = \Schema::hasColumn('campaigns', 'description');
		foreach ($campaigns as $campaignData) {
            $category = Category::where('name_ar', $campaignData['category_name_ar'])->first();
            
            if ($category) {
				$payload = [
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
				];
				// Legacy fallbacks for SQLite (columns not dropped)
				if ($hasLegacyTitle && !isset($payload['title'])) {
					$payload['title'] = $campaignData['title_en'] ?? $campaignData['title_ar'];
				}
				if ($hasLegacyDescription && !isset($payload['description'])) {
					$payload['description'] = $campaignData['description_en'] ?? $campaignData['description_ar'] ?? '';
				}
                Campaign::firstOrCreate(
                    [
                        'title_ar' => $campaignData['title_ar'],
                        'category_id' => $category->id,
                    ],
					$payload
                );
            }
        }
    }
}
