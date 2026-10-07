<?php
/**
 * Router untuk halaman mitra.
 *
 * Dibutuhkan karena server pengembangan `php -S localhost:8000` (tanpa script
 * router) selalu menjalankan affiliate/index.php untuk semua URL di bawah
 * /affiliate. Di Apache, .htaccess yang menangani rewrite dan file ini tidak
 * dipakai.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/affiliate', PHP_URL_PATH);
$uri = trim((string)$uri, '/');

$sub = '';
if (strpos($uri, 'affiliate/') === 0) {
    $sub = substr($uri, strlen('affiliate/'));
} elseif ($uri === 'affiliate') {
    $sub = '';
}
$sub = trim($sub, '/');

if ($sub === '') {
    $sub = 'login';
}

if (!preg_match('/^[a-z]+$/', $sub)) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit();
}

$file = __DIR__ . '/' . $sub . '.php';
if (!is_file($file)) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit();
}

require $file;
