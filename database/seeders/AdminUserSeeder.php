<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array of users to create
        $users = [
            // Admin users
            [
                'phone' => '+966500000000',
                'name' => 'System Admin',
                'email' => 'admin@example.com',
                'password' => 'admin12345',
                'role' => 'admin',
            ],
            [
                'phone' => '+966500000001',
                'name' => 'محمد أحمد',
                'email' => 'admin1@example.com',
                'password' => 'admin12345',
                'role' => 'admin',
            ],
            [
                'phone' => '+966500000002',
                'name' => 'فاطمة علي',
                'email' => 'admin2@example.com',
                'password' => 'admin12345',
                'role' => 'admin',
            ],
            
            // Reviewer users
            [
                'phone' => '+966500000010',
                'name' => 'خالد سعيد',
                'email' => 'reviewer1@example.com',
                'password' => 'reviewer12345',
                'role' => 'reviewer',
            ],
            [
                'phone' => '+966500000011',
                'name' => 'سارة محمد',
                'email' => 'reviewer2@example.com',
                'password' => 'reviewer12345',
                'role' => 'reviewer',
            ],
            
            // Regular users
            [
                'phone' => '+966500000100',
                'name' => 'عبدالله حسن',
                'email' => 'user1@example.com',
                'password' => 'user12345',
                'role' => 'user',
            ],
            [
                'phone' => '+966500000101',
                'name' => 'نورا إبراهيم',
                'email' => 'user2@example.com',
                'password' => 'user12345',
                'role' => 'user',
            ],
            [
                'phone' => '+966500000102',
                'name' => 'أحمد يوسف',
                'email' => 'user3@example.com',
                'password' => 'user12345',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['phone' => $userData['phone']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'settings' => ['notifications' => true],
                ]
            );

            // Ensure role is assigned using Spatie Permission
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        // Output success message if running from command line
        if ($this->command) {
            $this->command->info('✓ ' . count($users) . ' users created successfully!');
        }
    }
}


