# 🚀 إعداد سريع لـ WebView في Flutter

## 🎯 **المشكلة:**
الدفع يفتح في متصفح خارجي بدلاً من البقاء داخل التطبيق.

## ✅ **الحل السريع:**

### **1. إضافة Dependencies:**

```yaml
# pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  flutter_inappwebview: ^6.0.0
  http: ^1.1.0
```

### **2. استبدال الكود الحالي:**

**بدلاً من:**
```dart
// ❌ هذا يفتح متصفح خارجي
await launchUrl(Uri.parse(paymentUrl));
```

**استخدم:**
```dart
// ✅ هذا يفتح WebView داخل التطبيق
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => PaymentWebView(
      paymentUrl: paymentUrl,
      sessionId: sessionId,
      onPaymentResult: (status, sessionId) {
        Navigator.pop(context);
        if (status == 'success') {
          // عرض رسالة نجاح
        } else {
          // عرض رسالة إلغاء
        }
      },
    ),
  ),
);
```

### **3. إضافة PaymentWebView Widget:**

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
              child: CircularProgressIndicator(color: Colors.green),
            ),
        ],
      ),
    );
  }
}
```

## 🎯 **النتيجة:**

- ✅ **يبقى المستخدم داخل التطبيق**
- ✅ **لا يفتح متصفح خارجي**
- ✅ **تجربة مستخدم أفضل**
- ✅ **تحكم كامل في عملية الدفع**

## 📱 **ملف كامل:**

انظر إلى `COMPLETE_DONATION_SCREEN.dart` للحصول على مثال كامل.

---

**هذا كل ما تحتاجه! الآن الدفع سيعمل داخل التطبيق.**
