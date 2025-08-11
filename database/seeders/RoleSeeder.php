<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Categories
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            
            // Programs
            'view_programs',
            'create_programs',
            'edit_programs',
            'delete_programs',
            
            // Donations
            'view_donations',
            'create_donations',
            'edit_donations',
            'delete_donations',
            
            // Applications
            'view_applications',
            'create_applications',
            'edit_applications',
            'delete_applications',
            'review_applications',
            
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $reviewerRole = Role::create(['name' => 'reviewer']);
        $userRole = Role::create(['name' => 'user']);

        // Assign permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to reviewer role
        $reviewerRole->givePermissionTo([
            'view_categories',
            'view_programs',
            'view_donations',
            'view_applications',
            'review_applications',
            'edit_applications',
        ]);

        // Assign permissions to user role
        $userRole->givePermissionTo([
            'view_categories',
            'view_programs',
            'create_donations',
            'create_applications',
            'view_applications', // Only their own applications
        ]);
    }
}
