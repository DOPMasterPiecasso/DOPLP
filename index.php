<?php
// Simple router for clean URLs
error_log('Router called for: ' . ($_SERVER['REQUEST_URI'] ?? 'NONE'));

$request = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($request, PHP_URL_PATH);

// Remove leading/trailing slashes
$path = trim($path, '/');
error_log('Cleaned path: [' . $path . ']');

// Serve static files directly (CSS, JS, images, etc)
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|mp4|webm)$/i', $path)) {
    error_log('Static file, skipping');
    return false; // Let PHP built-in server handle it
}

// Friendly aliases for backend pages
$routes = [
    'login' => '/backend/admin/login.php',
    'logout' => '/backend/admin/logout.php',
    'admin' => '/backend/admin/dashboard.php',
    'dashboard' => '/backend/admin/dashboard.php',
    'sitemap.xml' => '/sitemap.php',
    'sitemap' => '/sitemap.php',
    'blog' => '/blog.php',
];

if (isset($routes[$path])) {
    error_log('Route match: ' . $path . ' -> ' . $routes[$path]);
    require __DIR__ . $routes[$path];
    exit;
}

// /blog/<slug> -> blog-detail.php
if (preg_match('#^blog/([A-Za-z0-9\-_]+)$#', $path, $matches)) {
    error_log('Route match: blog detail -> ' . $matches[1]);
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/blog-detail.php';
    exit;
}

// If empty, serve index.html
if (empty($path)) {
    error_log('Empty path, serving index.html');
    readfile(__DIR__ . '/index.html');
    exit;
}

// Try .html file
$htmlFile = __DIR__ . '/' . $path . '.html';
error_log('Trying: ' . $htmlFile);
if (file_exists($htmlFile)) {
    error_log('Found! Serving: ' . $htmlFile);
    readfile($htmlFile);
    exit;
}

// Try exact path: let the server handle it so .php files are executed
// (never readfile() here, or PHP source would be served as plain text)
if (is_file(__DIR__ . '/' . $path)) {
    error_log('Found exact path, deferring to server');
    return false;
}

// 404
error_log('404 Not Found');
http_response_code(404);
require __DIR__ . '/404.php';
