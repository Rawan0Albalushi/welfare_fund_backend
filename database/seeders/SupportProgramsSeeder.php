<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class SupportProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the support programs
        $supportPrograms = [
            [
                'title_ar' => 'برنامج فرص التعليم العالي',
                'title_en' => 'Higher Education Opportunities Program',
                'description_ar' => 'برنامج لدعم الطلاب في الحصول على فرص التعليم العالي والمنح الدراسية',
                'description_en' => 'A program to support students in obtaining higher education opportunities and scholarships',
                'status' => 'active',
            ],
            [
                'title_ar' => 'برنامج السكن والنقل',
                'title_en' => 'Housing and Transportation Program',
                'description_ar' => 'برنامج لدعم الطلاب في تكاليف السكن والنقل الجامعي',
                'description_en' => 'A program to support students with housing and university transportation costs',
                'status' => 'active',
            ],
            [
                'title_ar' => 'برنامج الإعانة الشهرية',
                'title_en' => 'Monthly Allowance Program',
                'description_ar' => 'برنامج لتقديم إعانات شهرية للطلاب المحتاجين',
                'description_en' => 'A program to provide monthly allowances for students in need',
                'status' => 'active',
            ],
            [
                'title_ar' => 'برنامج رسوم الاختبارات',
                'title_en' => 'Examination Fees Program',
                'description_ar' => 'برنامج لدعم الطلاب في رسوم الاختبارات والامتحانات',
                'description_en' => 'A program to support students with examination and test fees',
                'status' => 'active',
            ],
        ];

		// Create or update the programs (with legacy fallbacks for SQLite testing)
		$hasLegacyTitle = \Schema::hasColumn('programs', 'title');
		$hasLegacyDescription = \Schema::hasColumn('programs', 'description');
		foreach ($supportPrograms as $programData) {
			$payload = $programData;
			if ($hasLegacyTitle && !isset($payload['title'])) {
				$payload['title'] = $programData['title_en'] ?? $programData['title_ar'];
			}
			if ($hasLegacyDescription && !isset($payload['description'])) {
				$payload['description'] = $programData['description_en'] ?? $programData['description_ar'] ?? '';
			}
			Program::updateOrCreate(
				['title_ar' => $programData['title_ar']],
				$payload
			);
		}

        $this->command->info('Support programs seeded successfully!');
    }
}
