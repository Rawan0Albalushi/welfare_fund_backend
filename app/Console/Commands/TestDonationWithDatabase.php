<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Campaign;

class TestDonationWithDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:donation-db {--amount=1000} {--type=success}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test donation creation with database storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $amount = (int) $this->option('amount');
        $type = $this->option('type');

        $this->info("🎯 Testing Donation with Database Storage");
        $this->info("=========================================");
        $this->info("Amount: {$amount} baisa");
        $this->info("Type: {$type}");

        try {
            // Step 1: Check if we have programs or campaigns
            $programs = Program::active()->take(3)->get();
            $campaigns = Campaign::active()->take(3)->get();

            $this->info("\n📋 Available Programs:");
            foreach ($programs as $program) {
                $this->info("- ID: {$program->id}, Name: {$program->name}");
            }

            $this->info("\n📋 Available Campaigns:");
            foreach ($campaigns as $campaign) {
                $this->info("- ID: {$campaign->id}, Name: {$campaign->name}");
            }

            if ($programs->isEmpty() && $campaigns->isEmpty()) {
                $this->error("❌ No active programs or campaigns found!");
                return 1;
            }

            // Step 2: Create donation via API
            $this->info("\n💳 Creating donation via API...");
            
            $payload = [
                'products' => [
                    [
                        'name' => 'تبرع خيري',
                        'quantity' => 1,
                        'unit_amount' => $amount,
                    ]
                ],
                'client_reference_id' => 'db_test_' . time() . '_' . rand(1000, 9999),
                'donor_name' => 'مختبر النظام',
                'note' => 'اختبار حفظ التبرع في قاعدة البيانات',
                'type' => 'quick',
            ];

            // Add program_id or campaign_id
            if (!$programs->isEmpty()) {
                $payload['program_id'] = $programs->first()->id;
                $this->info("Using Program ID: {$programs->first()->id}");
            } elseif (!$campaigns->isEmpty()) {
                $payload['campaign_id'] = $campaigns->first()->id;
                $this->info("Using Campaign ID: {$campaigns->first()->id}");
            }

            $response = Http::post('http://localhost:8000/api/v1/payments/create', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Donation created successfully!");
                
                $this->info("\n📊 API Response:");
                $this->info("Message: " . $data['message']);
                $this->info("Session ID: " . $data['session_id']);
                $this->info("Payment URL: " . $data['payment_url']);
                
                if (isset($data['data']['donation'])) {
                    $donation = $data['data']['donation'];
                    $this->info("\n📋 Donation Details:");
                    $this->info("Donation ID: " . $donation['donation_id']);
                    $this->info("Amount: " . $donation['amount'] . " OMR");
                    $this->info("Donor Name: " . $donation['donor_name']);
                    $this->info("Status: " . $donation['status']);
                    $this->info("Type: " . $donation['type']);
                }

                // Step 3: Verify donation in database
                $this->info("\n🔍 Verifying donation in database...");
                
                if (isset($data['data']['donation']['id'])) {
                    $donationId = $data['data']['donation']['id'];
                    $donationFromDb = Donation::find($donationId);
                    
                    if ($donationFromDb) {
                        $this->info("✅ Donation found in database!");
                        $this->info("Database ID: " . $donationFromDb->id);
                        $this->info("Donation ID: " . $donationFromDb->donation_id);
                        $this->info("Amount: " . $donationFromDb->amount . " OMR");
                        $this->info("Status: " . $donationFromDb->status);
                        $this->info("Payment Session ID: " . $donationFromDb->payment_session_id);
                        $this->info("Payment URL: " . $donationFromDb->payment_url);
                        $this->info("Created At: " . $donationFromDb->created_at);
                    } else {
                        $this->error("❌ Donation not found in database!");
                    }
                }

                // Step 4: Check payment status
                $this->info("\n📊 Checking payment status...");
                $sessionId = $data['session_id'];
                $statusResponse = Http::get("http://localhost:8000/api/v1/payments/status/{$sessionId}");
                
                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    $this->info("Payment Status: " . $statusData['payment_status']);
                } else {
                    $this->error("❌ Failed to check payment status");
                }

                // Step 5: Show total donations count
                $this->info("\n📈 Database Statistics:");
                $totalDonations = Donation::count();
                $pendingDonations = Donation::where('status', 'pending')->count();
                $paidDonations = Donation::where('status', 'paid')->count();
                
                $this->info("Total Donations: {$totalDonations}");
                $this->info("Pending Donations: {$pendingDonations}");
                $this->info("Paid Donations: {$paidDonations}");

            } else {
                $this->error("❌ Failed to create donation!");
                $this->error("Status: " . $response->status());
                $this->error("Response: " . $response->body());
            }

            $this->info("\n🎉 Donation with database test completed!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('Donation database test failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
