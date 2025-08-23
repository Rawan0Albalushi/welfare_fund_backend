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
                'status' => 'active',
            ],
            [
                'category_id' => 2, // Educational Support
                'title' => 'Laptop and Technology Fund',
                'description' => 'Helps students acquire laptops and other essential technology for their studies, ensuring they can participate fully in online and hybrid learning.',
                'status' => 'active',
            ],
            [
                'category_id' => 2, // Educational Support
                'title' => 'Textbook and Study Materials',
                'description' => 'Provides textbooks, study materials, and academic resources to students who cannot afford them.',
                'status' => 'active',
            ],
            [
                'category_id' => 3, // Medical Aid
                'title' => 'Medical Treatment Support',
                'description' => 'Assists students with medical treatment costs, including surgeries, medications, and therapy sessions.',
                'status' => 'active',
            ],
            [
                'category_id' => 4, // Housing Support
                'title' => 'Housing Assistance Program',
                'description' => 'Provides financial support for housing costs, including rent, utilities, and emergency accommodation.',
                'status' => 'active',
            ],
            [
                'category_id' => 5, // Transportation
                'title' => 'Transportation Support',
                'description' => 'Helps students with transportation costs, including public transport passes, fuel assistance, and emergency travel.',
                'status' => 'active',
            ],
            [
                'category_id' => 6, // Technology Access
                'title' => 'Internet Connectivity Fund',
                'description' => 'Provides internet connectivity and data packages to students for online learning and research.',
                'status' => 'active',
            ],
            [
                'category_id' => 7, // Food Security
                'title' => 'Food Security Initiative',
                'description' => 'Ensures students have access to nutritious meals through meal vouchers, food packages, and emergency food assistance.',
                'status' => 'active',
            ],
            [
                'category_id' => 8, // Mental Health Support
                'title' => 'Mental Health and Wellness',
                'description' => 'Provides mental health support, counseling services, and wellness programs for students.',
                'status' => 'active',
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
