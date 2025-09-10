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

Route::get('/payment/bridge/success', function (Request $request) {
    $sessionId = $request->query('session_id'); // قد تكون null

    $html = '<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>تم الدفع بنجاح</title>
<style>
  :root { color-scheme: light dark; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Noto Sans", "Hiragino Kaku Gothic ProN", "Apple Color Emoji";
    display:flex; justify-content:center; align-items:center; height:100vh; margin:0;
    background: linear-gradient(135deg,#4CAF50,#2e7d32);
    color:#fff;
  }
  .card {
    text-align:center; padding:28px 32px; border-radius:18px;
    background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    max-width: 420px;
  }
  h1 { margin: 0 0 12px; font-size: 22px; }
  p  { margin: 4px 0 0; opacity:.95 }
  .icon { font-size:56px; margin-bottom:10px }
  .btn { margin-top:18px; display:inline-block; padding:10px 16px; border-radius:12px;
         background:#1b5e20; color:#fff; text-decoration:none; }
  .muted { opacity:.85; font-size: 13px; margin-top:8px }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">✅</div>
    <h1>تم الدفع بنجاح</h1>
    <p>يمكنك إغلاق هذه الصفحة والعودة للتطبيق.</p>
    <a class="btn" href="#" id="closeBtn">إغلاق الآن</a>
    <div class="muted" id="countdownBox">سيتم الإغلاق تلقائياً خلال <span id="count">3</span> ثوانٍ</div>
  </div>

<script>
(function(){
  // قد لا يتوفر session_id من ثواني — خله اختياري
  const sessionId = ' . json_encode($sessionId) . ';

  function payload(status){
    return { status, session_id: sessionId || null };
  }

  function notifyApp(status){
    const data = payload(status);

    // 1) InAppWebView (flutter_inappwebview)
    if (window.flutter_inappwebview && typeof window.flutter_inappwebview.callHandler === "function") {
      try { window.flutter_inappwebview.callHandler("paymentResult", data); } catch(e){}
    }

    // 2) webview_flutter (JavaScriptChannel) — استخدم اسم القناة PaymentChannel داخل Flutter
    if (window.PaymentChannel && typeof window.PaymentChannel.postMessage === "function") {
      try { window.PaymentChannel.postMessage(JSON.stringify(data)); } catch(e){}
    }

    // 3) iOS WKWebView عبر messageHandlers (لو عاملين حقن مخصص)
    if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.paymentResult) {
      try { window.webkit.messageHandlers.paymentResult.postMessage(data); } catch(e){}
    }

    // 4) Fallback: Deep Link (اختياري — فعّله لو عاملين schema)
    // try { window.location.href = "myapp://payment/success" + (sessionId ? ("?session_id="+encodeURIComponent(sessionId)) : ""); } catch(e){}
  }

  function closeView(){
    notifyApp("success");
    // محاولة إغلاق النافذة لو كانت Popup
    try { window.close(); } catch(e){}
  }

  // زر إغلاق يدوي
  document.getElementById("closeBtn").addEventListener("click", function(e){
    e.preventDefault(); closeView();
  });

  // عدّاد لإغلاق تلقائي
  var c = 3, countEl = document.getElementById("count");
  var t = setInterval(function(){
    c--; if (countEl) countEl.textContent = c;
    if (c <= 0) { clearInterval(t); closeView(); }
  }, 1000);
})();
</script>
</body>
</html>';

    return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
});

Route::get('/payment/bridge/cancel', function (Request $request) {
    $sessionId = $request->query('session_id'); // قد تكون null

    $html = '<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>تم إلغاء الدفع</title>
<style>
  :root { color-scheme: light dark; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Noto Sans";
    display:flex; justify-content:center; align-items:center; height:100vh; margin:0;
    background: linear-gradient(135deg,#e53935,#b71c1c);
    color:#fff;
  }
  .card {
    text-align:center; padding:28px 32px; border-radius:18px;
    background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    max-width: 420px;
  }
  h1 { margin: 0 0 12px; font-size: 22px; }
  p  { margin: 4px 0 0; opacity:.95 }
  .icon { font-size:56px; margin-bottom:10px }
  .btn { margin-top:18px; display:inline-block; padding:10px 16px; border-radius:12px;
         background:#7f0000; color:#fff; text-decoration:none; }
  .muted { opacity:.85; font-size: 13px; margin-top:8px }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">❌</div>
    <h1>تم إلغاء العملية</h1>
    <p>يمكنك إغلاق هذه الصفحة والعودة للتطبيق.</p>
    <a class="btn" href="#" id="closeBtn">إغلاق الآن</a>
    <div class="muted" id="countdownBox">سيتم الإغلاق تلقائياً خلال <span id="count">3</span> ثوانٍ</div>
  </div>

<script>
(function(){
  const sessionId = ' . json_encode($sessionId) . ';

  function payload(status){
    return { status, session_id: sessionId || null };
  }

  function notifyApp(status){
    const data = payload(status);

    if (window.flutter_inappwebview && typeof window.flutter_inappwebview.callHandler === "function") {
      try { window.flutter_inappwebview.callHandler("paymentResult", data); } catch(e){}
    }
    if (window.PaymentChannel && typeof window.PaymentChannel.postMessage === "function") {
      try { window.PaymentChannel.postMessage(JSON.stringify(data)); } catch(e){}
    }
    if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.paymentResult) {
      try { window.webkit.messageHandlers.paymentResult.postMessage(data); } catch(e){}
    }
    // Fallback Deep Link (اختياري):
    // try { window.location.href = "myapp://payment/cancel" + (sessionId ? ("?session_id="+encodeURIComponent(sessionId)) : ""); } catch(e){}
  }

  function closeView(){
    notifyApp("cancel");
    try { window.close(); } catch(e){}
  }

  document.getElementById("closeBtn").addEventListener("click", function(e){
    e.preventDefault(); closeView();
  });

  var c = 3, countEl = document.getElementById("count");
  var t = setInterval(function(){
    c--; if (countEl) countEl.textContent = c;
    if (c <= 0) { clearInterval(t); closeView(); }
  }, 1000);
})();
</script>
</body>
</html>';

    return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
});
