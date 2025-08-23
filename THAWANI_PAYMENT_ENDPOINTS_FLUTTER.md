# 🚀 Thawani Payment Endpoints - Flutter Integration

## 📋 نظرة عامة

هذا الدليل يحتوي على جميع الـ endpoints الخاصة بالدفع عبر ثواني لاستخدامها في تطبيق Flutter.

## 🌐 Base URL

```
http://localhost:8000/api/v1
```

**للإنتاج:**
```
https://your-domain.com/api/v1
```

---

## 🔥 **الـ Endpoints الرئيسية**

### **1. إنشاء تبرع مع دفع مباشر**
**الطريقة الأسهل والأكثر استخداماً**

#### **POST** `/donations/with-payment`

#### **Request Body:**
```json
{
  "campaign_id": 1,
  "amount": 25.0,
  "donor_name": "محمد أحمد",
  "note": "تبرع خيري",
  "type": "quick",
  "success_url": "https://your-app.com/payment/success",
  "cancel_url": "https://your-app.com/payment/cancel"
}
```

#### **Response (Success - 201):**
```json
{
  "message": "Donation and payment session created successfully",
  "data": {
    "donation": {
      "id": 7,
      "donation_id": "DN_f5cc4660-acf0-488c-9237-7501c686a2f6",
      "campaign_id": 1,
      "amount": "25.00",
      "donor_name": "محمد أحمد",
      "note": "تبرع خيري",
      "type": "quick",
      "status": "pending",
      "created_at": "2025-08-23T09:04:16.000000Z"
    },
    "payment_session": {
      "session_id": "checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf",
      "payment_url": "https://uatcheckout.thawani.om/pay/checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf?key=nTFkb94A6HUKTojVerBVIDs8ucMhrX"
    }
  }
}
```

#### **Response (Payment Failed - 201):**
```json
{
  "message": "Donation created but payment session failed",
  "data": {
    "donation": {
      "id": 7,
      "donation_id": "DN_f5cc4660-acf0-488c-9237-7501c686a2f6",
      "amount": "25.00",
      "donor_name": "محمد أحمد",
      "status": "pending"
    },
    "payment_error": "Thawani API request failed: Server error..."
  }
}
```

---

### **2. إنشاء جلسة دفع منفصلة**
**للحالات المتقدمة**

#### **POST** `/payments/create`

#### **Request Body:**
```json
{
  "products": [
    {
      "name": "تبرع خيري",
      "quantity": 1,
      "unit_amount": 25000
    }
  ],
  "client_reference_id": "donation_123456",
  "success_url": "https://your-app.com/payment/success",
  "cancel_url": "https://your-app.com/payment/cancel"
}
```

#### **Response (Success - 200):**
```json
{
  "success": true,
  "session_id": "checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf",
  "payment_url": "https://uatcheckout.thawani.om/pay/checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf?key=nTFkb94A6HUKTojVerBVIDs8ucMhrX"
}
```

---

### **3. التحقق من حالة الدفع**
**لتتبع حالة الدفع**

#### **GET** `/payments/status/{sessionId}`

#### **Response (Success - 200):**
```json
{
  "success": true,
  "payment_status": "paid",
  "session_id": "checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf",
  "raw_response": {
    "session_id": "checkout_JWZ5AcgRr0VNwtGxeGhopYT0W2yQyv2IjQIC9fBB6d0lGT38Mf",
    "payment_status": "paid",
    "total_amount": 25000,
    "client_reference_id": "donation_123456",
    "created_at": "2025-08-23T09:04:16.000000Z",
    "updated_at": "2025-08-23T09:04:16.000000Z"
  }
}
```

---

## 📱 **كود Flutter**

### **1. إنشاء تبرع مع دفع**

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class PaymentService {
  static const String baseUrl = 'http://localhost:8000/api/v1';
  
  static Future<Map<String, dynamic>> createDonationWithPayment({
    required int campaignId,
    required double amount,
    required String donorName,
    String? note,
    String type = 'quick',
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/donations/with-payment'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'campaign_id': campaignId,
          'amount': amount,
          'donor_name': donorName,
          'note': note,
          'type': type,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        
        // فتح صفحة الدفع
        if (data['data']['payment_session'] != null) {
          final paymentUrl = data['data']['payment_session']['payment_url'];
          // استخدم url_launcher لفتح صفحة الدفع
          // await launchUrl(Uri.parse(paymentUrl));
        }
        
        return data;
      } else {
        throw Exception('Failed to create donation: ${response.body}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}
```

### **2. إنشاء جلسة دفع منفصلة**

```dart
static Future<Map<String, dynamic>> createPaymentSession({
  required String productName,
  required double amount,
  required String referenceId,
}) async {
  try {
    final response = await http.post(
      Uri.parse('$baseUrl/payments/create'),
      headers: {
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'products': [
          {
            'name': productName,
            'quantity': 1,
            'unit_amount': (amount * 1000).toInt(), // تحويل إلى baisa
          }
        ],
        'client_reference_id': referenceId,
        'success_url': 'https://your-app.com/payment/success',
        'cancel_url': 'https://your-app.com/payment/cancel',
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to create payment session: ${response.body}');
    }
  } catch (e) {
    throw Exception('Network error: $e');
  }
}
```

### **3. التحقق من حالة الدفع**

```dart
static Future<Map<String, dynamic>> getPaymentStatus(String sessionId) async {
  try {
    final response = await http.get(
      Uri.parse('$baseUrl/payments/status/$sessionId'),
      headers: {
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to get payment status: ${response.body}');
    }
  } catch (e) {
    throw Exception('Network error: $e');
  }
}
```

### **4. استخدام كامل في Flutter**

```dart
class DonationScreen extends StatefulWidget {
  @override
  _DonationScreenState createState() => _DonationScreenState();
}

class _DonationScreenState extends State<DonationScreen> {
  double amount = 25.0;
  String donorName = '';
  String note = '';
  bool isLoading = false;

  Future<void> _createDonation() async {
    setState(() {
      isLoading = true;
    });

    try {
      final result = await PaymentService.createDonationWithPayment(
        campaignId: 1,
        amount: amount,
        donorName: donorName,
        note: note,
      );

      // فتح صفحة الدفع
      if (result['data']['payment_session'] != null) {
        final paymentUrl = result['data']['payment_session']['payment_url'];
        await launchUrl(Uri.parse(paymentUrl));
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('تم إنشاء التبرع بنجاح!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('خطأ: $e')),
      );
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('تبرع خيري')),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              decoration: InputDecoration(labelText: 'اسم المتبرع'),
              onChanged: (value) => donorName = value,
            ),
            SizedBox(height: 16),
            TextField(
              decoration: InputDecoration(labelText: 'المبلغ (OMR)'),
              keyboardType: TextInputType.number,
              onChanged: (value) => amount = double.tryParse(value) ?? 0,
            ),
            SizedBox(height: 16),
            TextField(
              decoration: InputDecoration(labelText: 'ملاحظات'),
              onChanged: (value) => note = value,
            ),
            SizedBox(height: 32),
            ElevatedButton(
              onPressed: isLoading ? null : _createDonation,
              child: isLoading 
                ? CircularProgressIndicator() 
                : Text('تبرع الآن'),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## 🔧 **ملاحظات مهمة**

### **1. تحويل العملة**
- **OMR إلى Baisa:** اضرب المبلغ في 1000
- **مثال:** 25 OMR = 25000 baisa

### **2. URLs الافتراضية**
```dart
// يمكنك تخصيص هذه URLs
'success_url': 'https://your-app.com/payment/success',
'cancel_url': 'https://your-app.com/payment/cancel',
```

### **3. معالجة الأخطاء**
```dart
try {
  final result = await PaymentService.createDonationWithPayment(...);
} catch (e) {
  // معالجة الخطأ
  print('Error: $e');
}
```

### **4. تتبع حالة الدفع**
```dart
// بعد إنشاء التبرع، يمكنك تتبع حالة الدفع
Timer.periodic(Duration(seconds: 5), (timer) async {
  final status = await PaymentService.getPaymentStatus(sessionId);
  if (status['payment_status'] == 'paid') {
    timer.cancel();
    // تحديث UI
  }
});
```

---

## 📊 **حالات الدفع**

| الحالة | الوصف |
|--------|--------|
| `pending` | في انتظار الدفع |
| `paid` | تم الدفع بنجاح |
| `failed` | فشل في الدفع |
| `expired` | انتهت صلاحية الجلسة |
| `cancelled` | تم إلغاء الدفع |

---

## 🎯 **الخلاصة**

**الـ Endpoint الرئيسي للاستخدام في Flutter:**
```
POST /api/v1/donations/with-payment
```

**هذا الـ endpoint يقوم بـ:**
1. ✅ إنشاء التبرع في قاعدة البيانات
2. ✅ إنشاء جلسة دفع في ثواني
3. ✅ إرجاع رابط الدفع
4. ✅ معالجة الأخطاء

**النظام جاهز للاستخدام في Flutter!** 🚀
