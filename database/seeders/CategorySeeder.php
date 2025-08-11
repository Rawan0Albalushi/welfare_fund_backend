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
                'name' => 'Emergency Assistance',
                'status' => 'active',
            ],
            [
                'name' => 'Educational Support',
                'status' => 'active',
            ],
            [
                'name' => 'Medical Aid',
                'status' => 'active',
            ],
            [
                'name' => 'Housing Support',
                'status' => 'active',
            ],
            [
                'name' => 'Transportation',
                'status' => 'active',
            ],
            [
                'name' => 'Technology Access',
                'status' => 'active',
            ],
            [
                'name' => 'Food Security',
                'status' => 'active',
            ],
            [
                'name' => 'Mental Health Support',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
