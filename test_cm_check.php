<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$eq = App\Models\CmEquipment::where('equipment_tag', '6611P01')->first();
if ($eq) {
    echo "DITEMUKAN:\n";
    print_r($eq->toArray());
    echo "\nAsset: ";
    print_r($eq->asset?->toArray() ?? 'NULL');
    echo "\nFindings count: " . $eq->findings->count() . "\n";
    echo "Readings count: " . $eq->readings()->count() . "\n";
} else {
    echo "TIDAK DITEMUKAN\n";
}
echo "\n--- Semua equipment_tag ---\n";
$all = App\Models\CmEquipment::pluck('equipment_tag');
print_r($all->toArray());
