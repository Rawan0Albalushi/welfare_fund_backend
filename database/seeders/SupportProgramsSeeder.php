<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Category;

class SupportProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create the support category
        $supportCategory = Category::firstOrCreate([
            'name' => 'برامج الدعم الطلابي'
        ], [
            'status' => 'active'
        ]);

        // Define the support programs
        $supportPrograms = [
            [
                'title' => 'برنامج فرص التعليم العالي',
                'description' => 'برنامج لدعم الطلاب في الحصول على فرص التعليم العالي والمنح الدراسية',
                'goal_amount' => 500000,
                'raised_amount' => 0,
                'status' => 'active',
                'category_id' => $supportCategory->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ],
            [
                'title' => 'برنامج السكن والنقل',
                'description' => 'برنامج لدعم الطلاب في تكاليف السكن والنقل الجامعي',
                'goal_amount' => 300000,
                'raised_amount' => 0,
                'status' => 'active',
                'category_id' => $supportCategory->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ],
            [
                'title' => 'برنامج الاعانة الشهرية',
                'description' => 'برنامج لتقديم إعانات شهرية للطلاب المحتاجين',
                'goal_amount' => 200000,
                'raised_amount' => 0,
                'status' => 'active',
                'category_id' => $supportCategory->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ],
            [
                'title' => 'رسوم الاختبارات',
                'description' => 'برنامج لدعم الطلاب في رسوم الاختبارات والامتحانات',
                'goal_amount' => 150000,
                'raised_amount' => 0,
                'status' => 'active',
                'category_id' => $supportCategory->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ],
        ];

        // Create or update the programs
        foreach ($supportPrograms as $programData) {
            Program::updateOrCreate(
                ['title' => $programData['title']],
                $programData
            );
        }

        $this->command->info('Support programs seeded successfully!');
    }
}
