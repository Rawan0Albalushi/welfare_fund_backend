<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

/**
 * صفحات Bridge للنجاح/الإلغاء تُستخدم كـ success_url / cancel_url
 * ملاحظات:
 * - لا نعتمد على وجود session_id في الـ query (قد لا ترسله ثواني)
 * - نرسل النتيجة للتطبيق بطرق متعددة حسب نوع WebView
 * - التطبيق هو اللي يحتفظ بالـ sessionId محليًا ويستعلم الحالة من الباكند
 */

Route::get('/payment/bridge/success', [App\Http\Controllers\PaymentsController::class, 'bridgeSuccess']);
Route::get('/payment/bridge/cancel', [App\Http\Controllers\PaymentsController::class, 'bridgeCancel']);

// Optional: Fallback login route for APIs that trigger auth redirect
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized'], 401);
})->name('login');
