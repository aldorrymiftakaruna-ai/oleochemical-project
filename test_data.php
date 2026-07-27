<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$eq = App\Models\CmEquipment::where('equipment_tag', '6611P01')->first();
echo 'Findings: ' . $eq->findings->count() . "\n";
echo 'Readings count: ' . $eq->readings()->count() . "\n";

$lastReading = $eq->readings()->orderBy('tanggal', 'desc')->first();
if ($lastReading) {
    $arr = $lastReading->toArray();
    echo "Last reading date: " . $arr['tanggal'] . "\n";
    echo "Kondisi: " . $arr['kondisi'] . "\n";
    echo "Vib values: ndev_motor=" . var_export($arr['ndev_motor'], true) 
        . " ndeh_motor=" . var_export($arr['ndeh_motor'], true)
        . " ndea_motor=" . var_export($arr['ndea_motor'], true)
        . " dev_motor=" . var_export($arr['dev_motor'], true)
        . " deh_motor=" . var_export($arr['deh_motor'], true)
        . " dea_motor=" . var_export($arr['dea_motor'], true) . "\n";
    echo "ndev_pompa=" . var_export($arr['ndev_pompa'], true)
        . " ndeh_pompa=" . var_export($arr['ndeh_pompa'], true)
        . " ndea_pompa=" . var_export($arr['ndea_pompa'], true) . "\n";
    echo "ndev_screw=" . var_export($arr['ndev_screw'], true) . "\n";
        
    // Test max calculation
    $vibValues = array_filter([
        $arr['ndev_motor'], $arr['ndeh_motor'], $arr['ndea_motor'],
        $arr['dev_motor'],  $arr['deh_motor'],  $arr['dea_motor'],
        $arr['ndev_pompa'], $arr['ndeh_pompa'], $arr['ndea_pompa'],
        $arr['dev_pompa'],  $arr['deh_pompa'],  $arr['dea_pompa'],
        $arr['ndev_screw'], $arr['ndeh_screw'], $arr['ndea_screw'],
        $arr['dev_screw'],  $arr['deh_screw'],  $arr['dea_screw'],
    ], fn($v) => $v !== null);
    echo "\nFiltered vibValues count: " . count($vibValues) . "\n";
    if (count($vibValues) > 0) {
        echo "Max vib: " . max($vibValues) . "\n";
    } else {
        echo "WARNING: Semua vibValues null!\n";
    }
    
    $tempValues = array_filter([
        $arr['temp_de_motor'], $arr['temp_nde_motor'],
        $arr['temp_de_pompa'], $arr['temp_nde_pompa'],
        $arr['temp_de_screw'], $arr['temp_nde_screw'],
    ], fn($t) => $t !== null);
    echo "\nFiltered tempValues count: " . count($tempValues) . "\n";
    if (count($tempValues) > 0) {
        echo "Max temp: " . max($tempValues) . "\n";
    } else {
        echo "WARNING: Semua tempValues null!\n";
    }
} else {
    echo "TIDAK ADA LAST READING\n";
}
