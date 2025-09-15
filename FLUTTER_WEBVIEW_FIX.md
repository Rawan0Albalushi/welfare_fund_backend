# 🔧 حل مشكلة فتح متصفح خارجي - استخدام WebView

## 🎯 **المشكلة الحالية:**
```dart
// هذا الكود يفتح متصفح خارجي ❌
await launchUrl(Uri.parse(paymentUrl));
```

## ✅ **الحل: استخدام WebView داخل التطبيق**

### **1. إضافة Dependencies:**

```yaml
# pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  flutter_inappwebview: ^6.0.0
  # أو
  webview_flutter: ^4.4.2
```

### **2. إنشاء Payment WebView Widget:**

```dart
import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';

class PaymentWebView extends StatefulWidget {
  final String paymentUrl;
  final String sessionId;
  final Function(String status, String sessionId) onPaymentResult;

  const PaymentWebView({
    Key? key,
    required this.paymentUrl,
    required this.sessionId,
    required this.onPaymentResult,
  }) : super(key: key);

  @override
  State<PaymentWebView> createState() => _PaymentWebViewState();
}

class _PaymentWebViewState extends State<PaymentWebView> {
  InAppWebViewController? webViewController;
  bool _isLoading = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('إتمام الدفع'),
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () {
            widget.onPaymentResult('cancel', widget.sessionId);
          },
        ),
      ),
      body: Stack(
        children: [
          InAppWebView(
            initialUrlRequest: URLRequest(url: WebUri(widget.paymentUrl)),
            onWebViewCreated: (controller) {
              webViewController = controller;
            },
            onLoadStart: (controller, url) {
              setState(() {
                _isLoading = true;
              });
              
              // التحقق من URLs النجاح والإلغاء
              if (url.toString().contains('/payment/bridge/success')) {
                widget.onPaymentResult('success', widget.sessionId);
              } else if (url.toString().contains('/payment/bridge/cancel')) {
                widget.onPaymentResult('cancel', widget.sessionId);
              }
            },
            onLoadStop: (controller, url) {
              setState(() {
                _isLoading = false;
              });
            },
            onReceivedServerTrustAuthRequest: (controller, challenge) {
              return ServerTrustAuthResponse(action: ServerTrustAuthResponseAction.PROCEED);
            },
          ),
          if (_isLoading)
            const Center(
              child: CircularProgressIndicator(),
            ),
        ],
      ),
    );
  }
}
```

### **3. تحديث PaymentService:**

```dart
class PaymentService {
  static const String baseUrl = 'http://192.168.100.105:8000/api';

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

### **4. استخدام WebView في التطبيق:**

```dart
// بدلاً من هذا الكود ❌
Future<void> makeDonation() async {
  try {
    final result = await PaymentService.createDonationWithPayment(
      campaignId: 1,
      amount: 100.0,
      donorName: 'أحمد محمد',
      note: 'تبرع للطلاب المحتاجين',
    );

    final paymentUrl = result['data']['payment_session']['payment_url'];
    
    // هذا يفتح متصفح خارجي ❌
    await launchUrl(Uri.parse(paymentUrl));
    
  } catch (e) {
    print('❌ Error: $e');
  }
}

// استخدم هذا الكود ✅
Future<void> makeDonation(BuildContext context) async {
  try {
    final result = await PaymentService.createDonationWithPayment(
      campaignId: 1,
      amount: 100.0,
      donorName: 'أحمد محمد',
      note: 'تبرع للطلاب المحتاجين',
    );

    final paymentUrl = result['data']['payment_session']['payment_url'];
    final sessionId = result['data']['payment_session']['session_id'];
    
    // فتح WebView داخل التطبيق ✅
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PaymentWebView(
          paymentUrl: paymentUrl,
          sessionId: sessionId,
          onPaymentResult: (status, sessionId) {
            Navigator.pop(context); // إغلاق WebView
            
            if (status == 'success') {
              _showSuccessDialog(context);
            } else {
              _showCancelDialog(context);
            }
          },
        ),
      ),
    );
    
  } catch (e) {
    print('❌ Error: $e');
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('خطأ في إنشاء التبرع: $e')),
    );
  }
}

void _showSuccessDialog(BuildContext context) {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('تم الدفع بنجاح!'),
      content: const Text('شكراً لك على تبرعك الكريم'),
      actions: [
        TextButton(
          onPressed: () {
            Navigator.pop(context);
            // العودة للصفحة الرئيسية
          },
          child: const Text('موافق'),
        ),
      ],
    ),
  );
}

void _showCancelDialog(BuildContext context) {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('تم إلغاء الدفع'),
      content: const Text('لم يتم إتمام عملية الدفع'),
      actions: [
        TextButton(
          onPressed: () {
            Navigator.pop(context);
          },
          child: const Text('موافق'),
        ),
      ],
    ),
  );
}
```

### **5. استخدام في صفحة التبرع:**

```dart
class DonationScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('التبرع'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              'صندوق رعاية الطلاب',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 20),
            const Text('المبلغ: 100 ريال'),
            const SizedBox(height: 30),
            ElevatedButton(
              onPressed: () => makeDonation(context), // تمرير context
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 15),
              ),
              child: const Text('التبرع الآن'),
            ),
          ],
        ),
      ),
    );
  }
}
```

## 🎯 **تدفق العمل الجديد:**

1. **المستخدم يضغط "التبرع"**
2. **Flutter يرسل طلب إنشاء دفع للـ backend**
3. **Backend يرد بـ payment_url**
4. **Flutter يفتح WebView مع payment_url** ← **هذا هو الحل!**
5. **المستخدم يدفع في Thawani داخل WebView**
6. **Thawani يعيد التوجيه إلى bridge URLs**
7. **Bridge page ترسل رسالة لـ Flutter**
8. **Flutter يتلقى النتيجة ويغلق WebView**
9. **عرض نتيجة الدفع للمستخدم**

## ✅ **المميزات:**

- ✅ **يبقى المستخدم داخل التطبيق**
- ✅ **لا يفتح متصفح خارجي**
- ✅ **تصميم جميل لصفحات النجاح والإلغاء**
- ✅ **إعادة توجيه تلقائي بعد 3 ثواني**
- ✅ **دعم JavaScript للتواصل مع Flutter**
- ✅ **معالجة أخطاء الدفع**
- ✅ **واجهة مستخدم محسنة**

## 🧪 **اختبار النظام:**

1. **تشغيل التطبيق**
2. **الضغط على "التبرع"**
3. **التأكد من فتح WebView داخل التطبيق**
4. **إتمام عملية الدفع**
5. **التأكد من العودة للتطبيق**

## 🎉 **النتيجة:**

**الآن الدفع سيعمل داخل التطبيق ولن يفتح متصفح خارجي!**

---

### **ملاحظات مهمة:**

1. **استخدم `flutter_inappwebview`** - أفضل من `webview_flutter`
2. **اختبر على أجهزة حقيقية** وليس فقط المحاكي
3. **تأكد من إضافة JavaScript channels** للتواصل
4. **استخدم HTTPS في الإنتاج**
5. **لا تستخدم `launchUrl`** - استخدم WebView بدلاً منه
