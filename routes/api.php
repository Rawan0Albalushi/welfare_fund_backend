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
    Route::get('/my-registration', [App\Http\Controllers\Students\RegistrationController::class, 'myRegistration']);
    Route::put('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'update']);
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
    Route::post('/donations', [DonationController::class, 'store']);
    Route::post('/donations/with-payment', [DonationController::class, 'storeWithPayment']);
    Route::get('/donations/quick-amounts', [DonationController::class, 'quickAmounts']);
    Route::get('/programs/{id}/donations', [DonationController::class, 'programDonations']);
    
    // Legacy donation endpoints
    Route::post('/donations/gift', [LegacyDonationController::class, 'gift']);
    Route::get('/donations/{id}/status', [LegacyDonationController::class, 'status']);
    Route::get('/payments/callback', [LegacyDonationController::class, 'callback']);
    Route::post('/payments/webhook', [LegacyDonationController::class, 'webhook']);

    // Payment endpoints
    Route::post('/payments/create', [PaymentController::class, 'createPayment']);
    Route::get('/payments/status/{sessionId}', [PaymentController::class, 'getPaymentStatus']);

    // Authentication endpoints
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth endpoints
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // User profile and settings
        Route::prefix('me')->group(function () {
            Route::get('/edit/profile', [App\Http\Controllers\Me\EditProfileController::class, 'show']);
            Route::patch('/edit/profile', [App\Http\Controllers\Me\EditProfileController::class, 'update']);
            Route::get('/donations', [App\Http\Controllers\Me\DonationsController::class, 'index']);
        });

        // Student registration
        Route::prefix('students/registration')->group(function () {
            Route::post('/', [App\Http\Controllers\Students\RegistrationController::class, 'store']);
            Route::get('/', [App\Http\Controllers\Students\RegistrationController::class, 'index']);
            Route::get('/my-registration', [App\Http\Controllers\Students\RegistrationController::class, 'myRegistration']);
            Route::get('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Students\RegistrationController::class, 'update']);
            Route::post('/{id}/documents', [App\Http\Controllers\Students\RegistrationController::class, 'uploadDocuments']);
        });

        // Admin routes (require admin role)
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Categories management
            Route::apiResource('categories', App\Http\Controllers\Admin\CategoryController::class);
            
            // Programs management
            Route::apiResource('programs', App\Http\Controllers\Admin\ProgramController::class);
            
            // Applications management
            Route::get('/applications', [App\Http\Controllers\Admin\ApplicationController::class, 'index']);
            Route::patch('/applications/{id}/status', [App\Http\Controllers\Admin\ApplicationController::class, 'updateStatus']);
            
            // Donations management
            Route::get('/donations', [App\Http\Controllers\Admin\DonationController::class, 'index']);
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::post('/webhooks/thawani', [WebhookController::class, 'handle']);
