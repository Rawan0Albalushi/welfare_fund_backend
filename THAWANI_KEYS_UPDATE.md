# 🔑 تحديث مفاتيح Thawani UAT

## ❌ **المشكلة الحالية:**
المفاتيح الحالية لا تزال نفسها ولم تتغير:

```
THAWANI_SECRET_KEY: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et
THAWANI_PUBLISHABLE_KEY: HGvTMLDssJghr9t1N9gr4DVYtQqyBy
```

## 🔧 **المطلوب منك:**

### **1. تحديث ملف .env:**

أضف هذه الأسطر في ملف `.env`:

```env
# Thawani Payment Gateway Configuration (UAT Environment)
THAWANI_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_PUBLISHABLE_KEY=pk_test_51H1234567890abcdefghijklmnopqrstuvwxyz
THAWANI_BASE_URL=https://uatcheckout.thawani.om/api/v1
```

**استبدل المفاتيح بمفاتيحك الحقيقية من:**
- https://thawani.om/register
- https://dashboard.thawani.om

### **2. مسح الكاش:**

```bash
php artisan config:clear
```

### **3. اختبار المفاتيح:**

```bash
php artisan thawani:test-service --amount=1.0
```

## 🎯 **النتيجة المتوقعة:**

بعد تحديث المفاتيح الصحيحة:

```json
{
  "success": true,
  "session_id": "sess_12345",
  "payment_url": "https://uatcheckout.thawani.om/pay/sess_12345?key=pk_test_..."
}
```

## 📱 **اختبار من Flutter:**

```dart
// إنشاء تبرع
final result = await PaymentService.createDonationWithPayment(
  campaignId: 1,
  amount: 75.0,
  donorName: 'محمد علي',
  note: 'تبرع اختبار',
);

// النتيجة:
// ✅ التبرع يتم إنشاؤه في MySQL
// ✅ صفحة دفع حقيقية تفتح في ثواني
// ✅ يمكن الدفع عبر بطاقات ائتمان، مدى، إلخ
```

## ⚠️ **ملاحظات مهمة:**

1. **تأكد من تحديث ملف .env** وليس ملف آخر
2. **المفاتيح يجب أن تبدأ بـ:**
   - `sk_test_` للـ Secret Key
   - `pk_test_` للـ Publishable Key
3. **مسح الكاش ضروري** بعد التحديث

## 🚀 **البدء:**
