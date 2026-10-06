<?php
/**
 * CLI: bersihkan tag HTML pada portfolio.deskripsi.
 *
 * Field deskripsi diisi oleh CKEditor sehingga tersimpan sebagai <p>...</p>,
 * padahal kartu portfolio dan modal detail merendernya sebagai teks biasa.
 * Jalankan sekali di server produksi:  php backend/scripts/clean-portfolio-deskripsi.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya dapat dijalankan melalui command line.');
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/text.php';

try {
    $pdo = getConnection();
    $rows = $pdo->query('SELECT id, judul, deskripsi FROM portfolio ORDER BY id ASC')->fetchAll();
} catch (Throwable $e) {
    fwrite(STDERR, "[GAGAL] Koneksi database: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$changed = 0;

foreach ($rows as $row) {
    $clean = plainText($row['deskripsi']);

    if ($clean === (string)$row['deskripsi']) {
        continue;
    }

    try {
        $stmt = $pdo->prepare('UPDATE portfolio SET deskripsi = ? WHERE id = ?');
        $stmt->execute([$clean, $row['id']]);
        $changed++;
        echo "[OK] #" . $row['id'] . " " . $row['judul'] . PHP_EOL;
        echo "     dari: " . mb_substr((string)$row['deskripsi'], 0, 70) . PHP_EOL;
        echo "     ke  : " . mb_substr($clean, 0, 70) . PHP_EOL;
    } catch (Throwable $e) {
        fwrite(STDERR, "[GAGAL] #" . $row['id'] . " " . $row['judul'] . ": " . $e->getMessage() . PHP_EOL);
    }
}

echo "Selesai. " . $changed . " dari " . count($rows) . " deskripsi dibersihkan." . PHP_EOL;
