# 🚀 ThawaniService - Laravel Payment Integration

## 📋 Overview

A comprehensive Laravel service class for Thawani payment gateway integration using Laravel's HTTP client. This service provides methods for creating payment sessions, retrieving session details, and processing refunds.

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Thawani API Configuration
THAWANI_API_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
THAWANI_BASE_URL=https://checkout.thawani.om/api/v1

# Optional: Additional keys for other services
THAWANI_SECRET_KEY=rRQ26GcsZzoEhbrP2HZvLYDbn9C9et
THAWANI_PUBLISHABLE_KEY=HGvTMLDssJghr9t1N9gr4DVYt0qyBy
```

### Configuration File (config/services.php)

```php
'thawani' => [
    'api_key' => env('THAWANI_API_KEY'),
    'secret_key' => env('THAWANI_SECRET_KEY'),
    'publishable_key' => env('THAWANI_PUBLISHABLE_KEY'),
    'base_url' => env('THAWANI_BASE_URL', 'https://checkout.thawani.om/api/v1'),
],
```

## 🔧 Service Class

### File: `app/Services/ThawaniService.php`

The service class provides the following methods:

#### 1. `createSession($clientReferenceId, $products, $successUrl, $cancelUrl)`

Creates a payment session with Thawani API.

```php
use App\Services\ThawaniService;

$thawaniService = new ThawaniService();

$products = [
    [
        'name' => 'Donation',
        'quantity' => 1,
        'unit_amount' => 5000, // 5 OMR in baisa
    ]
];

try {
    $sessionData = $thawaniService->createSession(
        'donation_' . time(),
        $products,
        'https://your-app.com/success',
        'https://your-app.com/cancel'
    );
    
    echo "Session ID: " . $sessionData['session_id'];
    echo "Payment URL: " . $sessionData['payment_url'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### 2. `getSessionDetails($sessionId)`

Retrieves session details including payment status.

```php
try {
    $sessionDetails = $thawaniService->getSessionDetails('sess_12345');
    
    echo "Payment Status: " . $sessionDetails['payment_status'];
    echo "Client Reference: " . $sessionDetails['client_reference_id'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### 3. `refundPayment($chargeId, $reason = null)`

Processes a refund for a payment.

```php
try {
    $refundData = $thawaniService->refundPayment(
        'ch_12345',
        'Customer requested refund'
    );
    
    echo "Refund Status: " . $refundData['status'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### 4. `testConnection()`

Tests the connection to Thawani API.

```php
try {
    $isConnected = $thawaniService->testConnection();
    if ($isConnected) {
        echo "✅ Connection successful!";
    } else {
        echo "❌ Connection failed!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## 🧪 Testing

### Test Command

```bash
# Basic test
php artisan thawani:test

# Test with custom amount
php artisan thawani:test --amount=5.0

# Test with custom reference
php artisan thawani:test --reference=donation --amount=10.0
```

### Test Output Example

```
🧪 Testing ThawaniService...

🔍 Test 1: Testing connection...
✅ Connection successful!

🔍 Test 2: Creating payment session...
Amount: 1.0 OMR (1000 baisa)
Reference: test_1755897936
Products: [
    {
        "name": "Test Donation",
        "quantity": 1,
        "unit_amount": 1000
    }
]
✅ Session created successfully!
Session ID: sess_12345
Payment URL: https://checkout.thawani.om/pay/sess_12345?key=...

🔍 Test 3: Getting session details...
✅ Session details retrieved successfully!
Payment Status: pending
Client Reference ID: test_1755897936

🎉 All tests completed successfully!
```

## 📱 Usage in Controllers

### Example Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    private ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    public function createPayment(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'reference' => 'required|string|max:255',
        ]);

        try {
            $products = [
                [
                    'name' => 'Donation',
                    'quantity' => 1,
                    'unit_amount' => (int)($request->amount * 1000), // Convert to baisa
                ]
            ];

            $sessionData = $this->thawaniService->createSession(
                $request->reference,
                $products,
                route('payment.success'),
                route('payment.cancel')
            );

            return response()->json([
                'success' => true,
                'session_id' => $sessionData['session_id'],
                'payment_url' => $sessionData['payment_url'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPaymentStatus(string $sessionId): JsonResponse
    {
        try {
            $sessionDetails = $this->thawaniService->getSessionDetails($sessionId);

            return response()->json([
                'success' => true,
                'payment_status' => $sessionDetails['payment_status'],
                'session_data' => $sessionDetails,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

## 🔄 API Routes

```php
// routes/api.php

Route::prefix('v1')->group(function () {
    Route::post('/payments/create', [PaymentController::class, 'createPayment']);
    Route::get('/payments/status/{sessionId}', [PaymentController::class, 'getPaymentStatus']);
});
```

## 📊 Error Handling

The service throws exceptions with descriptive messages:

- **Configuration Errors**: `THAWANI_API_KEY is not configured`
- **Network Errors**: `Request failed: 401 - Api key invalid`
- **Response Errors**: `Invalid response format: session_id not found`

### Error Response Example

```json
{
    "success": false,
    "message": "Failed to create payment session: Request failed: 401 - Api key invalid"
}
```

## 🔍 Logging

All requests and responses are logged using Laravel's Log facade:

```php
// Log levels used:
Log::info()  // Successful requests and responses
Log::error() // Failed requests and exceptions
```

### Log Example

```php
// Request log
[2025-08-22 21:19:36] local.INFO: Thawani createSession request {
    "payload": {
        "client_reference_id": "test_1755897936",
        "mode": "payment",
        "products": [...]
    }
}

// Response log
[2025-08-22 21:19:36] local.INFO: Thawani createSession response {
    "status_code": 200,
    "response": {...}
}
```

## 🚀 Flutter Integration

### Example Flutter Code

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<Map<String, dynamic>> createPayment(double amount) async {
  final response = await http.post(
    Uri.parse('http://192.168.100.105:8000/api/v1/payments/create'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'amount': amount,
      'reference': 'donation_${DateTime.now().millisecondsSinceEpoch}',
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return {
      'success': true,
      'session_id': data['session_id'],
      'payment_url': data['payment_url'],
    };
  } else {
    throw Exception('Failed to create payment');
  }
}

Future<Map<String, dynamic>> getPaymentStatus(String sessionId) async {
  final response = await http.get(
    Uri.parse('http://192.168.100.105:8000/api/v1/payments/status/$sessionId'),
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to get payment status');
  }
}
```

## 🔧 Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check if API key is correct and valid
2. **500 Internal Server Error**: Verify payload format and required fields
3. **Connection Timeout**: Check network connectivity and API endpoint
4. **Invalid Response Format**: Ensure API version compatibility

### Debug Commands

```bash
# Test connection
php artisan thawani:test

# Check configuration
php artisan config:show services.thawani

# Clear cache
php artisan config:clear
php artisan cache:clear
```

## 📞 Support

- **Thawani Documentation**: https://thawani-technologies.stoplight.io/docs/thawani-ecommerce-api
- **Thawani Dashboard**: https://dashboard.thawani.om
- **Thawani Support**: support@thawani.om

---

**🎉 The ThawaniService is ready for production use!**
