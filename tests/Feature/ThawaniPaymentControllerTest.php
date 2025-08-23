<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ThawaniPaymentService;
use Mockery;

class ThawaniPaymentControllerTest extends TestCase
{
    public function test_create_payment_session_success()
    {
        // Mock the ThawaniPaymentService
        $mockService = Mockery::mock(ThawaniPaymentService::class);
        $mockService->shouldReceive('createSession')
            ->once()
            ->andReturn([
                'session_id' => 'sess_test_123',
                'payment_url' => 'https://checkout.thawani.om/pay/sess_test_123?key=pk_test_123'
            ]);

        $this->app->instance(ThawaniPaymentService::class, $mockService);

        $response = $this->postJson('/api/v1/payments/create', [
            'products' => [
                [
                    'name' => 'Test Donation',
                    'quantity' => 1,
                    'unit_amount' => 5000
                ]
            ],
            'client_reference_id' => 'test_ref_123',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'session_id' => 'sess_test_123',
                'payment_url' => 'https://checkout.thawani.om/pay/sess_test_123?key=pk_test_123'
            ]);
    }

    public function test_create_payment_session_validation_error()
    {
        $response = $this->postJson('/api/v1/payments/create', [
            'products' => [],
            'client_reference_id' => '',
            'success_url' => 'invalid-url',
            'cancel_url' => 'invalid-url'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products', 'client_reference_id', 'success_url', 'cancel_url']);
    }

    public function test_get_payment_status_success()
    {
        // Mock the ThawaniPaymentService
        $mockService = Mockery::mock(ThawaniPaymentService::class);
        $mockService->shouldReceive('retrieveSession')
            ->once()
            ->with('sess_test_123')
            ->andReturn([
                'session_id' => 'sess_test_123',
                'payment_status' => 'paid',
                'total_amount' => 5000,
                'client_reference_id' => 'test_ref_123'
            ]);

        $this->app->instance(ThawaniPaymentService::class, $mockService);

        $response = $this->getJson('/api/v1/payments/status/sess_test_123');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'payment_status' => 'paid',
                'raw_response' => [
                    'session_id' => 'sess_test_123',
                    'payment_status' => 'paid',
                    'total_amount' => 5000,
                    'client_reference_id' => 'test_ref_123'
                ]
            ]);
    }

    public function test_get_payment_status_empty_session_id()
    {
        $response = $this->getJson('/api/v1/payments/status/');

        $response->assertStatus(404);
    }

    public function test_create_payment_session_service_exception()
    {
        // Mock the ThawaniPaymentService to throw an exception
        $mockService = Mockery::mock(ThawaniPaymentService::class);
        $mockService->shouldReceive('createSession')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $this->app->instance(ThawaniPaymentService::class, $mockService);

        $response = $this->postJson('/api/v1/payments/create', [
            'products' => [
                [
                    'name' => 'Test Donation',
                    'quantity' => 1,
                    'unit_amount' => 5000
                ]
            ],
            'client_reference_id' => 'test_ref_123',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Service error'
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
