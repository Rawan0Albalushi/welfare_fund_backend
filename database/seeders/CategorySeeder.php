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
                'name_ar' => 'فرص التعليم العالي',
                'name_en' => 'Higher Education Opportunities',
                'status' => 'active',
            ],
            [
                'name_ar' => 'السكن والنقل',
                'name_en' => 'Housing and Transportation',
                'status' => 'active',
            ],
            [
                'name_ar' => 'الإعانة الشهرية',
                'name_en' => 'Monthly Allowance',
                'status' => 'active',
            ],
            [
                'name_ar' => 'رسوم الاختبارات',
                'name_en' => 'Examination Fees',
                'status' => 'active',
            ],
            [
                'name_ar' => 'المساعدات العاجلة',
                'name_en' => 'Urgent Assistance',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
