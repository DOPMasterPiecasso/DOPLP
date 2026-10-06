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

if (in_array('teknologi', $columns, true)) {
    echo "[SKIP] Kolom teknologi sudah ada.\n";
} else {
    $pdo->exec("ALTER TABLE portfolio ADD COLUMN teknologi VARCHAR(255) DEFAULT NULL AFTER deskripsi");
    echo "[OK] Kolom teknologi ditambahkan.\n";
}
