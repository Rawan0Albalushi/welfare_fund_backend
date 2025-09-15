# 🚀 دليل ThawaniPaymentService الجديد

## 📋 نظرة عامة

تم إنشاء `ThawaniPaymentService` جديد مع واجهة برمجة تطبيقات محسنة تتوافق مع متطلباتك المحددة.

## 🔧 الإعداد

### 1. **تحديث ملف .env**

```env
# Thawani Payment Gateway Configuration
THAWANI_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_BASE_URL=https://checkout.thawani.om/api/v1
```

### 2. **مسح الكاش**

```bash
php artisan config:clear
php artisan cache:clear
```

## 🧪 اختبار الخدمة

### **اختبار الخدمة الجديدة:**

```bash
php artisan thawani:test-service
```

### **اختبار بمبلغ محدد:**

```bash
php artisan thawani:test-service --amount=5.0 --reference=test_donation
```

## 📊 واجهة برمجة التطبيقات

### **1. إنشاء جلسة دفع**
**POST** `/api/v1/payments/create`

#### **Request Body:**
```json
{
  "products": [
    {
      "name": "Donation",
      "quantity": 1,
      "unit_amount": 5000
    }
  ],
  "client_reference_id": "donation_123456",
  "success_url": "https://your-app.com/success",
  "cancel_url": "https://your-app.com/cancel"
}
```

#### **Response:**
```json
{
  "success": true,
  "session_id": "sess_12345",
  "payment_url": "https://checkout.thawani.om/pay/sess_12345?key=pk_test_xxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### **2. التحقق من حالة الدفع**
**GET** `/api/v1/payments/status/{sessionId}`

#### **Response:**
```json
{
  "success": true,
  "payment_status": "paid",
  "raw_response": {
    "session_id": "sess_12345",
    "payment_status": "paid",
    "total_amount": 5000,
    "client_reference_id": "donation_123456",
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z"
  }
}
```

## 💻 استخدام الخدمة في الكود

### **إنشاء جلسة دفع:**

```php
use App\Services\ThawaniPaymentService;

$thawaniService = new ThawaniPaymentService();

$products = [
    [
        'name' => 'Donation',
        'quantity' => 1,
        'unit_amount' => 5000, // 5 OMR in baisa
    ]
];

try {
    $result = $thawaniService->createSession(
        $products,
        'donation_' . time(),
        'https://your-app.com/success',
        'https://your-app.com/cancel'
    );
    
    $sessionId = $result['session_id'];
    $paymentUrl = $result['payment_url'];
    
    // Redirect user to payment URL
    return redirect($paymentUrl);
    
} catch (\Exception $e) {
    // Handle error
    return response()->json(['error' => $e->getMessage()], 500);
}
```

### **التحقق من حالة الدفع:**

```php
try {
    $sessionData = $thawaniService->retrieveSession($sessionId);
    
    $paymentStatus = $sessionData['payment_status'];
    $totalAmount = $sessionData['total_amount'];
    
    if ($paymentStatus === 'paid') {
        // Payment successful
        return response()->json(['status' => 'success']);
    } else {
        // Payment pending or failed
        return response()->json(['status' => $paymentStatus]);
    }
    
} catch (\Exception $e) {
    // Handle error
    return response()->json(['error' => $e->getMessage()], 500);
}
```

## 📱 اختبار من Flutter

### **إنشاء جلسة دفع:**

```dart
final response = await http.post(
  Uri.parse('http://192.168.100.105:8000/api/v1/payments/create'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'products': [
      {
        'name': 'Donation',
        'quantity': 1,
        'unit_amount': 5000, // 5 OMR in baisa
      }
    ],
    'client_reference_id': 'donation_${DateTime.now().millisecondsSinceEpoch}',
    'success_url': 'https://your-app.com/success',
    'cancel_url': 'https://your-app.com/cancel',
  }),
);

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  final sessionId = data['session_id'];
  final paymentUrl = data['payment_url'];
  
  // Open payment URL in browser or WebView
  launchUrl(Uri.parse(paymentUrl));
}
```

### **التحقق من حالة الدفع:**

```dart
final statusResponse = await http.get(
  Uri.parse('http://192.168.100.105:8000/api/v1/payments/status/$sessionId'),
);

if (statusResponse.statusCode == 200) {
  final data = jsonDecode(statusResponse.body);
  final paymentStatus = data['payment_status'];
  final rawResponse = data['raw_response'];
  
  print('Payment Status: $paymentStatus');
  print('Full Response: $rawResponse');
}
```

## 🔍 استكشاف الأخطاء

### **المشكلة: "THAWANI_SECRET_KEY is not configured"**

#### الحل:
1. تأكد من إضافة `THAWANI_SECRET_KEY` في ملف `.env`
2. مسح الكاش: `php artisan config:clear`

### **المشكلة: "THAWANI_PUBLISHABLE_KEY is not configured"**

#### الحل:
1. تأكد من إضافة `THAWANI_PUBLISHABLE_KEY` في ملف `.env`
2. مسح الكاش: `php artisan config:clear`

### **المشكلة: خطأ في التحقق من صحة البيانات**

#### الحل:
تأكد من أن البيانات المرسلة تتوافق مع الشكل المطلوب:

```json
{
  "products": [
    {
      "name": "string",
      "quantity": "integer",
      "unit_amount": "integer"
    }
  ],
  "client_reference_id": "string",
  "success_url": "valid URL",
  "cancel_url": "valid URL"
}
```

## 📊 تحويل المبالغ

### **من OMR إلى Baisa:**
```php
$amountInOMR = 5.0;
$amountInBaisa = (int)($amountInOMR * 1000); // 5000 baisa
```

### **من Baisa إلى OMR:**
```php
$amountInBaisa = 5000;
$amountInOMR = $amountInBaisa / 1000; // 5.0 OMR
```

## ✅ قائمة التحقق

- [ ] تم إضافة `THAWANI_SECRET_KEY` في `.env`
- [ ] تم إضافة `THAWANI_PUBLISHABLE_KEY` في `.env`
- [ ] تم مسح الكاش
- [ ] تم اختبار الخدمة: `php artisan thawani:test-service`
- [ ] تم اختبار النقاط النهائية من Flutter
- [ ] تم التحقق من الـ logs

## 🚀 الأوامر المتاحة

```bash
# اختبار الخدمة الجديدة
php artisan thawani:test-service

# اختبار بمبلغ محدد
php artisan thawani:test-service --amount=5.0 --reference=test_donation

# اختبار الخدمة القديمة (للتوافق)
php artisan thawani:test-official
```

## 📚 المراجع

- [Thawani API Documentation](https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api/5534c91789a48-thawani-e-commerce-api)
- [Thawani Dashboard](https://dashboard.thawani.om)
- [Thawani Registration](https://thawani.om/register)
