<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThawaniService;
use Illuminate\Support\Facades\Log;

class TestPaymentFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:payment-flow {--type=success} {--amount=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test payment flow with Thawani - success or failure scenarios';

    private ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        parent::__construct();
        $this->thawaniService = $thawaniService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $amount = (int) $this->option('amount');

        $this->info("🚀 Starting payment flow test...");
        $this->info("Type: {$type}");
        $this->info("Amount: {$amount} baisa");

        try {
            // Step 1: Test connection
            $this->info("\n📡 Testing Thawani connection...");
            $connectionTest = $this->thawaniService->testConnection();
            if ($connectionTest) {
                $this->info("✅ Connection successful!");
            } else {
                $this->error("❌ Connection failed!");
                return 1;
            }

            // Step 2: Create payment session
            $this->info("\n💳 Creating payment session...");
            $clientReferenceId = 'test_' . time() . '_' . rand(1000, 9999);
            
            $products = [
                [
                    'name' => 'تبرع خيري',
                    'quantity' => 1,
                    'unit_amount' => $amount,
                ]
            ];

            $successUrl = config('services.thawani.success_url');
            $cancelUrl = config('services.thawani.cancel_url');

            $session = $this->thawaniService->createSession(
                $clientReferenceId,
                $products,
                $successUrl,
                $cancelUrl
            );

            $this->info("✅ Session created successfully!");
            $this->info("Session ID: {$session['session_id']}");
            $this->info("Payment URL: {$session['payment_url']}");

            // Step 3: Check initial status
            $this->info("\n📊 Checking initial payment status...");
            $initialStatus = $this->thawaniService->getSessionDetails($session['session_id']);
            $this->info("Initial Status: " . ($initialStatus['payment_status'] ?? 'unknown'));

            // Step 4: Simulate different scenarios
            if ($type === 'success') {
                $this->simulateSuccessfulPayment($session['session_id']);
            } else {
                $this->simulateFailedPayment($session['session_id']);
            }

            // Step 5: Final status check
            $this->info("\n📊 Checking final payment status...");
            $finalStatus = $this->thawaniService->getSessionDetails($session['session_id']);
            $this->info("Final Status: " . ($finalStatus['payment_status'] ?? 'unknown'));

            $this->info("\n🎉 Payment flow test completed!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('Payment flow test failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    private function simulateSuccessfulPayment(string $sessionId): void
    {
        $this->info("\n✅ Simulating successful payment...");
        $this->info("In a real scenario, the user would complete the payment on Thawani's website.");
        $this->info("For testing, we'll wait a moment and check the status...");
        
        sleep(2);
        
        // In real scenario, this would be triggered by webhook
        $this->info("Payment simulation completed. Status should be 'paid' if webhook is configured.");
    }

    private function simulateFailedPayment(string $sessionId): void
    {
        $this->info("\n❌ Simulating failed payment...");
        $this->info("In a real scenario, the user would cancel or payment would fail.");
        $this->info("For testing, we'll wait a moment and check the status...");
        
        sleep(2);
        
        $this->info("Payment failure simulation completed. Status should be 'cancelled' or 'failed'.");
    }
}
