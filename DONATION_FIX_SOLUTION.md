# 🔧 حل مشكلة عدم ظهور التبرعات في قائمة المستخدم

## 🔍 المشكلة
المستخدم يسجل دخول ويعمل تبرع، لكن التبرع لا يظهر في قائمة تبرعاته (`/api/v1/me/donations`).

## 🕵️ السبب الجذري
كان هناك **تكرار في routes التبرعات** في ملف `routes/api.php`:

```php
// المشكلة: route مكرر!
Route::post('/donations', [DonationController::class, 'store']); // بدون auth
Route::post('/donations', [LegacyDonationController::class, 'store'])->middleware('auth:sanctum'); // مع auth
```

**النتيجة:** عندما يسجل المستخدم دخول ويعمل تبرع، قد يتم استخدام الـ route الخطأ الذي لا يربط التبرع بحسابه!

## ✅ الحل المطبق

### 1. إصلاح Routes
تم فصل routes التبرعات بوضوح:

```php
// للتبرعات المجهولة (بدون authentication)
Route::post('/donations/anonymous', [DonationController::class, 'store']);

// للمستخدمين المسجلين (مع authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/donations', [LegacyDonationController::class, 'store']);
    Route::post('/donations/gift', [LegacyDonationController::class, 'gift']);
});
```

### 2. التأكد من ربط التبرعات
الـ `LegacyDonationController` يربط التبرعات بالمستخدمين:

```php
$donation = $this->donationsService->createQuickDonation(
    $request->validated(),
    $request->user()->id // ربط التبرع بالمستخدم
);
```

### 3. استرجاع التبرعات
الـ `Me/DonationsController` يسترجع التبرعات المرتبطة بالمستخدم:

```php
$query = Donation::where(function ($q) use ($user) {
    $q->where('user_id', $user->id) // التبرعات المرتبطة مباشرة
      ->orWhere(function ($subQ) use ($user) {
          // التبرعات المرتبطة برقم الهاتف (للتبرعات القديمة)
          $subQ->whereNull('user_id')
               ->whereJsonContains('payload->phone', $user->phone);
      });
});
```

## 🚀 كيفية الاستخدام

### للمستخدمين المسجلين:
```bash
# إنشاء تبرع
POST /api/v1/donations
Authorization: Bearer YOUR_TOKEN
{
  "program_id": 1,
  "amount": 50,
  "donor_name": "اسمك",
  "note": "تبرع تجريبي"
}

# استرجاع تبرعاتك
GET /api/v1/me/donations
Authorization: Bearer YOUR_TOKEN
```

### للتبرعات المجهولة:
```bash
# إنشاء تبرع مجهول
POST /api/v1/donations/anonymous
{
  "program_id": 1,
  "amount": 25,
  "donor_name": "مجهول",
  "note": "تبرع مجهول"
}
```

## 🧪 اختبار الحل

### 1. سجل دخول في التطبيق
### 2. اعمل تبرع جديد باستخدام:
   - **Endpoint:** `POST /api/v1/donations`
   - **Headers:** `Authorization: Bearer YOUR_TOKEN`

### 3. تحقق من قائمة تبرعاتك:
   - **Endpoint:** `GET /api/v1/me/donations`
   - **Headers:** `Authorization: Bearer YOUR_TOKEN`

### 4. يجب أن يظهر التبرع الجديد! 🎉

## 📱 تحديث Flutter App

تأكد من أن تطبيق Flutter يستخدم الـ endpoints الصحيحة:

```dart
class DonationsService {
  static const String baseUrl = 'http://192.168.100.105:8000/api/v1';
  
  // للمستخدمين المسجلين
  Future<Map<String, dynamic>> createDonation({
    required int programId,
    required double amount,
    required String donorName,
  }) async {
    final token = await getAuthToken();
    
    final response = await http.post(
      Uri.parse('$baseUrl/donations'), // ✅ الصحيح
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'program_id': programId,
        'amount': amount,
        'donor_name': donorName,
      }),
    );
    
    return jsonDecode(response.body);
  }
  
  // استرجاع تبرعات المستخدم
  Future<List<Map<String, dynamic>>> getUserDonations() async {
    final token = await getAuthToken();
    
    final response = await http.get(
      Uri.parse('$baseUrl/me/donations'), // ✅ الصحيح
      headers: {
        'Authorization': 'Bearer $token',
      },
    );
    
    final data = jsonDecode(response.body);
    return List<Map<String, dynamic>>.from(data['data']);
  }
}
```

## 🔄 Migration للتبرعات القديمة

إذا كان لديك تبرعات قديمة غير مربوطة، يمكن ربطها برقم الهاتف:

```sql
-- ربط التبرعات القديمة بالمستخدمين
UPDATE donations 
SET user_id = (
    SELECT id FROM users 
    WHERE users.phone = JSON_EXTRACT(donations.payload, '$.phone')
)
WHERE user_id IS NULL 
AND JSON_EXTRACT(payload, '$.phone') IS NOT NULL;
```

## ✅ النتيجة

الآن عندما تسجل دخول وتعمل تبرع:
1. ✅ التبرع يتم ربطه بحسابك تلقائياً
2. ✅ يظهر في قائمة تبرعاتك فوراً
3. ✅ يمكنك تتبع جميع تبرعاتك
4. ✅ التبرعات المجهولة لا تزال متاحة

**المشكلة محلولة! 🎉**
