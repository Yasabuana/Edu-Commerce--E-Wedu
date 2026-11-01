<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 1. Arahkan storage ke memori sementara /tmp
$app->useStoragePath('/tmp');

// 2. Buat struktur subfolder yang diwajibkan Laravel
$storageDirs = [
    '/tmp/app',
    '/tmp/framework/cache/data',
    '/tmp/framework/sessions',
    '/tmp/framework/views',
    '/tmp/logs'
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 3. Eksekusi dan tampilkan halaman web
$app->handleRequest(Illuminate\Http\Request::capture());