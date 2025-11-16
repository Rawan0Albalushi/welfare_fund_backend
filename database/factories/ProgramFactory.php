<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
		$titleAr = $this->faker->words(3, true);
		$titleEn = $this->faker->words(3, true);
		$descAr  = $this->faker->paragraph();
		$descEn  = $this->faker->paragraph();

		$data = [
			'title_ar' => $titleAr,
			'title_en' => $titleEn,
			'description_ar' => $descAr,
			'description_en' => $descEn,
			'image' => null,
			'status' => $this->faker->randomElement(['draft', 'active', 'paused', 'archived']),
		];
		// Legacy fallbacks for SQLite tests when columns still exist
		if (\Schema::hasColumn('programs', 'title')) {
			$data['title'] = $titleEn;
		}
		if (\Schema::hasColumn('programs', 'description')) {
			$data['description'] = $descEn;
		}
		return $data;
    }

    /**
     * Indicate that the program is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the program is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }
}
