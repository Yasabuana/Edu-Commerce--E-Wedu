<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$vendorPath = __DIR__ . '/../vendor/autoload.php';

// 1. Cek apakah composer install berjalan di Vercel
if (!file_exists($vendorPath)) {
    die("FATAL ERROR: Folder vendor tidak ditemukan. Vercel tidak meng-install dependencies aplikasi.");
}

require $vendorPath;
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 2. Bypass folder storage yang read-only ke /tmp
$app->useStoragePath('/tmp');

// 3. Jalankan aplikasi
$app->handleRequest(Illuminate\Http\Request::capture());