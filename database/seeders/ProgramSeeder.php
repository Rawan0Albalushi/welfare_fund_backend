<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Category;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'category_id' => 1, // Emergency Assistance
                'title' => 'Emergency Financial Aid',
                'description' => 'Provides immediate financial assistance to students facing unexpected emergencies such as family crises, accidents, or urgent medical needs.',
                'goal_amount' => 50000.00,
                'raised_amount' => 12500.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 2, // Educational Support
                'title' => 'Laptop and Technology Fund',
                'description' => 'Helps students acquire laptops and other essential technology for their studies, ensuring they can participate fully in online and hybrid learning.',
                'goal_amount' => 75000.00,
                'raised_amount' => 32000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 2, // Educational Support
                'title' => 'Textbook and Study Materials',
                'description' => 'Provides textbooks, study materials, and academic resources to students who cannot afford them.',
                'goal_amount' => 30000.00,
                'raised_amount' => 8500.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 3, // Medical Aid
                'title' => 'Medical Treatment Support',
                'description' => 'Assists students with medical treatment costs, including surgeries, medications, and therapy sessions.',
                'goal_amount' => 100000.00,
                'raised_amount' => 45000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 4, // Housing Support
                'title' => 'Housing Assistance Program',
                'description' => 'Provides financial support for housing costs, including rent, utilities, and emergency accommodation.',
                'goal_amount' => 80000.00,
                'raised_amount' => 28000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 5, // Transportation
                'title' => 'Transportation Support',
                'description' => 'Helps students with transportation costs, including public transport passes, fuel assistance, and emergency travel.',
                'goal_amount' => 25000.00,
                'raised_amount' => 12000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 6, // Technology Access
                'title' => 'Internet Connectivity Fund',
                'description' => 'Provides internet connectivity and data packages to students for online learning and research.',
                'goal_amount' => 20000.00,
                'raised_amount' => 8000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 7, // Food Security
                'title' => 'Food Security Initiative',
                'description' => 'Ensures students have access to nutritious meals through meal vouchers, food packages, and emergency food assistance.',
                'goal_amount' => 40000.00,
                'raised_amount' => 15000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
            [
                'category_id' => 8, // Mental Health Support
                'title' => 'Mental Health and Wellness',
                'description' => 'Provides mental health support, counseling services, and wellness programs for students.',
                'goal_amount' => 35000.00,
                'raised_amount' => 18000.00,
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
