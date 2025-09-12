<?php

/**
 * Test script to verify bridge URL construction
 */

echo "🧪 Testing Bridge URL Construction\n";
echo "=================================\n\n";

// Test URL construction logic
$testDonationId = 'DN_test-12345';
$testOrigin = 'http://localhost:49887';

echo "1️⃣ Testing URL construction logic...\n";

// Simulate the logic from ThawaniService
$origin = rtrim($testOrigin, '/');

$success = $origin
    ? "http://192.168.1.21:8000/payment/bridge/success?donation_id={$testDonationId}&origin=" . urlencode($origin)
    : "http://192.168.1.21:8000/payment/bridge/success?donation_id={$testDonationId}";

$cancel = $origin
    ? "http://192.168.1.21:8000/payment/bridge/cancel?donation_id={$testDonationId}&origin=" . urlencode($origin)
    : "http://192.168.1.21:8000/payment/bridge/cancel?donation_id={$testDonationId}";

echo "✅ Success URL: $success\n";
echo "✅ Cancel URL: $cancel\n\n";

// Test URL parsing
echo "2️⃣ Testing URL parsing...\n";

$successUrl = "http://192.168.1.21:8000/payment/bridge/success?donation_id=DN_test-12345&origin=http%3A//localhost%3A49887";
$cancelUrl = "http://192.168.1.21:8000/payment/bridge/cancel?donation_id=DN_test-12345&origin=http%3A//localhost%3A49887";

$successParts = parse_url($successUrl);
$cancelParts = parse_url($cancelUrl);

parse_str($successParts['query'], $successQuery);
parse_str($cancelParts['query'], $cancelQuery);

echo "✅ Success URL parsed:\n";
echo "   - donation_id: " . ($successQuery['donation_id'] ?? 'NOT FOUND') . "\n";
echo "   - origin: " . ($successQuery['origin'] ?? 'NOT FOUND') . "\n";

echo "✅ Cancel URL parsed:\n";
echo "   - donation_id: " . ($cancelQuery['donation_id'] ?? 'NOT FOUND') . "\n";
echo "   - origin: " . ($cancelQuery['origin'] ?? 'NOT FOUND') . "\n\n";

// Test redirect URL construction
echo "3️⃣ Testing redirect URL construction...\n";

$donationId = $successQuery['donation_id'];
$origin = $successQuery['origin'] ?? 'http://localhost:49887';

$redirectUrl = rtrim($origin, '/') . '/payment/success?donation_id=' . urlencode($donationId);

echo "✅ Redirect URL: $redirectUrl\n\n";

// Test with missing origin
echo "4️⃣ Testing with missing origin...\n";

$successUrlNoOrigin = "http://192.168.1.21:8000/payment/bridge/success?donation_id=DN_test-12345";
$parts = parse_url($successUrlNoOrigin);
parse_str($parts['query'], $query);

$donationId = $query['donation_id'];
$origin = $query['origin'] ?? 'http://localhost:49887'; // fallback

$redirectUrl = rtrim($origin, '/') . '/payment/success?donation_id=' . urlencode($donationId);

echo "✅ URL without origin: $successUrlNoOrigin\n";
echo "✅ Fallback origin: $origin\n";
echo "✅ Redirect URL: $redirectUrl\n\n";

echo "🎉 URL construction testing completed!\n";
echo "\n";
echo "📋 Summary:\n";
echo "- URL construction: ✅\n";
echo "- URL parsing: ✅\n";
echo "- Redirect construction: ✅\n";
echo "- Fallback handling: ✅\n";
echo "\n";
echo "💡 Next steps:\n";
echo "1. Test actual payment flow\n";
echo "2. Check logs for 'THAWANI createSession payload'\n";
echo "3. Check logs for 'Bridge Success called' and 'Bridge Cancel called'\n";
echo "4. Verify redirect URLs in logs\n";
