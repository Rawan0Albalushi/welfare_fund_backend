# إصلاح مشكلة التبرعات المجهولة

## المشكلة
كان endpoint `/api/v1/donations/with-payment` يتطلب authentication، مما يمنع المستخدمين غير المسجلين من التبرع.

## الخطأ
```
DonationService: Response status: 401
DonationService: Response body: {"message":"Unauthenticated."}
```

## الحل

### 1. إزالة middleware authentication من route
**قبل:**
```php
Route::post('/donations/with-payment', [DonationController::class, 'storeWithPayment'])->middleware('auth:sanctum');
```

**بعد:**
```php
Route::post('/donations/with-payment', [DonationController::class, 'storeWithPayment']); // للتبرعات مع دفع (مسجل أو مجهول)
```

### 2. تحديث method storeWithPayment
**قبل:**
```php
public function storeWithPayment(Request $request): JsonResponse
{
    // المستخدم مطلوب الآن بسبب middleware
    $user = $request->user();
    
    // ...
    'user_id' => $user->id, // ربط التبرع بالمستخدم (مطلوب الآن)
```

**بعد:**
```php
public function storeWithPayment(Request $request): JsonResponse
{
    // المستخدم اختياري - يمكن أن يكون مسجل دخول أو مجهول
    $user = $request->user();
    
    // ...
    'user_id' => $user?->id, // ربط التبرع بالمستخدم إذا كان مسجل دخول
```

## النتيجة

### الآن يمكن للمستخدمين غير المسجلين:
- ✅ التبرع عبر `/api/v1/donations/with-payment`
- ✅ إنشاء جلسة دفع مع ثواني
- ✅ إتمام عملية الدفع

### المستخدمون المسجلون:
- ✅ يحتفظون بجميع الميزات السابقة
- ✅ يتم ربط التبرع بحسابهم تلقائياً
- ✅ يمكنهم متابعة تبرعاتهم

## Routes المتاحة للتبرعات

| Route | Authentication | الوصف |
|-------|---------------|--------|
| `POST /api/v1/donations/with-payment` | ❌ اختياري | تبرع مع دفع (مسجل أو مجهول) |
| `POST /api/v1/donations/anonymous` | ❌ غير مطلوب | تبرع مجهول بدون دفع |
| `POST /api/v1/donations/anonymous-with-payment` | ❌ غير مطلوب | تبرع مجهول مع دفع |
| `POST /api/v1/donations` | ✅ مطلوب | تبرع للمستخدمين المسجلين |
| `POST /api/v1/donations/gift` | ✅ مطلوب | تبرع هدية للمستخدمين المسجلين |

## اختبار الحل

### طلب تبرع مجهول:
```bash
POST /api/v1/donations/with-payment
Content-Type: application/json

{
    "program_id": 1,
    "amount": 100,
    "donor_name": "مجهول",
    "note": "تبرع خيري"
}
```

### الاستجابة المتوقعة:
```json
{
    "message": "Donation and payment session created successfully",
    "data": {
        "donation": {
            "id": 123,
            "donation_id": "DN_...",
            "amount": 100,
            "donor_name": "مجهول",
            "status": "pending",
            "user_id": null
        },
        "payment_session": {
            "session_id": "sess_...",
            "payment_url": "https://..."
        }
    }
}
```

## ملاحظات مهمة

1. **التوافق مع الكود الموجود**: جميع التغييرات متوافقة مع الكود الموجود
2. **الأمان**: لا تزال جميع التحققات الأمنية موجودة
3. **المرونة**: نفس endpoint يعمل للمستخدمين المسجلين وغير المسجلين
4. **التتبع**: يمكن تتبع التبرعات المجهولة عبر `donation_id`

---

**تم إصلاح المشكلة بنجاح!** 🎉
