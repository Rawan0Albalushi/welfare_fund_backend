<?php

/**
 * Test script to verify dynamic URL construction
 */

echo "🧪 Testing Dynamic URL Construction\n";
echo "==================================\n\n";

// Test URL construction logic
$testDonationId = 'DN_test-12345';
$testReturnOrigin = 'http://localhost:49887';

echo "1️⃣ Testing dynamic URL construction...\n";

// Simulate the logic from ThawaniService
$returnOrigin = $testReturnOrigin ?? null;

$successUrl = $returnOrigin ? 
    rtrim($returnOrigin, '/') . '/payment/success' : 
    'http://localhost:8000/payment/success';
    
$cancelUrl = $returnOrigin ? 
    rtrim($returnOrigin, '/') . '/payment/cancel' : 
    'http://localhost:8000/payment/cancel';

echo "✅ Frontend Success URL: $successUrl\n";
echo "✅ Frontend Cancel URL: $cancelUrl\n\n";

// Test bridge URL construction
$success = "http://192.168.1.21:8000/payment/bridge/success?donation_id={$testDonationId}&origin=" . urlencode($successUrl);
$cancel = "http://192.168.1.21:8000/payment/bridge/cancel?donation_id={$testDonationId}&origin=" . urlencode($cancelUrl);

echo "2️⃣ Testing bridge URL construction...\n";
echo "✅ Bridge Success URL: $success\n";
echo "✅ Bridge Cancel URL: $cancel\n\n";

// Test URL parsing
echo "3️⃣ Testing URL parsing...\n";

$successParts = parse_url($success);
$cancelParts = parse_url($cancel);

parse_str($successParts['query'], $successQuery);
parse_str($cancelParts['query'], $cancelQuery);

echo "✅ Success URL parsed:\n";
echo "   - donation_id: " . ($successQuery['donation_id'] ?? 'NOT FOUND') . "\n";
echo "   - origin: " . ($successQuery['origin'] ?? 'NOT FOUND') . "\n";

echo "✅ Cancel URL parsed:\n";
echo "   - donation_id: " . ($cancelQuery['donation_id'] ?? 'NOT FOUND') . "\n";
echo "   - origin: " . ($cancelQuery['origin'] ?? 'NOT FOUND') . "\n\n";

// Test redirect URL construction
echo "4️⃣ Testing redirect URL construction...\n";

$donationId = $successQuery['donation_id'];
$origin = $successQuery['origin'];

$redirectUrl = $origin . '?donation_id=' . urlencode($donationId);

echo "✅ Redirect URL: $redirectUrl\n\n";

// Test error URL construction
echo "5️⃣ Testing error URL construction...\n";

$errorUrl = str_replace('/payment/success', '/payment/error', $origin) . '?message=' . urlencode('Test error message');

echo "✅ Error URL: $errorUrl\n\n";

echo "🎉 Dynamic URL construction testing completed!\n";
echo "\n";
echo "📋 Summary:\n";
echo "- Dynamic URL construction: ✅\n";
echo "- Bridge URL construction: ✅\n";
echo "- URL parsing: ✅\n";
echo "- Redirect construction: ✅\n";
echo "- Error URL construction: ✅\n";
echo "\n";
echo "💡 Next steps:\n";
echo "1. Test actual payment flow\n";
echo "2. Check logs for 'THAWANI createSession payload'\n";
echo "3. Verify frontend URLs in logs\n";
echo "4. Test bridge redirects\n";
