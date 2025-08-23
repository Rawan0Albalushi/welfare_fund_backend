# 🚀 دليل إعداد Thawani Payment Gateway

## 📋 المتطلبات الأساسية

### 1. **حساب Thawani**
- إنشاء حساب في [Thawani](https://thawani.om)
- الحصول على API Key من لوحة التحكم
- التأكد من تفعيل الحساب

### 2. **إعدادات البيئة**

#### أ. إضافة متغيرات البيئة في ملف `.env`:

```env
# Thawani Payment Gateway Configuration
THAWANI_API_KEY=your_actual_api_key_here
THAWANI_BASE_URL=https://checkout.thawani.om/api/v1

# For Production (if different)
# THAWANI_BASE_URL=https://checkout.thawani.om/api/v1
```

#### ب. مسح الكاش بعد التعديل:

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. **اختبار الإعدادات**

#### أ. اختبار التكوين:
```bash
php artisan config:show services.thawani
```

#### ب. اختبار الاتصال:
```bash
php artisan thawani:test
```

#### ج. اختبار بمبلغ محدد:
```bash
php artisan thawani:test --amount=25.0 --reference=test_donation
```

## 🔧 استكشاف الأخطاء

### **المشكلة: خطأ 500 من Thawani**

#### الأسباب المحتملة:
1. **API Key غير صحيح أو فارغ**
2. **تنسيق البيانات غير صحيح**
3. **مشكلة في الاتصال بالإنترنت**
4. **حساب Thawani غير مفعل**

#### الحلول:

##### 1. التحقق من API Key:
```bash
php artisan config:show services.thawani
```

##### 2. فحص الـ Logs:
```bash
# عرض آخر 50 سطر من الـ logs
Get-Content storage/logs/laravel.log -Tail 50
```

##### 3. اختبار الاتصال المباشر:
```bash
php artisan thawani:test --amount=1.0
```

### **المشكلة: خطأ "API key is not configured"**

#### الحل:
1. تأكد من وجود `THAWANI_API_KEY` في ملف `.env`
2. تأكد من عدم وجود مسافات إضافية
3. أعد تشغيل الخادم:
```bash
php artisan config:clear
php artisan cache:clear
```

### **المشكلة: خطأ "Invalid response structure"**

#### الحل:
1. تحقق من صحة API Key
2. تأكد من أن الحساب مفعل في Thawani
3. تحقق من تنسيق البيانات المرسلة

## 📱 اختبار من Flutter

### 1. **اختبار API من Flutter:**

```dart
// Test payment creation
final response = await http.post(
  Uri.parse('http://192.168.1.21:8000/api/v1/payments/create'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'amount': 10.0,
    'client_reference_id': 'test_${DateTime.now().millisecondsSinceEpoch}',
    'return_url': 'https://your-app.com/payment/return',
  }),
);

print('Status: ${response.statusCode}');
print('Body: ${response.body}');
```

### 2. **اختبار حالة الدفع:**

```dart
// Test payment status
final statusResponse = await http.get(
  Uri.parse('http://192.168.1.21:8000/api/v1/payments/status/$sessionId'),
);

print('Status: ${statusResponse.statusCode}');
print('Body: ${statusResponse.body}');
```

## 🔍 مراقبة الأداء

### 1. **مراقبة الـ Logs في الوقت الفعلي:**

```bash
# في Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 10
```

### 2. **فحص إحصائيات الدفع:**

```bash
# إنشاء أمر مخصص لعرض إحصائيات الدفع
php artisan make:command PaymentStats
```

## 📞 الدعم

### **في حالة استمرار المشاكل:**

1. **تحقق من وثائق Thawani الرسمية:**
   - [Thawani API Documentation](https://docs.thawani.om)

2. **تواصل مع دعم Thawani:**
   - البريد الإلكتروني: support@thawani.om
   - الهاتف: +968 2444 4444

3. **فحص حالة الخدمة:**
   - [Thawani Status Page](https://status.thawani.om)

## ✅ قائمة التحقق

- [ ] تم إضافة `THAWANI_API_KEY` في ملف `.env`
- [ ] تم مسح الكاش بعد التعديل
- [ ] تم اختبار التكوين بـ `php artisan config:show services.thawani`
- [ ] تم اختبار الاتصال بـ `php artisan thawani:test`
- [ ] تم اختبار API من Flutter
- [ ] تم فحص الـ logs للتأكد من عدم وجود أخطاء

## 🎯 النتيجة المتوقعة

بعد الإعداد الصحيح، يجب أن تحصل على:

```json
{
    "success": true,
    "message": "Checkout session created successfully",
    "data": {
        "session_id": "sess_12345",
        "payment_url": "https://checkout.thawani.om/pay/sess_12345"
    }
}
```
