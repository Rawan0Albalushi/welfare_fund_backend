# 🔑 مفاتيح Thawani UAT الصحيحة

## 📋 المفاتيح المطلوبة

### **1. تحديث ملف .env:**

```env
# Thawani Payment Gateway Configuration (UAT Environment)
THAWANI_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_PUBLISHABLE_KEY=pk_test_51H1234567890abcdefghijklmnopqrstuvwxyz
THAWANI_BASE_URL=https://uatcheckout.thawani.om/api/v1
```

## 🔧 كيفية الحصول على المفاتيح الصحيحة

### **1. إنشاء حساب في Thawani:**
- اذهب إلى: https://thawani.om/register
- أنشئ حساب جديد
- فعّل الحساب عبر البريد الإلكتروني

### **2. الحصول على مفاتيح UAT:**
- اذهب إلى: https://dashboard.thawani.om
- سجل دخول بحسابك
- اذهب إلى "API Keys" أو "Settings"
- انسخ مفاتيح UAT (Test Keys)

### **3. تنسيق المفاتيح:**
- **Secret Key**: يبدأ بـ `sk_test_` أو `sk_uat_`
- **Publishable Key**: يبدأ بـ `pk_test_` أو `pk_uat_`

## 🧪 اختبار المفاتيح

بعد تحديث المفاتيح:

```bash
# مسح الكاش
php artisan config:clear

# اختبار المفاتيح
php artisan thawani:test-keys

# اختبار الخدمة
php artisan thawani:test-service --amount=1.0
```

## 📱 اختبار من Flutter

```dart
// إنشاء جلسة دفع
final response = await http.post(
  Uri.parse('http://192.168.1.21:8000/api/v1/payments/create'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'products': [
      {
        'name': 'Donation',
        'quantity': 1,
        'unit_amount': 1000, // 1 OMR in baisa
      }
    ],
    'client_reference_id': 'donation_${DateTime.now().millisecondsSinceEpoch}',
  }),
);

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  final paymentUrl = data['payment_url'];
  
  // فتح صفحة الدفع
  await launchUrl(Uri.parse(paymentUrl));
}
```

## ⚠️ ملاحظات مهمة

1. **مفاتيح UAT** تستخدم للاختبار فقط
2. **المدفوعات في UAT** ليست حقيقية
3. **صفحة الدفع** ستفتح في UAT environment
4. **للإنتاج** تحتاج مفاتيح Production

## 🎯 النتيجة المتوقعة

بعد تحديث المفاتيح الصحيحة:

```json
{
  "success": true,
  "session_id": "sess_12345",
  "payment_url": "https://uatcheckout.thawani.om/pay/sess_12345?key=pk_test_..."
}
```

**صفحة الدفع ستفتح في:** `https://uatcheckout.thawani.om/pay/sess_12345?key=pk_test_...`
