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

// Pastikan tabel ada (dengan kolom teknologi)
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS kategori (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS portfolio (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kategori_id INT,
        judul VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        deskripsi TEXT,
        teknologi VARCHAR(255) DEFAULT NULL,
        gambar VARCHAR(255),
        client VARCHAR(255),
        tahun YEAR,
        status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
        is_pin TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$cols = array_column($pdo->query('SHOW COLUMNS FROM portfolio')->fetchAll(PDO::FETCH_ASSOC), 'Field');
if (!in_array('teknologi', $cols, true)) {
    $pdo->exec("ALTER TABLE portfolio ADD COLUMN teknologi VARCHAR(255) DEFAULT NULL");
}
if (!in_array('status', $cols, true)) {
    $pdo->exec("ALTER TABLE portfolio ADD COLUMN status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif'");
}
if (!in_array('is_pin', $cols, true)) {
    $pdo->exec("ALTER TABLE portfolio ADD COLUMN is_pin TINYINT(1) NOT NULL DEFAULT 0");
}

$clear = in_array('--clear', $argv, true);
if ($clear) {
    $pdo->exec('DELETE FROM portfolio');
    fwrite(STDOUT, "[OK] Semua data portfolio dihapus.\n");
    exit(0);
}

$js = file_get_contents(dirname(__DIR__, 2) . '/js/portfolio-data.js');
if ($js === false) {
    fwrite(STDERR, "[GAGAL] js/portfolio-data.js tidak ditemukan.\n");
    exit(1);
}

if (!preg_match('/window\.PORTFOLIO_DATA\s*=\s*(\{.*\});?\s*$/s', trim($js), $jm)) {
    fwrite(STDERR, "[GAGAL] Format js/portfolio-data.js tidak dikenali.\n");
    exit(1);
}

$data = json_decode($jm[1], true);
if (!is_array($data) || !$data) {
    fwrite(STDERR, "[GAGAL] js/portfolio-data.js tidak berisi data yang valid.\n");
    exit(1);
}

$m = [];
foreach ($data as $slug => $item) {
    $m[] = [
        $slug,
        $item['kategori'] ?? '',
        '',
        '',
        $item['judul'] ?? '',
        $item['teknologi'] ?? '',
        $item['deskripsi'] ?? '',
        basename($item['gambar'] ?? ''),
    ];
}

$catStmt = $pdo->prepare('INSERT INTO kategori (nama, slug) VALUES (:nama, :slug) ON DUPLICATE KEY UPDATE nama = VALUES(nama)');
$catGet = $pdo->prepare('SELECT id FROM kategori WHERE slug = :slug LIMIT 1');
$portStmt = $pdo->prepare(
    'INSERT INTO portfolio (kategori_id, judul, slug, deskripsi, teknologi, gambar, status)
     VALUES (:kategori_id, :judul, :slug, :deskripsi, :teknologi, :gambar, "aktif")
     ON DUPLICATE KEY UPDATE kategori_id = VALUES(kategori_id), judul = VALUES(judul),
     deskripsi = VALUES(deskripsi), teknologi = VALUES(teknologi), gambar = VALUES(gambar)'
);

$count = 0;
foreach ($m as $x) {
    $catNama = html_entity_decode($x[1]);
    $catSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $catNama), '-'));
    $catStmt->execute(['nama' => $catNama, 'slug' => $catSlug]);
    $catGet->execute(['slug' => $catSlug]);
    $catId = $catGet->fetchColumn();

    $portStmt->execute([
        'kategori_id' => $catId,
        'judul' => html_entity_decode(trim($x[4])),
        'slug' => $x[0],
        'deskripsi' => html_entity_decode(trim($x[6])),
        'teknologi' => html_entity_decode(trim($x[5])),
        'gambar' => 'asset/projek/' . $x[7],
    ]);
    $count++;
}

$featured = ['erp-construction', 'barbershop-app', 'cielo-cura', 'dandanoma', 'inviguard', 'jam-sembilan'];
$pdo->exec("UPDATE portfolio SET is_pin = 0");
$in = implode(',', array_fill(0, count($featured), '?'));
$pinStmt = $pdo->prepare("UPDATE portfolio SET is_pin = 1 WHERE slug IN ($in) AND status = 'aktif'");
$pinStmt->execute($featured);
echo "[OK] $count portofolio di-seed dari js/portfolio-data.js\n";
echo "[OK] 6 portofolio di-pin ke index\n";
