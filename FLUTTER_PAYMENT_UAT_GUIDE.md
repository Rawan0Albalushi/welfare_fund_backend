# 🚀 دليل Flutter - نظام الدفع الحقيقي مع UAT

## ✅ **تم إصلاح النظام!**

### **ما تم تغييره:**
- ❌ **إزالة نظام Mock** - لا مزيد من الردود الوهمية
- ✅ **نظام دفع حقيقي** - يفتح صفحة Thawani الحقيقية
- ✅ **بيئة UAT** - للاختبار الآمن
- ✅ **صفحة دفع حقيقية** - تفتح في ثواني

## 🔧 **المطلوب منك:**

### **1. تحديث مفاتيح Thawani في ملف .env:**

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

## 📱 **كود Flutter المحدث:**

### **PaymentService Class:**

```dart
class PaymentService {
  static const String baseUrl = 'http://192.168.100.105:8000/api';

  // إنشاء جلسة دفع حقيقية
  static Future<Map<String, dynamic>> createPaymentSession({
    required double amount,
    required String reference,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/v1/payments/create'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'products': [
            {
              'name': 'Donation',
              'quantity': 1,
              'unit_amount': (amount * 1000).round(), // Convert to baisa
            }
          ],
          'client_reference_id': reference,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        print('✅ Payment session created successfully');
        print('🔗 Payment URL: ${data['payment_url']}');
        return data;
      } else {
        throw Exception('Failed to create payment session: ${response.body}');
      }
    } catch (e) {
      throw Exception('Payment service error: $e');
    }
  }

  // إنشاء تبرع مع دفع
  static Future<Map<String, dynamic>> createDonationWithPayment({
    required int campaignId,
    required double amount,
    required String donorName,
    String? note,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/v1/donations/with-payment'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campaign_id': campaignId,
          'amount': amount,
          'donor_name': donorName,
          'note': note,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        print('✅ Donation created with payment session');
        return data;
      } else {
        throw Exception('Failed to create donation: ${response.body}');
      }
    } catch (e) {
      throw Exception('Donation service error: $e');
    }
  }
}
```

### **استخدام في Flutter App:**

```dart
// مثال: إنشاء تبرع مع دفع
Future<void> makeDonation() async {
  try {
    final result = await PaymentService.createDonationWithPayment(
      campaignId: 1,
      amount: 100.0,
      donorName: 'أحمد محمد',
      note: 'تبرع للطلاب المحتاجين',
    );

    // الحصول على رابط الدفع
    final paymentUrl = result['data']['payment_session']['payment_url'];
    
    print('🔗 Opening payment page: $paymentUrl');
    
    // فتح صفحة الدفع في المتصفح
    await launchUrl(Uri.parse(paymentUrl));
    
    // أو فتح في WebView
    // Navigator.push(context, MaterialPageRoute(
    //   builder: (context) => WebViewPage(url: paymentUrl),
    // ));
    
  } catch (e) {
    print('❌ Error: $e');
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('خطأ في إنشاء التبرع: $e')),
    );
  }
}
```

## 🎯 **ما سيحدث:**

### **1. عند الضغط على "تبرع":**
- ✅ إنشاء تبرع في قاعدة البيانات
- ✅ إنشاء جلسة دفع حقيقية مع Thawani
- ✅ فتح صفحة دفع حقيقية في ثواني

### **2. صفحة الدفع:**
- 🌐 **تفتح في:** `https://uatcheckout.thawani.om/pay/sess_12345?key=pk_test_...`
- 💳 **خيارات الدفع:** بطاقات ائتمان، مدى، إلخ
- ⏱️ **سرعة:** تفتح في ثواني

### **3. بعد الدفع:**
- ✅ **نجح:** العودة إلى `success_url`
- ❌ **فشل:** العودة إلى `cancel_url`
- 📊 **تحديث:** حالة التبرع في قاعدة البيانات

## 🧪 **اختبار النظام:**

### **1. اختبار من Terminal:**
```bash
php artisan thawani:test-service --amount=1.0
```

### **2. اختبار من Flutter:**
```dart
// اختبار بسيط
await PaymentService.createPaymentSession(
  amount: 1.0,
  reference: 'test_${DateTime.now().millisecondsSinceEpoch}',
);
```

## ⚠️ **ملاحظات مهمة:**

1. **بيئة UAT** - المدفوعات ليست حقيقية
2. **صفحة دفع حقيقية** - تفتح في ثواني
3. **مفاتيح حقيقية** - مطلوبة من Thawani
4. **للإنتاج** - تغيير إلى Production keys

## 🚀 **النتيجة النهائية:**

```
✅ Flutter App → Laravel API → Thawani UAT → صفحة دفع حقيقية
```

**صفحة الدفع ستفتح في ثواني وستكون حقيقية تماماً!**
