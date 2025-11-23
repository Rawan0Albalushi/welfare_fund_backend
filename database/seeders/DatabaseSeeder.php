<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in order
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            // ProgramSeeder::class,  // Disabled - only using SupportProgramsSeeder
            SupportProgramsSeeder::class,
            // CharityProgramsSeeder::class,  // Disabled - only using SupportProgramsSeeder
            DonationCampaignsSeeder::class,
            AdminUserSeeder::class,
            StudentRegistrationCardSeeder::class,
            SettingPageSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
