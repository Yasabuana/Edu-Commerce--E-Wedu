<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';

    // 1. Bypass folder storage Vercel ke /tmp
    $app->useStoragePath('/tmp');
    $dirs = [
        '/tmp/app', 
        '/tmp/framework/cache/data', 
        '/tmp/framework/sessions', 
        '/tmp/framework/views', 
        '/tmp/logs'
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }

    // 2. Eksekusi Laravel 11
    $app->handleRequest(Request::capture());

} catch (Throwable $e) {
    echo "<div style='background:#fee; color:#c00; padding:20px; font-family:sans-serif; border-radius:8px;'>";
    echo "<strong>Vercel Error:</strong> " . $e->getMessage() . "<br><br>";
    echo "File: " . $e->getFile() . " (Baris " . $e->getLine() . ")";
    echo "</div>";
}