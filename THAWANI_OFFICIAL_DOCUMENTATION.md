# 🚀 دليل Thawani API الرسمي

بناءً على [الوثائق الرسمية لـ Thawani](https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api/5534c91789a48-thawani-e-commerce-api)

## 📋 المتطلبات الأساسية

### 1. **حساب Thawani**
- إنشاء حساب في [Thawani](https://thawani.om/register)
- تفعيل الحساب عبر البريد الإلكتروني
- الحصول على API Keys من لوحة التحكم

### 2. **أنواع API Keys (حسب الوثائق الرسمية)**

#### **Test Environment (Sandbox):**
```env
THAWANI_API_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
```

#### **Production Environment:**
```env
THAWANI_API_KEY=sk_live_xxxxxxxxxxxxxxxxxxxxxxxx
```

## 🔧 إعداد المشروع

### 1. **تحديث ملف .env**

```env
# Thawani Payment Gateway Configuration
THAWANI_API_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_BASE_URL=https://checkout.thawani.om/api/v1
```

### 2. **مسح الكاش**

```bash
php artisan config:clear
php artisan cache:clear
```

## 🧪 اختبار الإعدادات

### 1. **اختبار التكوين الأساسي**

```bash
php artisan config:show services.thawani
```

### 2. **اختبار الاتصال الرسمي**

```bash
php artisan thawani:test-official
```

### 3. **اختبار بمبلغ محدد**

```bash
php artisan thawani:test-official --amount=5.0 --reference=test_donation
```

## 📊 تنسيق البيانات حسب الوثائق الرسمية

### **Create Session Request:**

```json
{
  "client_reference_id": "donation_123456",
  "mode": "payment",
  "products": [
    {
      "name": "Donation",
      "unit_amount": 5000,
      "quantity": 1
    }
  ],
  "success_url": "https://your-app.com/success",
  "cancel_url": "https://your-app.com/cancel",
  "metadata": {
    "client_reference_id": "donation_123456",
    "amount_omr": 5.0,
    "type": "donation"
  }
}
```

### **Headers المطلوبة:**

```
Content-Type: application/json
thawani-api-key: sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
```

## 🎯 النتائج المتوقعة

### **Create Session Response:**

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

### **Payment Status Response:**

```json
{
  "success": true,
  "message": "Payment status retrieved successfully",
  "data": {
    "session_id": "sess_12345",
    "payment_status": "paid",
    "total_amount": 5000,
    "client_reference_id": "donation_123456"
  }
}
```

## 🔍 استكشاف الأخطاء

### **المشكلة: خطأ 500 من Thawani**

#### الأسباب المحتملة:
1. **API Key غير صحيح**
2. **API Key من بيئة خاطئة (test vs production)**
3. **تنسيق البيانات غير صحيح**
4. **حساب غير مفعل**

#### الحلول:

##### 1. التحقق من API Key:
```bash
php artisan config:show services.thawani
```

##### 2. اختبار API Key:
```bash
php artisan thawani:test-official
```

##### 3. فحص الـ Logs:
```bash
Get-Content storage/logs/laravel.log -Tail 20
```

### **المشكلة: خطأ "Invalid API Key"**

#### الحل:
1. تأكد من أن API Key يبدأ بـ `sk_test_` أو `sk_live_`
2. تأكد من عدم وجود مسافات إضافية
3. تأكد من أن الحساب مفعل في Thawani

### **المشكلة: خطأ "Account not activated"**

#### الحل:
1. اذهب إلى [Thawani Dashboard](https://dashboard.thawani.om)
2. تأكد من تفعيل الحساب
3. تحقق من إعدادات الحساب

## 📱 اختبار من Flutter

### 1. **اختبار إنشاء جلسة دفع**

```dart
// Test payment creation
final response = await http.post(
  Uri.parse('http://192.168.100.105:8000/api/v1/payments/create'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'amount': 5.0,
    'client_reference_id': 'test_${DateTime.now().millisecondsSinceEpoch}',
    'return_url': 'https://your-app.com/payment/return',
  }),
);

print('Status: ${response.statusCode}');
print('Body: ${response.body}');
```

### 2. **اختبار حالة الدفع**

```dart
// Test payment status
final statusResponse = await http.get(
  Uri.parse('http://192.168.100.105:8000/api/v1/payments/status/$sessionId'),
);

print('Status: ${statusResponse.statusCode}');
print('Body: ${statusResponse.body}');
```

## 📞 الدعم

### **وثائق Thawani الرسمية:**
- [Thawani API Documentation](https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api/5534c91789a48-thawani-e-commerce-api)
- [Thawani Dashboard](https://dashboard.thawani.om)
- [Thawani Support](https://thawani.om/support)

### **معلومات الاتصال:**
- البريد الإلكتروني: support@thawani.om
- الهاتف: +968 2444 4444

## ✅ قائمة التحقق النهائية

- [ ] تم إنشاء حساب Thawani
- [ ] تم تفعيل الحساب
- [ ] تم الحصول على API Keys
- [ ] تم إضافة API Keys في ملف .env
- [ ] تم مسح الكاش
- [ ] تم اختبار التكوين
- [ ] تم اختبار الاتصال
- [ ] تم اختبار من Flutter
- [ ] تم فحص الـ logs

## 🚀 بعد الإعداد الناجح

1. **اختبر بمبالغ مختلفة**
2. **اختبر في بيئة الإنتاج**
3. **راقب الـ logs للتأكد من عدم وجود أخطاء**
4. **أضف webhook handling إذا لزم الأمر**

## 📚 المراجع

- [Thawani E-Commerce API Documentation](https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api/5534c91789a48-thawani-e-commerce-api)
- [Thawani Dashboard](https://dashboard.thawani.om)
- [Thawani Registration](https://thawani.om/register)
