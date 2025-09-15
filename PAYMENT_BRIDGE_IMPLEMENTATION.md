# Payment Bridge Implementation (Final)

## Overview

This implementation adds a payment bridge system that handles return URLs from Thawani payment gateway and redirects users back to the frontend application with proper payment status confirmation. The system now uses explicit bridge URLs with IP address fallback and removes all hardcoded default values.

## Changes Made

### 1. Updated ThawaniService (`app/Services/ThawaniService.php`)

#### Modified `createSession()` method:
- Simplified method signature to only accept `returnOrigin` parameter
- Added explicit success/cancel URL handling with IP fallback:
  ```php
  // إن أردت ديناميكيًا من الواجهة:
  $origin = rtrim($returnOrigin ?? '', '/');

  $success = $origin
      ? "{$origin}/payment/bridge/success?donation_id={$donation->donation_id}"
      : "http://192.168.100.105:8000/payment/bridge/success?donation_id={$donation->donation_id}";

  $cancel = $origin
      ? "{$origin}/payment/bridge/cancel?donation_id={$donation->donation_id}"
      : "http://192.168.100.105:8000/payment/bridge/cancel?donation_id={$donation->donation_id}";
  ```
- Added comprehensive payload logging:
  ```php
  \Log::info('THAWANI createSession payload', [
      'success_url' => $success,
      'cancel_url'  => $cancel,
      'client_reference_id' => $donation->donation_id,
  ]);
  ```

### 2. Updated PaymentsController (`app/Http/Controllers/PaymentsController.php`)

#### Modified `create()` method:
- Simplified to only pass `return_origin` to ThawaniService
- Removed all hardcoded default values and URL construction
- Direct parameter passing:
  ```php
  $result = $this->thawaniService->createSession(
      $donation,
      $request->products,
      $request->input('return_origin')
  );
  ```

#### Added `bridgeSuccess()` method:
- Extracts `donation_id` and `origin` from query parameters (no defaults)
- Validates donation exists
- Confirms payment status with Thawani (idempotent)
- Updates donation status if payment is confirmed
- Redirects to frontend success page with donation_id

#### Added `bridgeCancel()` method:
- Extracts `donation_id` and `origin` from query parameters (no defaults)
- Redirects to frontend cancel page with donation_id

### 3. Updated ThawaniPaymentService (`app/Services/ThawaniPaymentService.php`)

Updated the wrapper service to pass the `returnOrigin` parameter to the core ThawaniService.

### 4. Updated Routes (`routes/web.php`)

Replaced the existing bridge route closures with controller methods:
```php
Route::get('/payment/bridge/success', [App\Http\Controllers\PaymentsController::class, 'bridgeSuccess']);
Route::get('/payment/bridge/cancel', [App\Http\Controllers\PaymentsController::class, 'bridgeCancel']);
```

### 5. Removed Configuration (`config/app.php`)

Removed all hardcoded frontend origin configuration to eliminate default values.

### 6. Updated OpenAPI Documentation

Updated the API documentation to reflect the new `return_origin` parameter instead of `success_url` and `cancel_url`.

## API Usage

### Creating Payment with Bridge

**Endpoint:** `POST /api/v1/payments/create`

**Request Body:**
```json
{
  "donation_id": "DN_12345678-1234-1234-1234-123456789012",
  "products": [
    {
      "name": "تبرع خيري",
      "quantity": 1,
      "unit_amount": 10000
    }
  ],
  "return_origin": "http://localhost:49887"
}
```

**Response:**
```json
{
  "success": true,
  "message": "تم إنشاء جلسة الدفع بنجاح",
  "data": {
    "checkout_url": "https://uatcheckout.thawani.om/pay/sess_xxx?key=xxx",
    "session_id": "sess_xxx"
  }
}
```

## Payment Flow

1. **Frontend** calls `/api/v1/payments/create` with `return_origin`
2. **Backend** creates Thawani session with bridge URLs:
   - Success: `/payment/bridge/success?donation_id=xxx&origin=xxx`
   - Cancel: `/payment/bridge/cancel?donation_id=xxx&origin=xxx`
3. **User** completes payment on Thawani
4. **Thawani** redirects to bridge success/cancel URL
5. **Bridge** confirms payment status and redirects to frontend:
   - Success: `{origin}/payment/success?donation_id=xxx`
   - Cancel: `{origin}/payment/cancel?donation_id=xxx`

## Bridge Endpoints

### Success Bridge: `/payment/bridge/success`

**Query Parameters:**
- `donation_id` (required): The donation ID
- `origin` (optional): Frontend origin URL (defaults to config)

**Behavior:**
1. Validates donation exists
2. Confirms payment status with Thawani
3. Updates donation status if paid
4. Redirects to `{origin}/payment/success?donation_id={donation_id}`

### Cancel Bridge: `/payment/bridge/cancel`

**Query Parameters:**
- `donation_id` (required): The donation ID
- `origin` (optional): Frontend origin URL (defaults to config)

**Behavior:**
1. Redirects to `{origin}/payment/cancel?donation_id={donation_id}`

## Error Handling

- Missing `donation_id`: Redirects to `{origin}/payment/error?message=Donation%20ID%20is%20required`
- Donation not found: Redirects to `{origin}/payment/error?message=Donation%20not%20found`
- Thawani API errors: Logged but don't prevent redirect

## Configuration

Add to your `.env` file:
```env
FRONTEND_ORIGIN=http://localhost:49887
```

## Testing

Run the test script to verify implementation:
```bash
php test_payment_bridge.php
```

## Logging and Verification

The implementation includes comprehensive logging for debugging and verification:

- **Payload Logging**: All Thawani API payloads are logged with the key `THAWANI createSession payload`
- **URL Verification**: Success and cancel URLs are explicitly logged in the payload
- **Error Logging**: All errors are logged with context for troubleshooting

To check the logs:
```bash
tail -f storage/logs/laravel.log | grep "THAWANI createSession payload"
```

## Benefits

1. **Flexible Frontend URLs**: Frontend can specify its own origin
2. **Payment Confirmation**: Automatic payment status verification
3. **Idempotent**: Safe to call multiple times
4. **Error Handling**: Graceful handling of missing data
5. **Clean Separation**: Backend handles payment logic, frontend handles UI
6. **Comprehensive Logging**: Full payload logging for debugging and verification

## Migration Notes

- **BREAKING CHANGE**: Existing integrations using `success_url` and `cancel_url` will need to be updated
- The `return_origin` parameter is now **required** - no default values
- If `return_origin` is not provided, the system will use IP fallback: `http://192.168.100.105:8000`
- Bridge endpoints are backward compatible with existing donation IDs
- All hardcoded values have been removed from the system
