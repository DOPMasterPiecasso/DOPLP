<?php
/**
 * Router untuk halaman /register/* pada server pengembangan
 * `php -S localhost:8000` (tanpa script router): server selalu menjalankan
 * register/index.php untuk URL yang tidak menunjuk file langsung, jadi
 * /register/affiliate harus diteruskan ke affiliate/register.php.
 * Di Apache, .htaccess yang me-rewrite dan file ini tidak dipakai.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/register', PHP_URL_PATH);
$uri = trim((string)$uri, '/');

$sub = '';
if (strpos($uri, 'register/') === 0) {
    $sub = substr($uri, strlen('register/'));
} elseif ($uri === 'register') {
    $sub = '';
}
$sub = trim($sub, '/');

if ($sub === 'affiliate') {
    require __DIR__ . '/../affiliate/register.php';
    exit();
}

http_response_code(404);
require __DIR__ . '/../404.php';
