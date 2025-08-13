<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Program;
use App\Models\StudentRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_latest_registration_status()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a program
        $program = Program::factory()->create([
            'title' => 'Test Program'
        ]);
        
        // Create a registration for the user
        $registration = StudentRegistration::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'under_review',
            'reject_reason' => null,
        ]);
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Make the request
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
                 // Assert the response
         $response->assertStatus(200)
             ->assertJsonStructure([
                 'message',
                 'data' => [
                     'id',
                     'registration_id',
                     'status',
                     'rejection_reason',
                     'personal' => [
                         'full_name',
                         'student_id',
                         'email',
                         'phone',
                         'gender'
                     ],
                     'academic' => [
                         'university',
                         'college',
                         'major',
                         'program',
                         'academic_year',
                         'gpa'
                     ],
                     'financial' => [
                         'income_level',
                         'family_size'
                     ],
                     'program' => [
                         'id',
                         'title'
                     ],
                     'created_at',
                     'updated_at'
                 ]
             ])
                         ->assertJson([
                 'message' => 'Registration status retrieved successfully',
                 'data' => [
                     'id' => $registration->id,
                     'status' => 'under_review',
                     'rejection_reason' => null,
                     'personal' => $registration->personal_json,
                     'academic' => $registration->academic_json,
                     'financial' => $registration->financial_json,
                     'program' => [
                         'id' => $program->id,
                         'title' => 'Test Program'
                     ]
                 ]
             ]);
    }

    public function test_user_gets_latest_registration_when_multiple_exist()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a program
        $program = Program::factory()->create();
        
        // Create multiple registrations for the user
        $oldRegistration = StudentRegistration::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'rejected',
            'reject_reason' => 'Old reason',
            'created_at' => now()->subDays(5),
        ]);
        
        $latestRegistration = StudentRegistration::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'accepted',
            'reject_reason' => null,
            'created_at' => now(),
        ]);
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Make the request
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
        // Assert the response returns the latest registration
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $latestRegistration->id,
                    'status' => 'accepted',
                    'rejection_reason' => null,
                ]
            ]);
    }

    public function test_user_gets_rejection_reason_when_registration_is_rejected()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a program
        $program = Program::factory()->create();
        
        // Create a rejected registration
        $registration = StudentRegistration::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'rejected',
            'reject_reason' => 'Incomplete documentation provided',
        ]);
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Make the request
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'rejected',
                    'rejection_reason' => 'Incomplete documentation provided',
                ]
            ]);
    }

    public function test_returns_404_when_user_has_no_registrations()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Authenticate the user
        Sanctum::actingAs($user);
        
        // Make the request
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
        // Assert the response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No registration found',
                'data' => null
            ]);
    }

    public function test_unauthenticated_user_cannot_access_endpoint()
    {
        // Make the request without authentication
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
        // Assert the response
        $response->assertStatus(401);
    }

    public function test_user_cannot_access_other_users_registration()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create a program
        $program = Program::factory()->create();
        
        // Create a registration for user2
        $registration = StudentRegistration::factory()->create([
            'user_id' => $user2->id,
            'program_id' => $program->id,
            'status' => 'under_review',
        ]);
        
        // Authenticate user1
        Sanctum::actingAs($user1);
        
        // Make the request
        $response = $this->getJson('/api/v1/students/registration/my-registration');
        
        // Assert the response - user1 should get 404 since they have no registrations
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No registration found',
                'data' => null
            ]);
    }
}
