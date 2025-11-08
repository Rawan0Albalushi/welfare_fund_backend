<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Campaign;
use App\Models\StudentRegistration;
use App\Models\StudentApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DonationResource;
use App\Http\Resources\StudentRegistrationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        try {
            $schema = DB::getSchemaBuilder();

            $totalDonations = $schema->hasTable('donations') ? (int) Donation::count() : 0;
            $totalPrograms = $schema->hasTable('programs') ? (int) Program::count() : 0;
            $activePrograms = $schema->hasTable('programs') ? (int) Program::where('status', 'active')->count() : 0;
            $totalCampaigns = $schema->hasTable('campaigns') ? (int) Campaign::count() : 0;

            // Sum paid donation amounts; fall back to 0 if table not present
            $donationsAmount = $schema->hasTable('donations') ? (float) Donation::where('status', 'paid')->sum('amount') : 0.0;

            $pendingApplications = 0;
            if ($schema->hasTable('student_applications')) {
                $pendingApplications = (int) StudentApplication::where('status', 'under_review')->count();
            } elseif ($schema->hasTable('student_registrations')) {
                $pendingApplications = (int) StudentRegistration::where('status', 'under_review')->count();
            }
        } catch (\Throwable $e) {
            \Log::error('Admin stats error', ['error' => $e->getMessage()]);
            $totalDonations = 0;
            $totalPrograms = 0;
            $activePrograms = 0;
            $totalCampaigns = 0;
            $donationsAmount = 0.0;
            $pendingApplications = 0;
        }

        return response()->json([
            'data' => [
                'total_donations' => (int) $totalDonations,
                'total_amount' => (float) $donationsAmount,
                'total_programs' => (int) ($totalPrograms ?? 0),
                'active_programs' => (int) ($activePrograms ?? 0),
                'total_campaigns' => (int) ($totalCampaigns ?? 0),
                'pending_applications' => (int) $pendingApplications,
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $recentDonations = Donation::with([
                    'user:id,name,phone',
                    'program:id,title_ar,title_en',
                    'campaign:id,title_ar,title_en',
                    'giftMeta',
                ])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $recentApplications = StudentRegistration::with(['user:id,name,phone', 'program:id,title_ar,title_en'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            \Log::error('Admin dashboard error', ['error' => $e->getMessage()]);
            $recentDonations = collect();
            $recentApplications = collect();
        }

        return response()->json([
            'data' => [
                'recent_donations' => DonationResource::collection($recentDonations),
                'recent_applications' => StudentRegistrationResource::collection($recentApplications),
            ],
        ]);
    }

    public function ping(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ['ok' => true],
        ]);
    }
}


