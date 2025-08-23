<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThawaniPaymentService;

class TestThawaniPaymentService extends Command
{
    protected $signature = 'thawani:test-service {--amount=1.0} {--reference=test}';
    protected $description = 'Test the new ThawaniPaymentService';

    public function handle()
    {
        $this->info('🔍 Testing ThawaniPaymentService...');
        
        // Check configuration
        $secretKey = env('THAWANI_SECRET_KEY');
        $publishableKey = env('THAWANI_PUBLISHABLE_KEY');
        
        $this->line("Secret Key: " . substr($secretKey, 0, 10) . "...");
        $this->line("Publishable Key: " . substr($publishableKey, 0, 10) . "...");
        
        // Validate keys
        if (empty($secretKey) || $secretKey === 'YOUR_SECRET_KEY_HERE') {
            $this->error('❌ THAWANI_SECRET_KEY is not configured!');
            $this->line('Please add to your .env file:');
            $this->line('THAWANI_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx');
            return 1;
        }
        
        if (empty($publishableKey) || $publishableKey === 'YOUR_PUBLISHABLE_KEY_HERE') {
            $this->error('❌ THAWANI_PUBLISHABLE_KEY is not configured!');
            $this->line('Please add to your .env file:');
            $this->line('THAWANI_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxx');
            return 1;
        }
        
        $amount = $this->option('amount');
        $reference = $this->option('reference') . '_' . time();
        $amountInBaisa = (int)($amount * 1000);
        
        // Prepare products array
        $products = [
            [
                'name' => 'Test Donation',
                'quantity' => 1,
                'unit_amount' => $amountInBaisa,
            ]
        ];
        
        $this->info("\n🧪 Testing createSession...");
        $this->line("Amount: {$amount} OMR ({$amountInBaisa} baisa)");
        $this->line("Reference: {$reference}");
        $this->line("Products: " . json_encode($products, JSON_PRETTY_PRINT));
        
        try {
            $thawaniService = new ThawaniPaymentService();
            
            $result = $thawaniService->createSession(
                $products,
                $reference,
                'https://example.com/success',
                'https://example.com/cancel'
            );
            
            $this->info("✅ createSession successful!");
            $this->line("Session ID: {$result['session_id']}");
            $this->line("Payment URL: {$result['payment_url']}");
            
            // Test retrieveSession
            $this->info("\n📡 Testing retrieveSession...");
            
            $sessionData = $thawaniService->retrieveSession($result['session_id']);
            
            $this->info("✅ retrieveSession successful!");
            $this->line("Payment Status: " . ($sessionData['payment_status'] ?? 'unknown'));
            $this->line("Full Response: " . json_encode($sessionData, JSON_PRETTY_PRINT));
            
        } catch (\Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            return 1;
        }
        
        $this->info("\n🎉 All tests completed successfully!");
        return 0;
    }
}
