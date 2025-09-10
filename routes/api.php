<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\CampaignController;
use App\Http\Controllers\Public\DonationController;
use App\Http\Controllers\Donations\DonationController as LegacyDonationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Me\DonationsController;

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

// Legacy student registration routes for frontend compatibility
Route::middleware('auth:sanctum')->prefix('students/registration')->group(function () {
    Route::post('/', [App\Http\Controllers\Students\RegistrationController::class, 'store']);
    Route::get('/my-registration', [App\Http\Controllers\Students\RegistrationController::class, 'myRegistration']);
    Route::get('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'update']);
    Route::post('/{id}/documents', [App\Http\Controllers\Students\RegistrationController::class, 'uploadDocuments']);
});

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

    // Public donation endpoints
    Route::post('/donations/with-payment', [DonationController::class, 'storeWithPayment'])->middleware('auth:sanctum');
    Route::post('/donations', [DonationController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/donations/{id}', [DonationController::class, 'show'])->middleware('auth:sanctum');
    Route::get('/donations/quick-amounts', [DonationController::class, 'quickAmounts']);
    Route::get('/programs/{id}/donations', [DonationController::class, 'programDonations']);
    
    // Legacy donation endpoints (with user linking)
    Route::post('/donations', [LegacyDonationController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/donations/gift', [LegacyDonationController::class, 'gift'])->middleware('auth:sanctum');
    Route::get('/donations/{id}/status', [LegacyDonationController::class, 'status']);
    Route::get('/payments/callback', [LegacyDonationController::class, 'callback']);
    Route::post('/payments/webhook', [LegacyDonationController::class, 'webhook']);

    // Payment endpoints (Thawani)
    Route::post('/payments/create', [PaymentController::class, 'createPayment']);
    Route::get('/payments/status/{sessionId}', [PaymentController::class, 'getPaymentStatus']);
    Route::get('/payments', [PaymentController::class, 'index']); // ?session_id=...

    // Success/Cancel display pages used by Thawani redirects (عرض فقط)
    Route::get('/payments/success', [PaymentController::class, 'paymentSuccess']);
    Route::get('/payments/cancel',  [PaymentController::class, 'paymentCancel']);

    // Webhook (Thawani) - سجّلي هذا المسار في لوحة ثواني
    Route::post('/payments/webhook/thawani', [WebhookController::class, 'handle']);

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
    });
});

// Authenticated user endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// (اختياري) Alias خارجي للويبهوك خارج v1 — سجّلي واحد فقط في Thawani Dashboard
Route::post('/webhooks/thawani', [WebhookController::class, 'handle']);
