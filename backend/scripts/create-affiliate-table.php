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

$pdo->exec("CREATE TABLE IF NOT EXISTS affiliate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pic_pemilik VARCHAR(150) NOT NULL,
    nama_usaha VARCHAR(200) NOT NULL,
    no_telepon VARCHAR(30) NOT NULL,
    photo_usaha VARCHAR(255) DEFAULT NULL,
    kota VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('approve', 'belum_approve') NOT NULL DEFAULT 'belum_approve',
    no_rekening VARCHAR(40) DEFAULT NULL,
    atas_nama VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "[OK] Tabel affiliate siap digunakan.\n";
