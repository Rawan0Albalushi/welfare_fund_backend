<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\GiftMeta;
use App\Models\Program;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DonationsService
{
    /**
     * Create a quick donation.
     */
    public function createQuickDonation(array $data, ?int $userId = null): Donation
    {
        return DB::transaction(function () use ($data, $userId) {
            $program = Program::findOrFail($data['program_id']);
            
            $donation = Donation::create([
                'program_id' => $data['program_id'],
                'amount' => $data['amount'],
                'donor_name' => $data['donor_name'] ?? 'Anonymous',
                'type' => 'quick',
                'status' => 'pending',
                'user_id' => $userId,
                'note' => $data['note'] ?? null,
                'expires_at' => Carbon::now()->addHours(24), // 24 hours expiry
            ]);

            // Log the donation creation
            AuditLog::create([
                'event_type' => 'donation_created',
                'entity_type' => 'Donation',
                'entity_id' => $donation->id,
                'payload' => $data,
                'user_id' => $userId,
            ]);

            return $donation;
        });
    }

    /**
     * Create a gift donation.
     */
    public function createGiftDonation(array $data, ?int $userId = null): Donation
    {
        return DB::transaction(function () use ($data, $userId) {
            $program = Program::findOrFail($data['program_id']);
            
            $donation = Donation::create([
                'program_id' => $data['program_id'],
                'amount' => $data['amount'],
                'donor_name' => $data['sender']['name'] ?? 'Anonymous',
                'type' => 'gift',
                'status' => 'pending',
                'user_id' => $userId,
                'expires_at' => Carbon::now()->addHours(24), // 24 hours expiry
            ]);

            // Create gift meta
            GiftMeta::create([
                'donation_id' => $donation->id,
                'recipient_name' => $data['recipient']['name'],
                'recipient_phone' => $data['recipient']['phone'],
                'message' => $data['recipient']['message'] ?? null,
                'sender_name' => $data['sender']['name'] ?? null,
                'sender_phone' => $data['sender']['phone'] ?? null,
                'hide_identity' => $data['sender']['hide_identity'] ?? false,
            ]);

            // Log the gift donation creation
            AuditLog::create([
                'event_type' => 'gift_donation_created',
                'entity_type' => 'Donation',
                'entity_id' => $donation->id,
                'payload' => $data,
                'user_id' => $userId,
            ]);

            return $donation;
        });
    }

    /**
     * Process payment webhook.
     */
    public function processPaymentWebhook(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $donation = Donation::where('donation_id', $data['donation_id'])->first();
            
            if (!$donation) {
                return false;
            }

            // Update donation status
            $donation->update([
                'status' => $data['status'],
                'payload' => $data,
                'paid_at' => $data['status'] === 'paid' ? Carbon::now() : null,
            ]);

            // If payment is successful, update program raised amount
            if ($data['status'] === 'paid') {
                $program = $donation->program;
                $program->increment('raised_amount', $donation->amount);
            }

            // Log the webhook
            AuditLog::create([
                'event_type' => 'payment_webhook',
                'entity_type' => 'Donation',
                'entity_id' => $donation->id,
                'payload' => $data,
            ]);

            return true;
        });
    }

    /**
     * Check donation status.
     */
    public function getDonationStatus(string $donationId): ?array
    {
        $donation = Donation::where('donation_id', $donationId)->first();
        
        if (!$donation) {
            return null;
        }

        return [
            'status' => $donation->status,
            'amount' => $donation->amount,
            'type' => $donation->type,
            'expires_at' => $donation->expires_at?->toISOString(),
            'paid_at' => $donation->paid_at?->toISOString(),
        ];
    }

    /**
     * Get recent donations for public display.
     */
    public function getRecentDonations(int $limit = 10): array
    {
        $donations = Donation::with(['program:id,title'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->limit($limit)
            ->get();

        return $donations->map(function ($donation) {
            return [
                'donor_name' => $donation->donor_name,
                'amount' => $donation->amount,
                'program_title' => $donation->program->title,
                'paid_at' => $donation->paid_at?->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Check for duplicate donation using idempotency key.
     */
    public function checkDuplicateDonation(string $idempotencyKey): ?Donation
    {
        $hash = Hash::make($idempotencyKey);
        
        // Check if we have a recent donation with this hash
        $recentDonation = Donation::where('payload->idempotency_hash', $hash)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        return $recentDonation;
    }
}
