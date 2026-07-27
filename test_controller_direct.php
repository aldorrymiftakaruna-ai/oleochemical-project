<?php
require 'vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Panggil controller langsung
$controller = $app->make('App\Http\Controllers\CmController');
$request = new Illuminate\Http\Request();
$request->merge(['range' => '30']);

try {
    $response = $controller->equipmentShow($request, '6611P01');
    echo "SUCCESS!\n";
    echo "Content length: " . strlen($response->render()) . "\n";
    echo substr($response->render(), 0, 500) . "\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
