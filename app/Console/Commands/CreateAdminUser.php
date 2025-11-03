<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--name= : Admin user name}
                            {--phone= : Admin phone number}
                            {--email= : Admin email address}
                            {--password= : Admin password}
                            {--role=admin : User role (admin, reviewer, user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating admin user...');

        // Get or prompt for user details
        $name = $this->option('name') ?: $this->ask('Enter admin name');
        $phone = $this->option('phone') ?: $this->ask('Enter phone number');
        $email = $this->option('email') ?: $this->ask('Enter email address (optional)', null);
        $password = $this->option('password') ?: $this->secret('Enter password (min 8 characters)');
        $role = $this->option('role') ?: 'admin';

        // Validate inputs
        $validator = Validator::make([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ], [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|regex:/^[0-9+\-\s()]+$/',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,reviewer,user',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - ' . $error);
            }
            return Command::FAILURE;
        }

        try {
            // Create user
            $user = User::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $role,
                'settings' => ['notifications' => true],
            ]);

            // Assign role using Spatie Permission
            $user->assignRole($role);

            $this->info('✓ Admin user created successfully!');
            $this->line('');
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Phone', $user->phone],
                    ['Email', $user->email ?? 'N/A'],
                    ['Role', $role],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

