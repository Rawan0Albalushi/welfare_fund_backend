# دليل الفرونت إند - API الدفع

## ✅ الباكند يعمل بشكل صحيح

### اختبار الباكند:
```bash
# طلب بدون session_id
GET http://localhost:8000/api/v1/payments
Response: {"success":false,"message":"Session ID is required"}

# طلب مع session_id غير صحيح
GET http://localhost:8000/api/v1/payments?session_id=invalid
Response: {"success":false,"message":"Donation not found for this session"}
```

## 🔧 الإصلاحات المطلوبة في الفرونت إند

### 1. تحديث payment_service.dart

```dart
// ❌ الخطأ - لا تستخدم
Future<Map<String, dynamic>> checkPaymentStatus(String sessionId) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/payments/status/$sessionId'), // خطأ
  );
  // ...
}

// ✅ الصحيح - استخدم هذا
Future<Map<String, dynamic>> checkPaymentStatus(String sessionId) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/payments?session_id=$sessionId'), // صحيح
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    if (data['success']) {
      return data['data'];
    }
  } else if (response.statusCode == 400) {
    throw Exception('Session ID is required');
  } else if (response.statusCode == 404) {
    throw Exception('Donation not found for this session');
  }
  
  throw Exception('Failed to check payment status');
}
```

### 2. تحديث donation_service.dart

```dart
// ❌ الخطأ - لا تستخدم
Future<Map<String, dynamic>> checkPaymentStatus(String sessionId) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/payments/status/$sessionId'), // خطأ
  );
  // ...
}

// ✅ الصحيح - استخدم هذا
Future<Map<String, dynamic>> checkPaymentStatus(String sessionId) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/payments?session_id=$sessionId'), // صحيح
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    if (data['success']) {
      return data['data'];
    }
  } else if (response.statusCode == 400) {
    throw Exception('Session ID is required');
  } else if (response.statusCode == 404) {
    throw Exception('Donation not found for this session');
  }
  
  throw Exception('Failed to check payment status');
}
```

## 🎯 تدفق العمل الصحيح

### 1. إنشاء جلسة الدفع
```dart
POST /api/v1/payments/create
{
  "products": [...],
  "program_id": 26,
  "donor_name": "أحمد محمد",
  "amount": 100.00
}
```

### 2. فتح صفحة الدفع
```dart
// استخدام payment_url من الاستجابة السابقة
WebView(
  initialUrl: paymentUrl, // من Thawani
  // ...
)
```

### 3. بعد نجاح الدفع
```dart
// التوجيه إلى
GET /api/v1/payments/success?session_id={sessionId}
```

### 4. التحقق من حالة الدفع
```dart
// ✅ الصحيح
GET /api/v1/payments?session_id={sessionId}

// ❌ الخطأ - لا تستخدم
GET /api/v1/payments/status/{sessionId}
```

## 🔍 خطوات التصحيح

### 1. تأكد من تحديث الملفات
- `lib/services/payment_service.dart`
- `lib/services/donation_service.dart`

### 2. مسح cache الفرونت إند
```bash
flutter clean
flutter pub get
```

### 3. إعادة تشغيل التطبيق
```bash
flutter run
```

### 4. اختبار التدفق
1. إنشاء تبرع جديد
2. الدفع عبر Thawani
3. التحقق من حالة الدفع
4. الانتقال لصفحة النجاح

## 📋 API Endpoints المتاحة

### ✅ الدفع
- `POST /api/v1/payments/create` - إنشاء جلسة دفع
- `GET /api/v1/payments?session_id={id}` - معلومات الدفع ✅
- `GET /api/v1/payments/success?session_id={id}` - نجاح الدفع
- `GET /api/v1/payments/cancel?session_id={id}` - إلغاء الدفع

### ❌ لا تستخدم
- `GET /api/v1/payments/status/{sessionId}` - قديم

## 🚨 ملاحظات مهمة

1. **تأكد من إرسال session_id** في جميع طلبات التحقق
2. **استخدم query parameter** `?session_id=` وليس path parameter
3. **عالج الأخطاء** بشكل صحيح (400, 404)
4. **اختبر التدفق الكامل** قبل الإنتاج

---
**تاريخ التحديث**: 24 أغسطس 2025
**الحالة**: ✅ الباكند جاهز، الفرونت إند يحتاج تحديث
