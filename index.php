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

// Try exact path
if (file_exists(__DIR__ . '/' . $path)) {
    error_log('Found exact path');
    readfile(__DIR__ . '/' . $path);
    exit;
}

// 404
error_log('404 Not Found');
http_response_code(404);
echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 Not Found</h1></body></html>';
