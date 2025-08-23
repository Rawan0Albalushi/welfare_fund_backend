# Thawani Payment Integration

This document describes the integration with Thawani payment gateway for the Student Welfare Fund backend.

## Overview

The integration consists of:
- `PaymentService`: Handles API communication with Thawani
- `PaymentController`: Provides REST API endpoints for payment operations
- Configuration in `config/services.php`

## Setup

### 1. Environment Variables

Add the following to your `.env` file:

```env
THAWANI_API_KEY=your_thawani_api_key_here
THAWANI_BASE_URL=https://checkout.thawani.om/api/v1
```

### 2. Dependencies

The integration uses GuzzleHttp for API requests. It's already included in the project.

## API Endpoints

### 1. Create Checkout Session

**Endpoint:** `POST /api/v1/payments/create`

**Request Body:**
```json
{
    "amount": 10.0,
    "client_reference_id": "unique_reference_id",
    "return_url": "https://your-domain.com/payment/return"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Checkout session created successfully",
    "data": {
        "session_id": "thawani_session_id",
        "payment_url": "https://checkout.thawani.om/pay/thawani_session_id"
    }
}
```

### 2. Get Payment Status

**Endpoint:** `GET /api/v1/payments/status/{sessionId}`

**Response:**
```json
{
    "success": true,
    "message": "Payment status retrieved successfully",
    "data": {
        "session_id": "thawani_session_id",
        "payment_status": "paid",
        "total_amount": 1000,
        "client_reference_id": "unique_reference_id",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

## PaymentService Methods

### createCheckoutSession($amount, $clientReferenceId, $returnUrl)

Creates a new checkout session with Thawani.

**Parameters:**
- `$amount` (float): Amount in OMR (Omani Rial)
- `$clientReferenceId` (string): Unique reference ID for tracking
- `$returnUrl` (string): URL to redirect after payment completion

**Returns:** Array with `session_id` and `payment_url`, or `null` on failure

### retrievePaymentStatus($sessionId)

Retrieves the payment status for a given session.

**Parameters:**
- `$sessionId` (string): The session ID from createCheckoutSession

**Returns:** Array with payment details, or `null` on failure

## Usage Example

```php
use App\Services\PaymentService;

$paymentService = new PaymentService();

// Create a checkout session
$result = $paymentService->createCheckoutSession(
    25.0,
    'donation_' . time(),
    'https://your-domain.com/payment/success'
);

if ($result) {
    $sessionId = $result['session_id'];
    $paymentUrl = $result['payment_url'];
    
    // Redirect user to payment URL
    return redirect($paymentUrl);
}

// Check payment status
$status = $paymentService->retrievePaymentStatus($sessionId);
if ($status && $status['payment_status'] === 'paid') {
    // Payment successful
    echo "Payment completed!";
}
```

## Error Handling

The service includes comprehensive error handling:

- **API Errors**: Logged with detailed context
- **Network Errors**: Caught and logged
- **Invalid Responses**: Validated and logged
- **Validation Errors**: Returned with specific error messages

## Testing

Run the tests to verify the integration:

```bash
php artisan test --filter=PaymentServiceTest
php artisan test --filter=PaymentControllerTest
```

## Thawani API Documentation

For more details about the Thawani API, refer to the official documentation:
- [Thawani API Documentation](https://docs.thawani.om)

## Notes

- Amounts are automatically converted from OMR to baisa (1000 baisa = 1 OMR)
- The service uses a 30-second timeout for API requests
- All API requests include proper headers and authentication
- Failed requests are logged for debugging purposes
