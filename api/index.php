<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    $vendor = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($vendor)) {
        throw new Exception("Folder vendor tidak ditemukan. Composer install gagal atau belum dijalankan oleh Vercel.");
    }
    require $vendor;

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Paksa Laravel menggunakan folder sementara RAM Vercel agar tidak crash
    $app->useStoragePath('/tmp');
    
    // Jalankan aplikasi (Format standar Laravel 11)
    $app->handleRequest(Illuminate\Http\Request::capture());
    
} catch (Throwable $e) {
    // Tampilkan error ke layar dengan kotak merah agar jelas
    echo "<div style='font-family: sans-serif; padding: 20px; background: #ffebee; color: #b71c1c; border-radius: 8px; margin: 20px;'>";
    echo "<h2>LARAVEL CRASHED</h2>";
    echo "<p><strong>Pesan Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Lokasi File:</strong> " . $e->getFile() . " (Baris " . $e->getLine() . ")</p>";
    echo "</div>";
}