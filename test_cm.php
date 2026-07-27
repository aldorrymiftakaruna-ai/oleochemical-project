<?php
require __DIR__ . '/vendor/autoload.php';
echo class_exists('App\Http\Controllers\CmController', true) ? "OK\n" : "NOT FOUND\n";
