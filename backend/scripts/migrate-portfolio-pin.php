<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya dapat dijalankan melalui command line.');
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getConnection();
} catch (Throwable $e) {
    fwrite(STDERR, "[GAGAL] Koneksi database gagal: " . $e->getMessage() . "\n");
    exit(1);
}

$columns = array_column($pdo->query('SHOW COLUMNS FROM portfolio')->fetchAll(PDO::FETCH_ASSOC), 'Field');

if (in_array('is_pin', $columns, true)) {
    echo "[SKIP] Kolom is_pin sudah ada.\n";
} else {
    $pdo->exec("ALTER TABLE portfolio ADD COLUMN is_pin TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
    echo "[OK] Kolom is_pin ditambahkan.\n";
}
