<?php
require 'vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

$request = Illuminate\Http\Request::create('/cm/equipment/6611P01', 'GET');
try {
    $response = $kernel->handle($request);
    echo 'Status: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 500) {
        echo 'ERROR 500' . "\n";
        echo substr($response->getContent(), 0, 2000) . "\n";
    } else {
        echo 'OK - length: ' . strlen($response->getContent()) . "\n";
        echo substr($response->getContent(), 0, 1000) . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
}
