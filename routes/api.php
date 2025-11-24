<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\CampaignController;
use App\Http\Controllers\Public\BannerController as PublicBannerController;
use App\Http\Controllers\Public\StudentRegistrationCardController as PublicStudentRegistrationCardController;
use App\Http\Controllers\Public\DonationController;
use App\Http\Controllers\Donations\DonationController as LegacyDonationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Me\DonationsController;
use App\Http\Controllers\Me\EditProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\StudentRegistrationCardController as AdminStudentRegistrationCardController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Public\SettingPageController as PublicSettingPageController;
use App\Http\Controllers\Admin\SettingPageController as AdminSettingPageController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\FcmTokenController;
use App\Services\FcmService;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Legacy auth routes for frontend compatibility (without v1 prefix)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Admin auth (v1 prefix only)
Route::post('/v1/admin/auth/login', [AdminAuthController::class, 'login']);

// Legacy student registration routes for frontend compatibility
Route::middleware('auth:sanctum')->prefix('students/registration')->group(function () {
    Route::post('/', [App\Http\Controllers\Students\RegistrationController::class, 'store']);
    Route::get('/my-registration', [App\Http\Controllers\Students\RegistrationController::class, 'myRegistration']);
    Route::get('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'update']);
    Route::post('/{id}/documents', [App\Http\Controllers\Students\RegistrationController::class, 'uploadDocuments']);
});

// Public settings pages endpoints (without v1 prefix for compatibility)
Route::get('/settings-pages', [PublicSettingPageController::class, 'index']);
Route::get('/settings-pages/{key}', [PublicSettingPageController::class, 'show']);

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Public catalog endpoints
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/programs', [CatalogController::class, 'programs']);
    Route::get('/programs/support', [CatalogController::class, 'supportPrograms']);
    Route::get('/programs/{id}', [CatalogController::class, 'show']);
    Route::get('/donations/recent', [CatalogController::class, 'recentDonations']);

    // Public campaign endpoints
    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/urgent', [CampaignController::class, 'urgent']);
    Route::get('/campaigns/featured', [CampaignController::class, 'featured']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);

    // Public banner endpoints
    Route::get('/banners', [PublicBannerController::class, 'index']);
    Route::get('/banners/featured', [PublicBannerController::class, 'featured']);
    Route::get('/banners/{id}', [PublicBannerController::class, 'show']);
    Route::get('/student-registration-card', [PublicStudentRegistrationCardController::class, 'show']);

    // Public settings pages endpoints
    Route::get('/settings-pages', [PublicSettingPageController::class, 'index']);
    Route::get('/settings-pages/{key}', [PublicSettingPageController::class, 'show']);

    // Public donation endpoints (allow anonymous donations)
    Route::post('/donations/with-payment', [DonationController::class, 'storeWithPayment']); // للتبرعات مع دفع (مسجل أو مجهول)
    Route::post('/donations/anonymous', [DonationController::class, 'store']); // للتبرعات المجهولة
    Route::post('/donations/anonymous-with-payment', [DonationController::class, 'storeWithPaymentAnonymous']); // للتبرعات المجهولة مع دفع
    Route::get('/donations/{id}', [DonationController::class, 'show'])->middleware('auth:sanctum');
    Route::get('/donations/quick-amounts', [DonationController::class, 'quickAmounts']);
    Route::get('/programs/{id}/donations', [DonationController::class, 'programDonations']);
    
    // Authenticated donation endpoints (with user linking)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/donations', [LegacyDonationController::class, 'store']); // للمستخدمين المسجلين
        Route::post('/donations/gift', [LegacyDonationController::class, 'gift']);
    });
    
    // Legacy endpoints (no auth required)
    Route::get('/donations/{id}/status', [LegacyDonationController::class, 'status']);
    Route::get('/payments/callback', [LegacyDonationController::class, 'callback']);
    Route::post('/payments/webhook', [LegacyDonationController::class, 'webhook']);

    // Payment endpoints (Thawani) - Unified endpoints with improved security
    // Rate limiting: 20 requests per minute for payment creation, 60 for status checks
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/payments/create', [PaymentController::class, 'createPayment']);
        Route::post('/payments/create-with-donation', [PaymentController::class, 'createWithDonation']);
        Route::post('/payments/confirm', [PaymentController::class, 'confirm']);
    });
    
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/payments/mobile/success', [PaymentController::class, 'mobileSuccess']);
        Route::get('/payments/status/{sessionId}', [PaymentController::class, 'getPaymentStatus']);
        Route::get('/payments', [PaymentController::class, 'index']); // ?session_id=...
    });

    // Success/Cancel display pages used by Thawani redirects (عرض فقط)
    Route::get('/payments/success', [PaymentController::class, 'paymentSuccess']);
    Route::get('/payments/cancel',  [PaymentController::class, 'paymentCancel']);

    // Webhook (Thawani) - سجّلي هذا المسار في لوحة ثواني
    // Rate limiting أعلى للويبهوك لأنها تأتي من ثواني (100 requests per minute)
    Route::middleware('throttle:100,1')->post('/payments/webhook/thawani', [WebhookController::class, 'handle']);

    // Student registration routes (v1 prefix for frontend compatibility)
    Route::middleware('auth:sanctum')->prefix('students/registration')->group(function () {
        Route::post('/', [App\Http\Controllers\Students\RegistrationController::class, 'store']);
        Route::get('/my-registration', [App\Http\Controllers\Students\RegistrationController::class, 'myRegistration']);
        Route::get('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'update']);
        Route::post('/{id}/documents', [App\Http\Controllers\Students\RegistrationController::class, 'uploadDocuments']);
    });

    // User's personal endpoints (require authentication)
    Route::middleware('auth:sanctum')->prefix('me')->group(function () {
        Route::get('/donations', [DonationsController::class, 'index']);
        Route::get('/donations/{id}', [DonationsController::class, 'show']);
        
        // Profile editing endpoints
        Route::get('/edit/profile', [EditProfileController::class, 'show']);
        Route::patch('/edit/profile', [EditProfileController::class, 'update']);
    });

    // FCM Token endpoint (require authentication)
    Route::middleware('auth:sanctum')->post('/fcm-token', [FcmTokenController::class, 'store']);

    // Test push notification endpoint
    Route::get('/test-push/{id}', function ($id, FcmService $fcm) {
        $record = DB::table('fcm_tokens')->where('id', $id)->first();

        if (!$record) {
            return response()->json(['error' => 'Token not found']);
        }

        $fcm->sendToToken(
            $record->fcm_token,
            'تنبيه تجريبي 🔔',
            'تم اختبار نظام الإشعارات بنجاح ✅',
            ['type' => 'test_notification']
        );

        return response()->json(['status' => 'Notification sent successfully ✅']);
    });

    // Admin endpoints (require auth + admin role)
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        // Admin auth (within v1)
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
        Route::get('/auth/me', [AdminAuthController::class, 'me']);

        // Dashboard & stats
        Route::get('/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard']);
        Route::get('/ping', [AdminDashboardController::class, 'ping']);

        // Reports endpoints
        Route::prefix('reports')->group(function () {
            Route::get('/overview', [AdminReportController::class, 'overview']);
            Route::get('/donations', [AdminReportController::class, 'donations']);
            Route::get('/donations/export/excel', [AdminReportController::class, 'exportDonationsExcel']);
            Route::get('/donations/export/pdf', [AdminReportController::class, 'exportDonationsPdf']);
            Route::get('/financial', [AdminReportController::class, 'financial']);
            Route::get('/financial/export/excel', [AdminReportController::class, 'exportFinancialExcel']);
            Route::get('/financial/export/pdf', [AdminReportController::class, 'exportFinancialPdf']);
            Route::get('/programs', [AdminReportController::class, 'programs']);
            Route::get('/programs/export/excel', [AdminReportController::class, 'exportProgramsExcel']);
            Route::get('/campaigns', [AdminReportController::class, 'campaigns']);
            Route::get('/applications', [AdminReportController::class, 'applications']);
            Route::get('/applications/export/excel', [AdminReportController::class, 'exportApplicationsExcel']);
            Route::get('/applications/export/pdf', [AdminReportController::class, 'exportApplicationsPdf']);
            Route::get('/users', [AdminReportController::class, 'users']);
        });
        // Categories CRUD
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);

        // Programs CRUD
        Route::get('/programs', [AdminProgramController::class, 'index']);
        Route::post('/programs', [AdminProgramController::class, 'store']);
        Route::get('/programs/{id}', [AdminProgramController::class, 'show']);
        Route::put('/programs/{id}', [AdminProgramController::class, 'update']);
        Route::delete('/programs/{id}', [AdminProgramController::class, 'destroy']);

        // Campaigns CRUD
        Route::get('/campaigns', [AdminCampaignController::class, 'index']);
        Route::post('/campaigns', [AdminCampaignController::class, 'store']);
        Route::get('/campaigns/{id}', [AdminCampaignController::class, 'show']);
        Route::put('/campaigns/{id}', [AdminCampaignController::class, 'update']);
        Route::delete('/campaigns/{id}', [AdminCampaignController::class, 'destroy']);
        Route::post('/upload/image', [AdminCampaignController::class, 'uploadImage']);

        // Banners CRUD
        Route::get('/banners', [AdminBannerController::class, 'index']);
        Route::post('/banners', [AdminBannerController::class, 'store']);
        Route::get('/banners/{id}', [AdminBannerController::class, 'show']);
        Route::put('/banners/{id}', [AdminBannerController::class, 'update']);
        Route::delete('/banners/{id}', [AdminBannerController::class, 'destroy']);
        Route::post('/banners/upload/image', [AdminBannerController::class, 'uploadImage']);
        Route::get('/student-registration-card', [AdminStudentRegistrationCardController::class, 'show']);
        Route::put('/student-registration-card', [AdminStudentRegistrationCardController::class, 'update']);
        Route::post('/student-registration-card/upload-background', [AdminStudentRegistrationCardController::class, 'uploadBackgroundImage']);

        // Donations listing
        Route::get('/donations', [AdminDonationController::class, 'index']);

        // Student applications
        Route::get('/applications', [AdminApplicationController::class, 'index']);
        Route::put('/applications/{id}/status', [AdminApplicationController::class, 'updateStatus']);

        // Users management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}/role', [AdminUserController::class, 'updateRole']);

        // Roles management
        Route::get('/roles', [AdminRoleController::class, 'index']);
        Route::post('/roles', [AdminRoleController::class, 'store']);
        Route::get('/roles/{id}', [AdminRoleController::class, 'show']);
        Route::put('/roles/{id}', [AdminRoleController::class, 'update']);
        Route::delete('/roles/{id}', [AdminRoleController::class, 'destroy']);

        // Permissions management
        Route::get('/permissions', [AdminPermissionController::class, 'index']);
        Route::post('/permissions', [AdminPermissionController::class, 'store']);
        Route::get('/permissions/{id}', [AdminPermissionController::class, 'show']);
        Route::put('/permissions/{id}', [AdminPermissionController::class, 'update']);
        Route::delete('/permissions/{id}', [AdminPermissionController::class, 'destroy']);

        // Settings pages management
        Route::get('/settings-pages', [AdminSettingPageController::class, 'index']);
        Route::post('/settings-pages', [AdminSettingPageController::class, 'store']);
        Route::get('/settings-pages/{key}', [AdminSettingPageController::class, 'show']);
        Route::put('/settings-pages/{key}', [AdminSettingPageController::class, 'update']);

        // Notifications
        Route::post('/send-notification', [AdminNotificationController::class, 'sendNotification']);
    });
});

// Authenticated user endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Test push notification endpoint (outside v1 for compatibility)
Route::get('/test-push/{id}', function ($id, FcmService $fcm) {
    $record = DB::table('fcm_tokens')->where('id', $id)->first();

    if (!$record) {
        return response()->json(['error' => 'Token not found']);
    }

    $fcm->sendToToken(
        $record->fcm_token,
        'تنبيه تجريبي 🔔',
        'تم اختبار نظام الإشعارات بنجاح ✅',
        ['type' => 'test_notification']
    );

    return response()->json(['status' => 'Notification sent successfully ✅']);
});

// (اختياري) Alias خارجي للويبهوك خارج v1 — سجّلي واحد فقط في Thawani Dashboard
Route::post('/webhooks/thawani', [WebhookController::class, 'handle']);
