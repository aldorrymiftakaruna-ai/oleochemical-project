<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Buat request ke halaman equipment langsung — bypass auth
$request = Illuminate\Http\Request::create('/cm/equipment/6611P01', 'GET');

// Override: kita panggil controller langsung
$controller = $app->make('App\Http\Controllers\CmController');
$response = $controller->equipmentShow($request, '6611P01');

echo "Status: OK\n";
echo "Content length: " . strlen($response->render()) . "\n";
echo "First 2000 chars:\n" . substr($response->render(), 0, 2000) . "\n";
