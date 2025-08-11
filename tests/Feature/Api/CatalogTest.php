<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Program;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_categories(): void
    {
        // Create a test category
        Category::create([
            'name' => 'Test Category',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'status',
                            'programs_count',
                            'created_at',
                            'updated_at',
                        ]
                    ]
                ]);
    }

    public function test_can_get_programs(): void
    {
        // Create a test category and program
        $category = Category::create([
            'name' => 'Test Category',
            'status' => 'active',
        ]);

        Program::create([
            'category_id' => $category->id,
            'title' => 'Test Program',
            'description' => 'Test Description',
            'goal_amount' => 10000.00,
            'raised_amount' => 5000.00,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/programs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'goal_amount',
                            'raised_amount',
                            'progress_percentage',
                            'status',
                            'category',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ]
                ]);
    }

    public function test_can_get_specific_program(): void
    {
        // Create a test category and program
        $category = Category::create([
            'name' => 'Test Category',
            'status' => 'active',
        ]);

        $program = Program::create([
            'category_id' => $category->id,
            'title' => 'Test Program',
            'description' => 'Test Description',
            'goal_amount' => 10000.00,
            'raised_amount' => 5000.00,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/programs/{$program->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'goal_amount',
                        'raised_amount',
                        'progress_percentage',
                        'status',
                        'category',
                        'created_at',
                        'updated_at',
                    ]
                ]);
    }

    public function test_can_filter_programs_by_category(): void
    {
        // Create test categories and programs
        $category1 = Category::create(['name' => 'Category 1', 'status' => 'active']);
        $category2 = Category::create(['name' => 'Category 2', 'status' => 'active']);

        Program::create([
            'category_id' => $category1->id,
            'title' => 'Program 1',
            'description' => 'Description 1',
            'goal_amount' => 10000.00,
            'raised_amount' => 5000.00,
            'status' => 'active',
        ]);

        Program::create([
            'category_id' => $category2->id,
            'title' => 'Program 2',
            'description' => 'Description 2',
            'goal_amount' => 20000.00,
            'raised_amount' => 10000.00,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/programs?category_id={$category1->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Program 1', $response->json('data.0.title'));
    }

    public function test_can_search_programs(): void
    {
        // Create a test category and programs
        $category = Category::create(['name' => 'Test Category', 'status' => 'active']);

        Program::create([
            'category_id' => $category->id,
            'title' => 'Laptop Fund',
            'description' => 'Technology support for students',
            'goal_amount' => 10000.00,
            'raised_amount' => 5000.00,
            'status' => 'active',
        ]);

        Program::create([
            'category_id' => $category->id,
            'title' => 'Food Security',
            'description' => 'Meal assistance program',
            'goal_amount' => 20000.00,
            'raised_amount' => 10000.00,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/programs?search=laptop');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('Laptop Fund', $response->json('data.0.title'));
    }
}
