<?php

// اختبار API برامج الدعم
$url = 'http://localhost:8000/api/v1/programs/support';

echo "=== اختبار API برامج الدعم ===\n";
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";
echo $response . "\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['data']) && is_array($data['data'])) {
        echo "\nعدد البرامج: " . count($data['data']) . "\n";
        foreach ($data['data'] as $program) {
            echo "- {$program['title']} (ID: {$program['id']})\n";
        }
    }
} else {
    echo "\n❌ فشل في جلب البيانات\n";
}

echo "\n=== انتهى الاختبار ===\n";
