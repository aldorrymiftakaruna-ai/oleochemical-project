<?php
$ch = curl_init();

// 1. Login
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:9999/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => 'admin@oleochemical.test',
        'password' => 'password',
        '_token' => '',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => 'cookies.txt',
    CURLOPT_COOKIEFILE => 'cookies.txt',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Login HTTP status: $httpCode\n";

// 2. Akses halaman equipment
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:9999/cm/equipment/6611P01',
    CURLOPT_POST => false,
    CURLOPT_HTTPGET => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "\nEquipment page HTTP status: $httpCode\n";
echo "First 3000 chars:\n" . substr($response, 0, 3000) . "\n";

curl_close($ch);
@unlink('cookies.txt');
