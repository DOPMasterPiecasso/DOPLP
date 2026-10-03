<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya dapat dijalankan melalui command line.');
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/database.php';

function fail($message) {
    fwrite(STDERR, "[GAGAL] $message\n");
    exit(1);
}

function promptSecret($label) {
    fwrite(STDOUT, "$label: ");
    if (DIRECTORY_SEPARATOR === '/' && @posix_isatty(STDIN)) {
        $hidden = @shell_exec('stty -g 2>/dev/null');
        if ($hidden) {
            @shell_exec('stty -echo 2>/dev/null');
        }
        $value = trim((string)fgets(STDIN));
        if ($hidden) {
            @shell_exec('stty ' . trim($hidden) . ' 2>/dev/null');
            fwrite(STDOUT, "\n");
        }
        return $value;
    }
    return trim((string)fgets(STDIN));
}

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$name = $argv[3] ?? 'Administrator';

if ($email === '') {
    $email = trim((string)fgets(STDIN));
}
if ($password === '') {
    $password = promptSecret('Password');
}

$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Format email tidak valid.');
}
if (strlen($password) < 8) {
    fail('Password minimal 8 karakter.');
}
if ($name === '') {
    $name = 'Administrator';
}

try {
    $pdo = getConnection();
} catch (Throwable $e) {
    fail('Koneksi database gagal: ' . $e->getMessage());
}

$columns = [
    'remember_selector' => 'VARCHAR(64) DEFAULT NULL',
    'remember_token' => 'VARCHAR(64) DEFAULT NULL',
    'remember_expires_at' => 'DATETIME DEFAULT NULL',
];

try {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM(\'admin\', \'user\') DEFAULT \'user\',
            remember_selector VARCHAR(64) DEFAULT NULL,
            remember_token VARCHAR(64) DEFAULT NULL,
            remember_expires_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $existing = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC);
    $existingNames = array_column($existing, 'Field');

    foreach ($columns as $column => $definition) {
        if (!in_array($column, $existingNames, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
            fwrite(STDOUT, "[INFO] Kolom $column ditambahkan.\n");
        }
    }
} catch (Throwable $e) {
    fail('Gagal menyiapkan tabel users: ' . $e->getMessage());
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $update = $pdo->prepare(
            'UPDATE users
             SET name = :name, password = :password, role = \'admin\',
                 remember_selector = NULL, remember_token = NULL, remember_expires_at = NULL
             WHERE id = :id'
        );
        $update->execute(['name' => $name, 'password' => $hash, 'id' => $existingUser['id']]);
        fwrite(STDOUT, "[OK] Admin '$email' diperbarui (password di-reset, semua remember-me dicabut).\n");
    } else {
        $insert = $pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, \'admin\')'
        );
        $insert->execute(['name' => $name, 'email' => $email, 'password' => $hash]);
        fwrite(STDOUT, "[OK] Admin '$email' dibuat.\n");
    }
} catch (Throwable $e) {
    fail('Gagal menyimpan admin: ' . $e->getMessage());
}

fwrite(STDOUT, "Login: /backend/admin/login.php\n");