<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Campaign;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DonationsExport;
use App\Exports\FinancialExport;
use App\Exports\ProgramsExport;
use App\Exports\ApplicationsExport;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;

class ReportController extends Controller
{
    /**
     * تقرير التبرعات المفصل مع الفلترة
     * 
     * GET /api/v1/admin/reports/donations
     * 
     * Query Parameters:
     * - from_date: تاريخ البداية (Y-m-d)
     * - to_date: تاريخ النهاية (Y-m-d)
     * - status: حالة التبرع (pending, paid, failed, expired)
     * - type: نوع التبرع (quick, gift)
     * - program_id: فلترة حسب البرنامج
     * - campaign_id: فلترة حسب الحملة
     * - export: true/false - تصدير البيانات
     */
    public function donations(Request $request): JsonResponse
    {
        $query = Donation::with(['user:id,name,phone', 'program:id,title_ar,title_en', 'campaign:id,title_ar,title_en']);

        // فلترة حسب التاريخ
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // فلترة حسب الحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب النوع
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب البرنامج
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // فلترة حسب الحملة
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        // إحصائيات سريعة - إنشاء استعلامات منفصلة لتجنب تضارب الفلترات
        $baseQuery = Donation::query();
        
        // تطبيق نفس الفلترات على الاستعلام الأساسي
        if ($request->has('from_date')) {
            $baseQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $baseQuery->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->has('program_id')) {
            $baseQuery->where('program_id', $request->program_id);
        }
        if ($request->has('campaign_id')) {
            $baseQuery->where('campaign_id', $request->campaign_id);
        }
        
        $stats = [
            'total_count' => $query->count(),
            'total_amount' => (float) $query->sum('amount'),
            'paid_count' => (int) (clone $baseQuery)->where('status', 'paid')->count(),
            'paid_amount' => (float) (clone $baseQuery)->where('status', 'paid')->sum('amount'),
            'pending_count' => (int) (clone $baseQuery)->where('status', 'pending')->count(),
            'pending_amount' => (float) (clone $baseQuery)->where('status', 'pending')->sum('amount'),
            'failed_count' => (int) (clone $baseQuery)->where('status', 'failed')->count(),
            'expired_count' => (int) (clone $baseQuery)->where('status', 'expired')->count(),
        ];

        // الحصول على البيانات
        $perPage = $request->get('per_page', 50);
        $donations = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'message' => 'تم جلب تقرير التبرعات بنجاح',
            'data' => $donations->items(),
            'stats' => $stats,
            'meta' => [
                'current_page' => $donations->currentPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
                'last_page' => $donations->lastPage(),
            ],
        ]);
    }

    /**
     * تقرير مالي شامل
     * 
     * GET /api/v1/admin/reports/financial
     * 
     * Query Parameters:
     * - period: daily, weekly, monthly, yearly, custom
     * - from_date: تاريخ البداية (Y-m-d)
     * - to_date: تاريخ النهاية (Y-m-d)
     */
    public function financial(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->subMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());
        $period = $request->get('period', 'monthly');

        // إجمالي التبرعات
        $totalDonations = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('amount');

        // التبرعات المدفوعة
        $paidDonations = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'paid')
            ->sum('amount');

        // التبرعات المعلقة
        $pendingDonations = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'pending')
            ->sum('amount');

        // عدد المتبرعين (فقط التبرعات التي لها user_id)
        $donorsCount = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'paid')
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // التبرعات المجهولة
        $anonymousCount = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->whereNull('user_id')
            ->where('status', 'paid')
            ->count();

        // متوسط التبرع
        $averageDonation = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'paid')
            ->avg('amount');

        // التبرعات حسب الحالة
        $donationsByStatus = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->get();

        // التبرعات حسب النوع
        $donationsByType = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('type')
            ->get();

        // التبرعات حسب الفترة الزمنية
        $donationsOverTime = [];
        $dateFormat = 'Y-m-d';
        
        if ($period === 'daily') {
            $dateFormat = 'Y-m-d';
            $donationsOverTime = Donation::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', 'paid')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();
        } elseif ($period === 'weekly') {
            $donationsOverTime = Donation::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', 'paid')
                ->select(DB::raw('YEAR(created_at) as year'), DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('WEEK(created_at)'))
                ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
                ->orderBy(DB::raw('WEEK(created_at)'), 'asc')
                ->get();
        } elseif ($period === 'monthly') {
            $donationsOverTime = Donation::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', 'paid')
                ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
                ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
                ->get();
        }

        return response()->json([
            'message' => 'تم جلب التقرير المالي بنجاح',
            'data' => [
                'period' => [
                    'from' => $fromDate,
                    'to' => $toDate,
                    'type' => $period,
                ],
                'summary' => [
                    'total_donations' => (float) $totalDonations,
                    'paid_donations' => (float) $paidDonations,
                    'pending_donations' => (float) $pendingDonations,
                    'donors_count' => (int) $donorsCount,
                    'anonymous_count' => (int) $anonymousCount,
                    'average_donation' => (float) round($averageDonation ?? 0, 2),
                ],
                'by_status' => $donationsByStatus,
                'by_type' => $donationsByType,
                'over_time' => $donationsOverTime,
            ],
        ]);
    }

    /**
     * تقرير البرامج مع الإحصائيات
     * 
     * GET /api/v1/admin/reports/programs
     */
    public function programs(Request $request): JsonResponse
    {
        $programs = Program::withCount([
            'donations',
            'donations as paid_donations_count' => function ($query) {
                $query->where('status', 'paid');
            },
            'studentRegistrations',
            'studentRegistrations as pending_registrations_count' => function ($query) {
                $query->where('status', 'under_review');
            },
        ])
        ->withSum([
            'donations as total_donations_amount' => function ($query) {
                $query->where('status', 'paid');
            }
        ], 'amount')
        ->get()
        ->map(function ($program) {
            return [
                'id' => $program->id,
                'title_ar' => $program->title_ar,
                'title_en' => $program->title_en,
                'status' => $program->status,
                'donations_count' => $program->donations_count,
                'paid_donations_count' => $program->paid_donations_count,
                'total_amount' => (float) ($program->total_donations_amount ?? 0),
                'registrations_count' => $program->student_registrations_count,
                'pending_registrations_count' => $program->pending_registrations_count,
                'created_at' => $program->created_at,
            ];
        });

        $totalStats = [
            'total_programs' => $programs->count(),
            'total_donations' => $programs->sum('donations_count'),
            'total_amount' => (float) $programs->sum('total_amount'),
            'total_registrations' => $programs->sum('registrations_count'),
        ];

        return response()->json([
            'message' => 'تم جلب تقرير البرامج بنجاح',
            'data' => $programs,
            'summary' => $totalStats,
        ]);
    }

    /**
     * تقرير الحملات مع الإحصائيات
     * 
     * GET /api/v1/admin/reports/campaigns
     */
    public function campaigns(Request $request): JsonResponse
    {
        $campaigns = Campaign::withCount([
            'donations',
            'donations as paid_donations_count' => function ($query) {
                $query->where('status', 'paid');
            },
        ])
        ->withSum([
            'donations as total_donations_amount' => function ($query) {
                $query->where('status', 'paid');
            }
        ], 'amount')
        ->get()
        ->map(function ($campaign) {
            $progress = $campaign->goal_amount > 0 
                ? round(($campaign->raised_amount / $campaign->goal_amount) * 100, 2) 
                : 0;

            return [
                'id' => $campaign->id,
                'title_ar' => $campaign->title_ar,
                'title_en' => $campaign->title_en,
                'status' => $campaign->status,
                'goal_amount' => (float) $campaign->goal_amount,
                'raised_amount' => (float) $campaign->raised_amount,
                'progress_percentage' => $progress,
                'donations_count' => $campaign->donations_count,
                'paid_donations_count' => $campaign->paid_donations_count,
                'total_donations_amount' => (float) ($campaign->total_donations_amount ?? 0),
                'start_date' => $campaign->start_date,
                'end_date' => $campaign->end_date,
                'days_remaining' => $campaign->end_date ? max(0, Carbon::parse($campaign->end_date)->diffInDays(Carbon::now(), false)) : null,
                'created_at' => $campaign->created_at,
            ];
        });

        $totalStats = [
            'total_campaigns' => $campaigns->count(),
            'active_campaigns' => $campaigns->where('status', 'active')->count(),
            'total_goal' => (float) $campaigns->sum('goal_amount'),
            'total_raised' => (float) $campaigns->sum('raised_amount'),
            'total_donations' => $campaigns->sum('donations_count'),
        ];

        return response()->json([
            'message' => 'تم جلب تقرير الحملات بنجاح',
            'data' => $campaigns,
            'summary' => $totalStats,
        ]);
    }

    /**
     * تقرير طلبات التسجيل
     * 
     * GET /api/v1/admin/reports/applications
     * 
     * Query Parameters:
     * - status: حالة الطلب (under_review, accepted, rejected)
     * - program_id: فلترة حسب البرنامج
     * - from_date: تاريخ البداية
     * - to_date: تاريخ النهاية
     */
    public function applications(Request $request): JsonResponse
    {
        $query = StudentRegistration::with(['user:id,name,phone', 'program:id,title_ar,title_en']);

        // فلترة حسب الحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب البرنامج
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // فلترة حسب التاريخ
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // إحصائيات - إنشاء استعلامات منفصلة
        $baseQuery = StudentRegistration::query();
        
        // تطبيق نفس الفلترات
        if ($request->has('program_id')) {
            $baseQuery->where('program_id', $request->program_id);
        }
        if ($request->has('from_date')) {
            $baseQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $baseQuery->whereDate('created_at', '<=', $request->to_date);
        }
        
        $stats = [
            'total' => $query->count(),
            'under_review' => (int) (clone $baseQuery)->where('status', 'under_review')->count(),
            'accepted' => (int) (clone $baseQuery)->where('status', 'accepted')->count(),
            'rejected' => (int) (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // الطلبات حسب البرنامج
        $byProgram = StudentRegistration::select('program_id', DB::raw('count(*) as count'))
            ->groupBy('program_id')
            ->with('program:id,title_ar,title_en')
            ->get();

        // الطلبات حسب الحالة
        $byStatus = StudentRegistration::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // الحصول على البيانات
        $perPage = $request->get('per_page', 50);
        $applications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'message' => 'تم جلب تقرير الطلبات بنجاح',
            'data' => $applications->items(),
            'stats' => $stats,
            'by_program' => $byProgram,
            'by_status' => $byStatus,
            'meta' => [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'last_page' => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * تقرير المستخدمين
     * 
     * GET /api/v1/admin/reports/users
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::withCount([
            'donations',
            'donations as paid_donations_count' => function ($query) {
                $query->where('status', 'paid');
            },
            'studentRegistrations',
        ])
        ->withSum([
            'donations as total_donations_amount' => function ($query) {
                $query->where('status', 'paid');
            }
        ], 'amount')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'donations_count' => $user->donations_count,
                'paid_donations_count' => $user->paid_donations_count,
                'total_donated' => (float) ($user->total_donations_amount ?? 0),
                'registrations_count' => $user->student_registrations_count,
                'created_at' => $user->created_at,
            ];
        })
        ->sortByDesc('total_donated')
        ->values();

        $totalStats = [
            'total_users' => $users->count(),
            'total_donations' => $users->sum('donations_count'),
            'total_amount' => (float) $users->sum('total_donated'),
            'users_with_donations' => $users->where('donations_count', '>', 0)->count(),
        ];

        return response()->json([
            'message' => 'تم جلب تقرير المستخدمين بنجاح',
            'data' => $users,
            'summary' => $totalStats,
        ]);
    }

    /**
     * تقرير شامل - لوحة معلومات رئيسية
     * 
     * GET /api/v1/admin/reports/overview
     */
    public function overview(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->subMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        // إجمالي التبرعات
        $totalDonations = Donation::count();
        $totalDonationsAmount = Donation::sum('amount');
        $paidDonationsAmount = Donation::where('status', 'paid')->sum('amount');

        // إجمالي البرامج والحملات
        $totalPrograms = Program::count();
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();

        // الطلبات
        $totalApplications = StudentRegistration::count();
        $pendingApplications = StudentRegistration::where('status', 'under_review')->count();

        // المستخدمين
        $totalUsers = User::count();

        // التبرعات في الفترة المحددة
        $recentDonations = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'paid')
            ->count();

        $recentDonationsAmount = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->where('status', 'paid')
            ->sum('amount');

        // أفضل البرامج من حيث التبرعات
        $topPrograms = Program::withSum([
            'donations as total_amount' => function ($query) {
                $query->where('status', 'paid');
            }
        ], 'amount')
        ->withCount([
            'donations as donations_count' => function ($query) {
                $query->where('status', 'paid');
            }
        ])
        ->orderByDesc('total_amount')
        ->limit(5)
        ->get(['id', 'title_ar', 'title_en', 'status']);

        // أفضل الحملات
        $topCampaigns = Campaign::orderByDesc('raised_amount')
            ->limit(5)
            ->get(['id', 'title_ar', 'title_en', 'goal_amount', 'raised_amount', 'status']);

        return response()->json([
            'message' => 'تم جلب التقرير الشامل بنجاح',
            'data' => [
                'summary' => [
                    'total_donations' => (int) $totalDonations,
                    'total_donations_amount' => (float) $totalDonationsAmount,
                    'paid_donations_amount' => (float) $paidDonationsAmount,
                    'total_programs' => (int) $totalPrograms,
                    'total_campaigns' => (int) $totalCampaigns,
                    'active_campaigns' => (int) $activeCampaigns,
                    'total_applications' => (int) $totalApplications,
                    'pending_applications' => (int) $pendingApplications,
                    'total_users' => (int) $totalUsers,
                ],
                'period_summary' => [
                    'from' => $fromDate,
                    'to' => $toDate,
                    'donations_count' => (int) $recentDonations,
                    'donations_amount' => (float) $recentDonationsAmount,
                ],
                'top_programs' => $topPrograms->map(function ($program) {
                    return [
                        'id' => $program->id,
                        'title_ar' => $program->title_ar,
                        'title_en' => $program->title_en,
                        'total_amount' => (float) ($program->total_amount ?? 0),
                        'donations_count' => (int) ($program->donations_count ?? 0),
                    ];
                }),
                'top_campaigns' => $topCampaigns->map(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'title_ar' => $campaign->title_ar,
                        'title_en' => $campaign->title_en,
                        'goal_amount' => (float) $campaign->goal_amount,
                        'raised_amount' => (float) $campaign->raised_amount,
                        'progress_percentage' => $campaign->goal_amount > 0 
                            ? round(($campaign->raised_amount / $campaign->goal_amount) * 100, 2) 
                            : 0,
                    ];
                }),
            ],
        ]);
    }

    /**
     * تصدير تقرير التبرعات إلى Excel
     * 
     * GET /api/v1/admin/reports/donations/export/excel
     */
    public function exportDonationsExcel(Request $request)
    {
        $params = $request->all();
        $fileName = 'donations_report_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new DonationsExport($params), $fileName);
    }

    /**
     * تصدير تقرير التبرعات إلى PDF
     * 
     * GET /api/v1/admin/reports/donations/export/pdf
     */
    public function exportDonationsPdf(Request $request)
    {
        $query = Donation::with(['user:id,name,phone', 'program:id,title_ar,title_en', 'campaign:id,title_ar,title_en']);

        // تطبيق نفس الفلترات من method donations
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $donations = $query->orderBy('created_at', 'desc')->get();

        // حساب الإحصائيات
        $stats = [
            'total_count' => $donations->count(),
            'total_amount' => $donations->sum('amount'),
            'paid_count' => $donations->where('status', 'paid')->count(),
            'paid_amount' => $donations->where('status', 'paid')->sum('amount'),
        ];

        $pdf = PdfFacade::loadView('reports.donations', [
            'donations' => $donations,
            'stats' => $stats,
            'filters' => $request->all(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('donations_report_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * تصدير التقرير المالي إلى Excel
     * 
     * GET /api/v1/admin/reports/financial/export/excel
     */
    public function exportFinancialExcel(Request $request)
    {
        $params = $request->all();
        $fileName = 'financial_report_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new FinancialExport($params), $fileName);
    }

    /**
     * تصدير التقرير المالي إلى PDF
     * 
     * GET /api/v1/admin/reports/financial/export/pdf
     */
    public function exportFinancialPdf(Request $request)
    {
        $fromDate = $request->get('from_date', Carbon::now()->subMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());
        $period = $request->get('period', 'monthly');

        // جمع البيانات (نفس منطق method financial)
        $totalDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->sum('amount');
        $paidDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->sum('amount');
        $pendingDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'pending')->sum('amount');
        $donorsCount = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $averageDonation = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->avg('amount');

        $donationsByStatus = Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->get();

        $data = [
            'period' => ['from' => $fromDate, 'to' => $toDate, 'type' => $period],
            'summary' => [
                'total_donations' => (float) $totalDonations,
                'paid_donations' => (float) $paidDonations,
                'pending_donations' => (float) $pendingDonations,
                'donors_count' => (int) $donorsCount,
                'average_donation' => (float) round($averageDonation ?? 0, 2),
            ],
            'by_status' => $donationsByStatus,
        ];

        $pdf = PdfFacade::loadView('reports.financial', $data)->setPaper('a4', 'portrait');
        return $pdf->download('financial_report_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * تصدير تقرير البرامج إلى Excel
     * 
     * GET /api/v1/admin/reports/programs/export/excel
     */
    public function exportProgramsExcel(Request $request)
    {
        $fileName = 'programs_report_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new ProgramsExport(), $fileName);
    }

    /**
     * تصدير تقرير الطلبات إلى Excel
     * 
     * GET /api/v1/admin/reports/applications/export/excel
     */
    public function exportApplicationsExcel(Request $request)
    {
        $params = $request->all();
        $fileName = 'applications_report_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new ApplicationsExport($params), $fileName);
    }

    /**
     * تصدير تقرير الطلبات إلى PDF
     * 
     * GET /api/v1/admin/reports/applications/export/pdf
     */
    public function exportApplicationsPdf(Request $request)
    {
        $query = StudentRegistration::with(['user:id,name,phone', 'program:id,title_ar,title_en']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $applications->count(),
            'under_review' => $applications->where('status', 'under_review')->count(),
            'accepted' => $applications->where('status', 'accepted')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];

        $pdf = PdfFacade::loadView('reports.applications', [
            'applications' => $applications,
            'stats' => $stats,
            'filters' => $request->all(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('applications_report_' . date('Y-m-d_His') . '.pdf');
    }
}

