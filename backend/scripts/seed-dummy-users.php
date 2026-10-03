<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya dapat dijalankan melalui command line.');
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/database.php';

$dummyUsers = [
    ['Dummy Admin Utama', 'admin.dummy@dopagency.test', 'DummyAdmin123!', 'admin'],
    ['Dummy Editor', 'editor.dummy@dopagency.test', 'DummyEditor123!', 'admin'],
    ['Dummy Kontributor', 'kontributor.dummy@dopagency.test', 'DummyKontrib123!', 'admin'],
    ['Dummy Reviewer', 'reviewer.dummy@dopagency.test', 'DummyReview123!', 'admin'],
    ['Dummy User Biasa', 'user.dummy@dopagency.test', 'DummyUser123!', 'user'],
];

try {
    $pdo = getConnection();
} catch (Throwable $e) {
    fwrite(STDERR, "[GAGAL] Koneksi database gagal: " . $e->getMessage() . "\n");
    exit(1);
}

$columns = [
    'remember_selector' => 'VARCHAR(64) DEFAULT NULL',
    'remember_token' => 'VARCHAR(64) DEFAULT NULL',
    'remember_expires_at' => 'DATETIME DEFAULT NULL',
];

try {
    $existingNames = array_column($pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC), 'Field');
    foreach ($columns as $column => $definition) {
        if (!in_array($column, $existingNames, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
            fwrite(STDOUT, "[INFO] Kolom $column ditambahkan.\n");
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, "[GAGAL] Gagal menyiapkan tabel users: " . $e->getMessage() . "\n");
    exit(1);
}

if (in_array('--remove', $argv, true)) {
    $emails = array_column($dummyUsers, 1);
    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    $delete = $pdo->prepare("DELETE FROM users WHERE email IN ($placeholders)");
    $delete->execute($emails);
    fwrite(STDOUT, "[OK] " . $delete->rowCount() . " dummy user dihapus.\n");
    exit(0);
}

$upsert = $pdo->prepare(
    'INSERT INTO users (name, email, password, role)
     VALUES (:name, :email, :password, :role)
     ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        password = VALUES(password),
        role = VALUES(role),
        remember_selector = NULL,
        remember_token = NULL,
        remember_expires_at = NULL'
);

fwrite(STDOUT, str_repeat('=', 64) . "\n");
fwrite(STDOUT, " DUMMY USERS - LOGIN: /login\n");
fwrite(STDOUT, str_repeat('=', 64) . "\n");

foreach ($dummyUsers as [$name, $email, $password, $role]) {
    $upsert->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
    ]);

    $canLogin = $role === 'admin';
    fwrite(STDOUT, sprintf(
        "%-26s %-34s %-18s %s\n",
        $name,
        $email,
        $password,
        $canLogin ? 'bisa login' : 'DITOLAK (bukan admin)'
    ));
}

fwrite(STDOUT, str_repeat('-', 64) . "\n");
fwrite(STDOUT, "Hapus semua dummy user:\n");
fwrite(STDOUT, "  php backend/scripts/seed-dummy-users.php --remove\n");
fwrite(STDOUT, "Hapus sebelum dipublikasikan ke produksi.\n");