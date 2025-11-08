<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'title_ar' => 'المساعدة المالية الطارئة',
                'title_en' => 'Emergency Financial Aid',
                'description_ar' => 'يوفر مساعدة مالية فورية للطلاب الذين يواجهون حالات طوارئ غير متوقعة مثل الأزمات العائلية، الحوادث، أو الاحتياجات الطبية العاجلة.',
                'description_en' => 'Provides immediate financial assistance to students facing unexpected emergencies such as family crises, accidents, or urgent medical needs.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'صندوق الكمبيوتر المحمول والتكنولوجيا',
                'title_en' => 'Laptop and Technology Fund',
                'description_ar' => 'يساعد الطلاب في الحصول على أجهزة الكمبيوتر المحمولة والتكنولوجيا الأساسية الأخرى للدراسة، مما يضمن مشاركتهم الكاملة في التعلم الإلكتروني والهجين.',
                'description_en' => 'Helps students acquire laptops and other essential technology for their studies, ensuring they can participate fully in online and hybrid learning.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'الكتب والمواد الدراسية',
                'title_en' => 'Textbook and Study Materials',
                'description_ar' => 'يوفر الكتب الدراسية والمواد الدراسية والموارد الأكاديمية للطلاب الذين لا يستطيعون تحمل تكلفتها.',
                'description_en' => 'Provides textbooks, study materials, and academic resources to students who cannot afford them.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'دعم العلاج الطبي',
                'title_en' => 'Medical Treatment Support',
                'description_ar' => 'يساعد الطلاب في تكاليف العلاج الطبي، بما في ذلك العمليات الجراحية والأدوية وجلسات العلاج.',
                'description_en' => 'Assists students with medical treatment costs, including surgeries, medications, and therapy sessions.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'برنامج المساعدة في السكن',
                'title_en' => 'Housing Assistance Program',
                'description_ar' => 'يوفر دعماً مالياً لتكاليف السكن، بما في ذلك الإيجار والمرافق والإقامة الطارئة.',
                'description_en' => 'Provides financial support for housing costs, including rent, utilities, and emergency accommodation.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'دعم النقل',
                'title_en' => 'Transportation Support',
                'description_ar' => 'يساعد الطلاب في تكاليف النقل، بما في ذلك بطاقات النقل العام ومساعدات الوقود والسفر الطارئ.',
                'description_en' => 'Helps students with transportation costs, including public transport passes, fuel assistance, and emergency travel.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'صندوق الاتصال بالإنترنت',
                'title_en' => 'Internet Connectivity Fund',
                'description_ar' => 'يوفر الاتصال بالإنترنت وحزم البيانات للطلاب للتعلم الإلكتروني والبحث.',
                'description_en' => 'Provides internet connectivity and data packages to students for online learning and research.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'مبادرة الأمن الغذائي',
                'title_en' => 'Food Security Initiative',
                'description_ar' => 'يضمن حصول الطلاب على وجبات مغذية من خلال قسائم الوجبات وحزم الطعام والمساعدة الغذائية الطارئة.',
                'description_en' => 'Ensures students have access to nutritious meals through meal vouchers, food packages, and emergency food assistance.',
                'status' => 'active',
            ],
            [
                'title_ar' => 'الصحة النفسية والعافية',
                'title_en' => 'Mental Health and Wellness',
                'description_ar' => 'يوفر الدعم الصحي النفسي وخدمات الاستشارة وبرامج العافية للطلاب.',
                'description_en' => 'Provides mental health support, counseling services, and wellness programs for students.',
                'status' => 'active',
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
