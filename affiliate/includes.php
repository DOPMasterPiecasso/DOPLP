<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/config/database.php';

const AFFILIATE_SESSION_KEY = 'affiliate_user';

function affiliateStatuses() {
    return [
        'approve' => 'Approve',
        'belum_approve' => 'Belum Approve',
    ];
}

function affiliateStatusLabel($value) {
    $value = (string)$value;
    $list = affiliateStatuses();
    return $list[$value] ?? $list['belum_approve'];
}

function affiliateIsApproved($value) {
    return $value === 'approve';
}

function affiliateCurrent() {
    authStartSession();
    if (empty($_SESSION[AFFILIATE_SESSION_KEY]) || !is_array($_SESSION[AFFILIATE_SESSION_KEY])) {
        return null;
    }
    $_SESSION['created_at'] = time();
    return $_SESSION[AFFILIATE_SESSION_KEY];
}

function affiliateEstablishSession(array $row) {
    $_SESSION[AFFILIATE_SESSION_KEY] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'pic_pemilik' => $row['pic_pemilik'],
        'nama_usaha' => $row['nama_usaha'],
        'status' => $row['status'],
    ];
    $_SESSION['auth_fingerprint'] = authFingerprint();
    $_SESSION['created_at'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function affiliateRequireAuth() {
    if (!affiliateCurrent()) {
        header('Location: /affiliate/login');
        exit();
    }
}

function affiliateLogin($username, $password) {
    $username = trim((string)$username);
    $password = (string)$password;

    if ($username === '' || $password === '') {
        return ['ok' => false, 'message' => 'Username dan password wajib diisi.'];
    }

    $throttle = authThrottleState();
    if ($throttle['locked']) {
        return [
            'ok' => false,
            'message' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($throttle['remaining'] / 60) . ' menit.',
        ];
    }

    $dummy = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30M1MlGPLdFvlKq9m';
    $row = null;

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare('SELECT * FROM affiliate WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $found = $stmt->fetch();
        if ($found) {
            $row = $found;
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Database tidak dapat diakses. Silakan coba lagi nanti.'];
    }

    $hash = $row['password'] ?? $dummy;
    $valid = password_verify($password, $hash);

    if (!$row || !$valid) {
        usleep(random_int(200000, 400000));
        authThrottleHit();
        return ['ok' => false, 'message' => 'Username atau password salah.'];
    }

    if (!affiliateIsApproved($row['status'])) {
        authThrottleClear();
        return [
            'ok' => false,
            'message' => 'Akun Bapak/Ibu masih BELUM APPROVE. Tunggu admin memverifikasi data usaha Bapak/Ibu, lalu coba masuk lagi.',
        ];
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        try {
            $rehash = $pdo->prepare('UPDATE affiliate SET password = :password WHERE id = :id');
            $rehash->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $row['id']]);
        } catch (Throwable $e) {
        }
    }

    session_regenerate_id(true);
    authThrottleClear();
    affiliateEstablishSession($row);

    return ['ok' => true, 'message' => '', 'user_id' => (int)$row['id']];
}

function affiliateLogout() {
    authStartSession();
    unset($_SESSION[AFFILIATE_SESSION_KEY]);
    authThrottleClear();
    session_regenerate_id(true);
}

function affiliatePhotoDir() {
    return __DIR__ . '/../uploads/affiliate/';
}

function affiliatePhotoUrl($filename) {
    return '/uploads/affiliate/' . rawurlencode((string)$filename);
}

function affiliateHandlePhotoUpload(&$error) {
    $error = '';
    if (!isset($_FILES['photo_usaha']) || $_FILES['photo_usaha']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['photo_usaha'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Foto usaha gagal diunggah. Silakan coba lagi.';
        return null;
    }

    $maxSize = 2 * 1024 * 1024;
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['size'] > $maxSize) {
        $error = 'Ukuran foto usaha maksimal 2MB.';
        return null;
    }
    if (!in_array($ext, $allowed, true)) {
        $error = 'Format foto usaha harus JPG, PNG, WEBP, atau GIF.';
        return null;
    }

    $dir = affiliatePhotoDir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        $error = 'Folder upload tidak dapat ditulis.';
        return null;
    }

    $filename = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        $error = 'Foto usaha gagal disimpan di server.';
        return null;
    }

    return $filename;
}

function affiliatePhotoUnlink($filename) {
    if (!$filename) {
        return;
    }
    $file = affiliatePhotoDir() . basename((string)$filename);
    if (is_file($file)) {
        @unlink($file);
    }
}
