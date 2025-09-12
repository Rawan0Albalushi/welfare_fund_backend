# 🔧 حل مشكلة فتح متصفح خارجي في الدفع

## 🎯 **المشكلة:**
- عند الضغط على الدفع، يفتح متصفح خارجي بدلاً من البقاء داخل التطبيق
- المستخدم يخرج من التطبيق أثناء عملية الدفع

## ✅ **الحل: استخدام WebView داخل التطبيق**

### **1. إضافة Dependencies في pubspec.yaml:**

```yaml
dependencies:
  flutter:
    sdk: flutter
  webview_flutter: ^4.4.2
  # أو الأفضل:
  flutter_inappwebview: ^6.0.0
```

### **2. إنشاء Payment WebView Widget:**

```dart
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

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
  late final WebViewController _controller;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initializeWebView();
  }

  void _initializeWebView() {
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            setState(() {
              _isLoading = true;
            });
          },
          onPageFinished: (String url) {
            setState(() {
              _isLoading = false;
            });
          },
          onNavigationRequest: (NavigationRequest request) {
            // التحقق من URLs النجاح والإلغاء
            if (request.url.contains('/payment/bridge/success')) {
              widget.onPaymentResult('success', widget.sessionId);
              return NavigationDecision.prevent;
            } else if (request.url.contains('/payment/bridge/cancel')) {
              widget.onPaymentResult('cancel', widget.sessionId);
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.paymentUrl));
  }

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
          WebViewWidget(controller: _controller),
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

### **3. استخدام WebView في صفحة الدفع:**

```dart
class PaymentScreen extends StatefulWidget {
  @override
  _PaymentScreenState createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  bool _isLoading = false;
  String? _sessionId;
  String? _paymentUrl;

  Future<void> _createPayment() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // إنشاء جلسة الدفع
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/payments/create'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'products': [
            {
              'name': 'تبرع',
              'quantity': 1,
              'unit_amount': 1000, // 10 ريال بالبيسة
            }
          ],
          'program_id': 1,
          'donor_name': 'اسم المتبرع',
          'type': 'quick',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _sessionId = data['data']['payment_session']['session_id'];
        _paymentUrl = data['data']['payment_session']['redirect_url'];

        // فتح WebView للدفع بدلاً من المتصفح الخارجي
        _openPaymentWebView();
      }
    } catch (e) {
      print('خطأ في إنشاء الدفع: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _openPaymentWebView() {
    if (_sessionId != null && _paymentUrl != null) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => PaymentWebView(
            paymentUrl: _paymentUrl!,
            sessionId: _sessionId!,
            onPaymentResult: _handlePaymentResult,
          ),
        ),
      );
    }
  }

  void _handlePaymentResult(String status, String sessionId) {
    Navigator.pop(context); // إغلاق WebView

    if (status == 'success') {
      _showSuccessDialog();
    } else {
      _showCancelDialog();
    }
  }

  void _showSuccessDialog() {
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

  void _showCancelDialog() {
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
            const Text('المبلغ: 10 ريال'),
            const SizedBox(height: 30),
            ElevatedButton(
              onPressed: _isLoading ? null : _createPayment,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 15),
              ),
              child: _isLoading
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text('التبرع الآن'),
            ),
          ],
        ),
      ),
    );
  }
}
```

## 🚀 **استخدام flutter_inappwebview (الأفضل):**

```dart
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('إتمام الدفع'),
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
      ),
      body: InAppWebView(
        initialUrlRequest: URLRequest(url: WebUri(widget.paymentUrl)),
        onWebViewCreated: (controller) {
          webViewController = controller;
        },
        onLoadStart: (controller, url) {
          // التحقق من URLs النجاح والإلغاء
          if (url.toString().contains('/payment/bridge/success')) {
            widget.onPaymentResult('success', widget.sessionId);
          } else if (url.toString().contains('/payment/bridge/cancel')) {
            widget.onPaymentResult('cancel', widget.sessionId);
          }
        },
        onReceivedServerTrustAuthRequest: (controller, challenge) {
          return ServerTrustAuthResponse(action: ServerTrustAuthResponseAction.PROCEED);
        },
      ),
    );
  }
}
```

## 🔧 **إعداد Backend:**

### **تحديث ملف .env:**

```env
# Thawani URLs للـ WebView
THAWANI_SUCCESS_URL=http://your-domain.com/payment/bridge/success
THAWANI_CANCEL_URL=http://your-domain.com/payment/bridge/cancel
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
- ✅ **تصميم جميل لصفحات النجاح والإلغاء**
- ✅ **إعادة توجيه تلقائي بعد 3 ثواني**
- ✅ **دعم JavaScript للتواصل مع Flutter**
- ✅ **معالجة أخطاء الدفع**
- ✅ **واجهة مستخدم محسنة**

## 🧪 **اختبار النظام:**

```bash
# اختبار صفحة النجاح
curl "http://localhost:8000/payment/bridge/success?session_id=test123"

# اختبار صفحة الإلغاء
curl "http://localhost:8000/payment/bridge/cancel?session_id=test123"
```

---

## 🎉 **النتيجة:**

**الآن الدفع سيعمل داخل التطبيق ولن يفتح متصفح خارجي!**

### **نصائح مهمة:**

1. **استخدم `flutter_inappwebview` بدلاً من `webview_flutter`** - أفضل أداء وميزات
2. **تأكد من إضافة JavaScript channels** للتواصل مع Flutter
3. **اختبر على أجهزة حقيقية** وليس فقط المحاكي
4. **استخدم HTTPS** في الإنتاج لضمان الأمان
