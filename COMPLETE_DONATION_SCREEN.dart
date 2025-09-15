// مثال كامل لصفحة التبرع مع WebView
// انسخ هذا الكود في ملف Dart في مشروع Flutter

import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class DonationScreen extends StatefulWidget {
  @override
  _DonationScreenState createState() => _DonationScreenState();
}

class _DonationScreenState extends State<DonationScreen> {
  bool _isLoading = false;

  Future<void> _makeDonation() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // إنشاء تبرع مع دفع
      final response = await http.post(
        Uri.parse('http://192.168.1.101:8000/api/v1/donations/with-payment'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'campaign_id': 1,
          'amount': 100.0,
          'donor_name': 'أحمد محمد',
          'note': 'تبرع للطلاب المحتاجين',
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        final paymentUrl = data['data']['payment_session']['payment_url'];
        final sessionId = data['data']['payment_session']['session_id'];
        
        // فتح WebView داخل التطبيق بدلاً من المتصفح الخارجي
        _openPaymentWebView(paymentUrl, sessionId);
      } else {
        throw Exception('Failed to create donation: ${response.body}');
      }
    } catch (e) {
      print('❌ Error: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('خطأ في إنشاء التبرع: $e')),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _openPaymentWebView(String paymentUrl, String sessionId) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PaymentWebView(
          paymentUrl: paymentUrl,
          sessionId: sessionId,
          onPaymentResult: (status, sessionId) {
            Navigator.pop(context); // إغلاق WebView
            
            if (status == 'success') {
              _showSuccessDialog();
            } else {
              _showCancelDialog();
            }
          },
        ),
      ),
    );
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
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.favorite,
              size: 80,
              color: Colors.green,
            ),
            const SizedBox(height: 20),
            const Text(
              'صندوق رعاية الطلاب',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 10),
            const Text(
              'ساهم في دعم الطلاب المحتاجين',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
            const SizedBox(height: 30),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.green.shade200),
              ),
              child: Column(
                children: [
                  const Text(
                    'المبلغ',
                    style: TextStyle(fontSize: 16, color: Colors.grey),
                  ),
                  const SizedBox(height: 5),
                  const Text(
                    '100 ريال عماني',
                    style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.green),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 40),
            ElevatedButton(
              onPressed: _isLoading ? null : _makeDonation,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 50, vertical: 15),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(25),
                ),
              ),
              child: _isLoading
                  ? const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        ),
                        SizedBox(width: 10),
                        Text('جاري المعالجة...'),
                      ],
                    )
                  : const Text(
                      'التبرع الآن',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
            ),
            const SizedBox(height: 20),
            const Text(
              'سيتم فتح صفحة الدفع داخل التطبيق',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }
}

// WebView Widget للدفع
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
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: Colors.green),
                  SizedBox(height: 20),
                  Text('جاري تحميل صفحة الدفع...'),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
