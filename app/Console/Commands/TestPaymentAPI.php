<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestPaymentAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-api {--type=success} {--amount=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test payment API endpoints directly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $amount = (int) $this->option('amount');

        $this->info("🚀 Starting API payment test...");
        $this->info("Type: {$type}");
        $this->info("Amount: {$amount} baisa");

        try {
            // Test 1: Create payment via API
            $this->info("\n📡 Testing payment creation API...");
            $paymentData = $this->testCreatePayment($amount);
            
            if (!$paymentData) {
                $this->error("❌ Payment creation failed!");
                return 1;
            }

            $sessionId = $paymentData['session_id'];
            $paymentUrl = $paymentData['payment_url'];

            $this->info("✅ Payment created successfully!");
            $this->info("Session ID: {$sessionId}");
            $this->info("Payment URL: {$paymentUrl}");

            // Test 2: Check payment status
            $this->info("\n📊 Testing payment status API...");
            $statusData = $this->testPaymentStatus($sessionId);
            
            if ($statusData) {
                $this->info("✅ Status check successful!");
                $this->info("Payment Status: " . ($statusData['payment_status'] ?? 'unknown'));
            } else {
                $this->error("❌ Status check failed!");
            }

            // Test 3: Test both status endpoints
            $this->info("\n🔄 Testing both status endpoints...");
            
            $status1 = $this->testStatusEndpoint("/api/v1/payments/status/{$sessionId}");
            $status2 = $this->testStatusEndpoint("/api/v1/payments/thawani/status/{$sessionId}");
            
            $this->info("Status endpoint 1: " . ($status1 ? '✅' : '❌'));
            $this->info("Status endpoint 2: " . ($status2 ? '✅' : '❌'));

            $this->info("\n🎉 API payment test completed!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('API payment test failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    private function testCreatePayment(int $amount): ?array
    {
        try {
            $response = Http::post('http://192.168.1.15:8000/api/v1/payments/create', [
                'products' => [
                    [
                        'name' => 'تبرع خيري',
                        'quantity' => 1,
                        'unit_amount' => $amount,
                    ]
                ],
                'client_reference_id' => 'api_test_' . time() . '_' . rand(1000, 9999),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info("Response: " . json_encode($data, JSON_PRETTY_PRINT));
                
                if (isset($data['session_id']) && isset($data['payment_url'])) {
                    return [
                        'session_id' => $data['session_id'],
                        'payment_url' => $data['payment_url']
                    ];
                }
            }

            $this->error("API Response: " . $response->body());
            return null;

        } catch (\Exception $e) {
            $this->error("API Error: " . $e->getMessage());
            return null;
        }
    }

    private function testPaymentStatus(string $sessionId): ?array
    {
        try {
            $response = Http::get("http://192.168.1.15:8000/api/v1/payments/status/{$sessionId}");

            if ($response->successful()) {
                $data = $response->json();
                $this->info("Status Response: " . json_encode($data, JSON_PRETTY_PRINT));
                return $data;
            }

            $this->error("Status API Response: " . $response->body());
            return null;

        } catch (\Exception $e) {
            $this->error("Status API Error: " . $e->getMessage());
            return null;
        }
    }

    private function testStatusEndpoint(string $endpoint): bool
    {
        try {
            $response = Http::get("http://192.168.1.15:8000{$endpoint}");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
