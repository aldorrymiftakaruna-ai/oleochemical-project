<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Login dulu
$loginRequest = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'admin@oleochemical.test',
    'password' => 'password',
]);
$loginResponse = $kernel->handle($loginRequest);
$cookies = $loginResponse->headers->getCookies();

echo "Login status: " . $loginResponse->getStatusCode() . "\n";
echo "Cookies: " . count($cookies) . "\n";

// Akses halaman equipment
$request2 = Illuminate\Http\Request::create('/cm/equipment/6611P01', 'GET');
foreach ($cookies as $cookie) {
    $request2->cookies->set($cookie->getName(), $cookie->getValue());
}
try {
    $response2 = $kernel->handle($request2);
    echo "Status: " . $response2->getStatusCode() . "\n";
    $content = $response2->getContent();
    if ($response2->getStatusCode() === 500) {
        echo "ERROR 500 - content:\n" . $content . "\n";
    } else {
        echo "OK - content length: " . strlen($content) . "\n";
        echo "First 500 chars:\n" . substr($content, 0, 500) . "\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
