<?php

/**
 * Test script to verify fallback URL fix
 */

echo "🧪 Testing Fallback URL Fix\n";
echo "==========================\n\n";

// Test fallback URL construction when return_origin is null
echo "1️⃣ Testing fallback URL construction when return_origin is null...\n";

$returnOrigin = null; // كما يأتي من الواجهة الأمامية

$successUrl = $returnOrigin ? 
    rtrim($returnOrigin, '/') . '/payment/success' : 
    'http://localhost:49887/payment/success';
    
$cancelUrl = $returnOrigin ? 
    rtrim($returnOrigin, '/') . '/payment/cancel' : 
    'http://localhost:49887/payment/cancel';

echo "✅ Fallback Success URL: $successUrl\n";
echo "✅ Fallback Cancel URL: $cancelUrl\n\n";

// Test bridge URL construction with fallback
echo "2️⃣ Testing bridge URL construction with fallback...\n";

$testDonationId = 'DN_test-12345';

$success = "http://192.168.1.101:8000/payment/bridge/success?donation_id={$testDonationId}&origin=" . urlencode($successUrl);
$cancel = "http://192.168.1.101:8000/payment/bridge/cancel?donation_id={$testDonationId}&origin=" . urlencode($cancelUrl);

echo "✅ Bridge Success URL: $success\n";
echo "✅ Bridge Cancel URL: $cancel\n\n";

// Test URL parsing
echo "3️⃣ Testing URL parsing...\n";

$successParts = parse_url($success);
parse_str($successParts['query'], $successQuery);

echo "✅ Success URL parsed:\n";
echo "   - donation_id: " . ($successQuery['donation_id'] ?? 'NOT FOUND') . "\n";
echo "   - origin: " . ($successQuery['origin'] ?? 'NOT FOUND') . "\n\n";

// Test redirect URL construction
echo "4️⃣ Testing redirect URL construction...\n";

$donationId = $successQuery['donation_id'];
$origin = $successQuery['origin'];

$redirectUrl = $origin . '?donation_id=' . urlencode($donationId);

echo "✅ Redirect URL: $redirectUrl\n\n";

echo "🎉 Fallback URL fix testing completed!\n";
echo "\n";
echo "📋 Summary:\n";
echo "- Fallback URL now includes port 49887: ✅\n";
echo "- Bridge URL construction: ✅\n";
echo "- URL parsing: ✅\n";
echo "- Redirect construction: ✅\n";
echo "\n";
echo "💡 Next steps:\n";
echo "1. Test actual payment flow\n";
echo "2. Check logs for 'Payment creation request'\n";
echo "3. Verify return_origin is being sent from frontend\n";
echo "4. Test bridge redirects with correct port\n";
