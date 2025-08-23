<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThawaniService;
use Exception;

class TestThawaniService extends Command
{
    protected $signature = 'thawani:test {--amount=1.0} {--reference=test}';
    protected $description = 'Test ThawaniService with session creation and details retrieval';

    public function handle()
    {
        $this->info('🧪 Testing ThawaniService...');

        try {
            $thawaniService = new ThawaniService();
            
            // Test 1: Connection test
            $this->info("\n🔍 Test 1: Testing connection...");
            try {
                $isConnected = $thawaniService->testConnection();
                if ($isConnected) {
                    $this->info("✅ Connection successful!");
                } else {
                    $this->warn("⚠️ Connection test returned false");
                }
            } catch (Exception $e) {
                $this->error("❌ Connection failed: " . $e->getMessage());
            }

            // Test 2: Create session
            $this->info("\n🔍 Test 2: Creating payment session...");
            
            $amount = $this->option('amount');
            $reference = $this->option('reference');
            $clientReferenceId = $reference . '_' . time();
            
            $products = [
                [
                    'name' => 'Test Donation',
                    'quantity' => 1,
                    'unit_amount' => (int)($amount * 1000), // Convert OMR to baisa
                ]
            ];

            $this->line("Amount: {$amount} OMR (" . (int)($amount * 1000) . " baisa)");
            $this->line("Reference: {$clientReferenceId}");
            $this->line("Products: " . json_encode($products, JSON_PRETTY_PRINT));

            $sessionData = $thawaniService->createSession(
                $clientReferenceId,
                $products,
                'https://example.com/success',
                'https://example.com/cancel'
            );

            $this->info("✅ Session created successfully!");
            $this->line("Session ID: " . ($sessionData['session_id'] ?? 'N/A'));
            $this->line("Payment URL: " . ($sessionData['payment_url'] ?? 'N/A'));
            $this->line("Full Response: " . json_encode($sessionData, JSON_PRETTY_PRINT));

            // Test 3: Get session details
            if (isset($sessionData['session_id'])) {
                $this->info("\n🔍 Test 3: Getting session details...");
                
                $sessionDetails = $thawaniService->getSessionDetails($sessionData['session_id']);
                
                $this->info("✅ Session details retrieved successfully!");
                $this->line("Payment Status: " . ($sessionDetails['payment_status'] ?? 'N/A'));
                $this->line("Client Reference ID: " . ($sessionDetails['client_reference_id'] ?? 'N/A'));
                $this->line("Full Response: " . json_encode($sessionDetails, JSON_PRETTY_PRINT));
            } else {
                $this->warn("⚠️ Skipping session details test - no session_id in response");
            }

            $this->info("\n🎉 All tests completed successfully!");
            return 0;

        } catch (Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            return 1;
        }
    }
}
