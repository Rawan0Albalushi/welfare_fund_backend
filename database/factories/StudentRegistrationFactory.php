<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Program;
use App\Models\StudentRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentRegistration>
 */
class StudentRegistrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentRegistration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'program_id' => Program::factory(),
            'personal_json' => [
                'full_name' => $this->faker->name(),
                'student_id' => $this->faker->regexify('[A-Z]{2}[0-9]{6}'),
                'email' => $this->faker->email(),
                'phone' => $this->faker->phoneNumber(),
                'gender' => $this->faker->randomElement(['male', 'female']),
            ],
            'academic_json' => [
                'university' => $this->faker->company(),
                'college' => $this->faker->word(),
                'major' => $this->faker->word(),
                'program' => $this->faker->sentence(3),
                'academic_year' => $this->faker->numberBetween(1, 5),
                'gpa' => $this->faker->randomFloat(2, 2.0, 4.0),
            ],
            'financial_json' => [
                'income_level' => $this->faker->randomElement(['low', 'medium', 'high']),
                'family_size' => $this->faker->randomElement(['1-3', '4-6', '7-9', '10+']),
            ],
            'status' => $this->faker->randomElement(['under_review', 'accepted', 'rejected']),
            'reject_reason' => null,
            'id_card_image' => null,
        ];
    }

    /**
     * Indicate that the registration is under review.
     */
    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_review',
            'reject_reason' => null,
        ]);
    }

    /**
     * Indicate that the registration is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'reject_reason' => null,
        ]);
    }

    /**
     * Indicate that the registration is rejected.
     */
    public function rejected(string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reject_reason' => $reason ?? $this->faker->sentence(),
        ]);
    }
}
