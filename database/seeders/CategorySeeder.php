<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_ar' => 'المساعدات الطارئة',
                'name_en' => 'Emergency Assistance',
                'status' => 'active',
            ],
            [
                'name_ar' => 'الدعم التعليمي',
                'name_en' => 'Educational Support',
                'status' => 'active',
            ],
            [
                'name_ar' => 'المساعدات الطبية',
                'name_en' => 'Medical Aid',
                'status' => 'active',
            ],
            [
                'name_ar' => 'دعم السكن',
                'name_en' => 'Housing Support',
                'status' => 'active',
            ],
            [
                'name_ar' => 'المواصلات',
                'name_en' => 'Transportation',
                'status' => 'active',
            ],
            [
                'name_ar' => 'الوصول للتكنولوجيا',
                'name_en' => 'Technology Access',
                'status' => 'active',
            ],
            [
                'name_ar' => 'الأمن الغذائي',
                'name_en' => 'Food Security',
                'status' => 'active',
            ],
            [
                'name_ar' => 'دعم الصحة النفسية',
                'name_en' => 'Mental Health Support',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
