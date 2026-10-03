<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../config/database.php';

const AUTH_MAX_ATTEMPTS = 5;
const AUTH_LOCKOUT_SECONDS = 300;
const AUTH_SESSION_KEY = 'admin_user';
const AUTH_REMEMBER_COOKIE = 'dopremember';
const AUTH_REMEMBER_DAYS = 30;

function authIsSecureRequest() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function authStartSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = authIsSecureRequest();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('dopadmin');
    session_start();
}

function authFingerprint() {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return hash('sha256', $agent);
}

function authStart() {
    authStartSession();

    if (!isset($_SESSION['auth_fingerprint'])) {
        $_SESSION['auth_fingerprint'] = authFingerprint();
        $_SESSION['created_at'] = time();
    } elseif (!hash_equals($_SESSION['auth_fingerprint'], authFingerprint())) {
        authDestroy();
        authStartSession();
        $_SESSION['auth_fingerprint'] = authFingerprint();
        $_SESSION['created_at'] = time();
    }

    if (isset($_SESSION['created_at']) && (time() - $_SESSION['created_at']) > 7200) {
        authDestroy();
        authStartSession();
        $_SESSION['auth_fingerprint'] = authFingerprint();
        $_SESSION['created_at'] = time();
    }

    if (!isset($_SESSION[AUTH_SESSION_KEY])) {
        authLoginFromRememberCookie();
    }
}

function authDestroy() {
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE && ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function csrfToken() {
    authStartSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfVerify($token) {
    authStartSession();
    if (empty($_SESSION['csrf_token']) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function authThrottleState() {
    authStartSession();

    $attempts = (int)($_SESSION['login_attempts'] ?? 0);
    $lockedUntil = (int)($_SESSION['login_locked_until'] ?? 0);
    $remaining = 0;

    if ($lockedUntil > time()) {
        $remaining = $lockedUntil - time();
    } elseif ($lockedUntil > 0) {
        unset($_SESSION['login_locked_until'], $_SESSION['login_attempts']);
        $attempts = 0;
    }

    return [
        'locked' => $remaining > 0,
        'remaining' => $remaining,
        'attempts' => $attempts,
    ];
}

function authThrottleHit() {
    authStartSession();

    $attempts = (int)($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_attempts'] = $attempts;

    if ($attempts >= AUTH_MAX_ATTEMPTS) {
        $_SESSION['login_locked_until'] = time() + AUTH_LOCKOUT_SECONDS;
        $_SESSION['login_attempts'] = 0;
    }
}

function authThrottleClear() {
    authStartSession();
    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
}

function isAuthenticated() {
    authStartSession();
    return isset($_SESSION[AUTH_SESSION_KEY]) && is_array($_SESSION[AUTH_SESSION_KEY]);
}

function currentUser() {
    authStartSession();
    return $_SESSION[AUTH_SESSION_KEY] ?? null;
}

function currentUserRole() {
    $user = currentUser();
    return $user['role'] ?? null;
}

function login($email, $password) {
    $email = trim((string)$email);
    $password = (string)$password;

    if ($email === '' || $password === '') {
        return ['ok' => false, 'message' => 'Email dan password wajib diisi.'];
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
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $found = $stmt->fetch();

        if ($found) {
            $row = $found;
        }
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'message' => 'Database tidak dapat diakses. Silakan coba lagi nanti.',
        ];
    }

    $hash = $row['password'] ?? $dummy;
    $valid = password_verify($password, $hash);

    if (!$row || !$valid) {
        usleep(random_int(200000, 400000));
        authThrottleHit();
        return ['ok' => false, 'message' => 'Email atau password salah.'];
    }

    if (!in_array($row['role'], ['admin'], true)) {
        return ['ok' => false, 'message' => 'Akun ini tidak memiliki akses admin.'];
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        try {
            $pdo = getConnection();
            $rehash = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $rehash->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $row['id']]);
        } catch (Throwable $e) {
        }
    }

    session_regenerate_id(true);
    authThrottleClear();
    authEstablishSession($row);

    return ['ok' => true, 'message' => ''];
}

function authEstablishSession(array $row) {
    $_SESSION[AUTH_SESSION_KEY] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'role' => $row['role'],
    ];
    $_SESSION['auth_fingerprint'] = authFingerprint();
    $_SESSION['created_at'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function authIssueRememberCookie(int $userId) {
    try {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + (AUTH_REMEMBER_DAYS * 86400);

        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'UPDATE users SET remember_selector = :selector, remember_token = :token, remember_expires_at = :expires WHERE id = :id'
        );
        $stmt->execute([
            'selector' => $selector,
            'token' => hash('sha256', $validator),
            'expires' => date('Y-m-d H:i:s', $expiresAt),
            'id' => $userId,
        ]);

        setcookie(AUTH_REMEMBER_COOKIE, $selector . ':' . $validator, [
            'expires' => $expiresAt,
            'path' => '/',
            'secure' => authIsSecureRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } catch (Throwable $e) {
    }
}

function authClearRememberCookie() {
    if (isset($_COOKIE[AUTH_REMEMBER_COOKIE])) {
        setcookie(AUTH_REMEMBER_COOKIE, '', [
            'expires' => time() - 42000,
            'path' => '/',
            'secure' => authIsSecureRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[AUTH_REMEMBER_COOKIE]);
    }

    try {
        $pdo = getConnection();
        $pdo->exec('UPDATE users SET remember_selector = NULL, remember_token = NULL, remember_expires_at = NULL');
    } catch (Throwable $e) {
    }
}

function authLoginFromRememberCookie() {
    $raw = $_COOKIE[AUTH_REMEMBER_COOKIE] ?? '';
    if (!is_string($raw) || !str_contains($raw, ':')) {
        return;
    }

    [$selector, $validator] = explode(':', $raw, 2);
    if ($selector === '' || $validator === '') {
        return;
    }

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'SELECT id, name, email, role, remember_selector, remember_token, remember_expires_at FROM users WHERE remember_selector = :selector LIMIT 1'
        );
        $stmt->execute(['selector' => $selector]);
        $row = $stmt->fetch();

        if (!$row || empty($row['remember_token']) || empty($row['remember_expires_at'])) {
            return;
        }

        if (strtotime($row['remember_expires_at']) < time()) {
            authClearRememberCookie();
            return;
        }

        if (!hash_equals($row['remember_token'], hash('sha256', $validator))) {
            authClearRememberCookie();
            return;
        }

        if ($row['role'] !== 'admin') {
            authClearRememberCookie();
            return;
        }

        session_regenerate_id(true);
        authEstablishSession($row);
        authIssueRememberCookie((int)$row['id']);
    } catch (Throwable $e) {
    }
}

function requireAuth() {
    if (!isAuthenticated()) {
        $intended = $_SERVER['REQUEST_URI'] ?? '';
        if ($intended !== '' && strpos($intended, '/backend/admin/login.php') === false) {
            $_SESSION['intended_url'] = $intended;
        }
        authDestroy();
        header('Location: /backend/admin/login.php');
        exit();
    }
}

function takeIntendedUrl($fallback = '/backend/admin/dashboard.php') {
    authStartSession();
    $intended = $_SESSION['intended_url'] ?? '';
    unset($_SESSION['intended_url']);

    if ($intended === '' || !preg_match('#^/backend/admin/[A-Za-z0-9_\-/.]+$#', $intended)) {
        return $fallback;
    }
    if (strpos($intended, '//') !== false || strpos($intended, '..') !== false) {
        return $fallback;
    }
    return $intended;
}

function logout() {
    authStartSession();
    authClearRememberCookie();
    $_SESSION = [];
    authDestroy();
}

authStart();