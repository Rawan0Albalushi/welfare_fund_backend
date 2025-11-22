<?php

namespace Database\Seeders;

use App\Models\StudentRegistrationCard;
use Illuminate\Database\Seeder;

class StudentRegistrationCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentRegistrationCard::updateOrCreate(
            [],
            StudentRegistrationCard::defaultPayload()
        );
    }
}

