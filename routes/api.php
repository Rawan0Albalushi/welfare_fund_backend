<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Donations\DonationController;

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

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Public catalog endpoints
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/programs', [CatalogController::class, 'programs']);
    Route::get('/programs/{id}', [CatalogController::class, 'show']);
    Route::get('/donations/recent', [CatalogController::class, 'recentDonations']);

    // Public donation endpoints
    Route::post('/donations', [DonationController::class, 'store']);
    Route::post('/donations/gift', [DonationController::class, 'gift']);
    Route::get('/donations/{id}/status', [DonationController::class, 'status']);
    Route::get('/payments/callback', [DonationController::class, 'callback']);
    Route::post('/payments/webhook', [DonationController::class, 'webhook']);

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

        // Student applications
        Route::prefix('students/applications')->group(function () {
            Route::post('/', [App\Http\Controllers\Students\ApplicationController::class, 'store']);
            Route::get('/', [App\Http\Controllers\Students\ApplicationController::class, 'index']);
            Route::get('/{id}', [App\Http\Controllers\Students\ApplicationController::class, 'show']);
            Route::post('/{id}/documents', [App\Http\Controllers\Students\ApplicationController::class, 'uploadDocuments']);
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
