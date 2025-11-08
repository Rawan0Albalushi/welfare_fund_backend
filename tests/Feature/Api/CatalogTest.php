<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_categories(): void
    {
        Category::create([
            'name_ar' => 'فئة تجريبية',
            'name_en' => 'Test Category',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name_ar',
                        'name_en',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_can_get_programs(): void
    {
        Program::factory()->create([
            'title_ar' => 'برنامج تجريبي',
            'title_en' => 'Test Program',
            'description_ar' => 'وصف تجريبي',
            'description_en' => 'Test Description',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/programs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title_ar',
                        'title_en',
                        'description_ar',
                        'description_en',
                        'image',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonFragment([
                'title_en' => 'Test Program',
                'status' => 'active',
            ]);
    }

    public function test_can_get_specific_program(): void
    {
        $program = Program::factory()->create([
            'title_ar' => 'برنامج تجريبي',
            'title_en' => 'Test Program',
            'description_ar' => 'وصف تجريبي',
            'description_en' => 'Test Description',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/programs/{$program->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $program->id,
                'title_en' => 'Test Program',
            ]);
    }

    public function test_can_search_programs(): void
    {
        Program::factory()->create([
            'title_ar' => 'صندوق الحواسيب',
            'title_en' => 'Laptop Fund',
            'description_ar' => 'دعم تقني للطلاب',
            'description_en' => 'Technology support for students',
            'status' => 'active',
        ]);

        Program::factory()->create([
            'title_ar' => 'الأمن الغذائي',
            'title_en' => 'Food Security',
            'description_ar' => 'برنامج لدعم الوجبات',
            'description_en' => 'Meal assistance program',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/programs?search=laptop');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title_en' => 'Laptop Fund',
            ]);
    }
}
