<?php
require_once __DIR__ . '/../includes/auth.php';

if (isAuthenticated()) {
    header('Location: ' . takeIntendedUrl());
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi keamanan kedaluwarsa. Silakan coba lagi.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);

        $result = login($email, $password);

        if ($result['ok']) {
            if ($remember) {
                $_SESSION['remember_until'] = time() + 2592000;
            }
            header('Location: ' . takeIntendedUrl());
            exit();
        }

        $error = $result['message'];
    }
}

$locked = authThrottleState();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#75dab4">
    <meta name="robots" content="noindex, nofollow">
    <title>Login Admin | dopagency</title>
    <link href="/ico/favicon.png" rel="shortcut icon">
    <link rel="stylesheet" href="/css/fontawesome.min.css">
    <link rel="stylesheet" href="/css/admin-login.css">
</head>
<body class="login-body">
    <div class="login-shell">

        <aside class="login-aside">
            <a href="/" class="login-brand">dop<span>agency</span></a>

            <div class="login-pitch">
                <h1>Kelola konten website dalam satu tempat.</h1>
                <p>Portfolio, kategori, dan blog dopagency tersinkronisasi langsung dengan website tanpa perlu menyentuh kode.</p>
            </div>

            <div class="login-metrics">
                <div>
                    <b>23</b>
                    <small>Proyek</small>
                </div>
                <div>
                    <b>6</b>
                    <small>Pilar Layanan</small>
                </div>
                <div>
                    <b>100%</b>
                    <small>Responsif</small>
                </div>
            </div>
        </aside>

        <main class="login-main">
            <div class="login-card">
                <h2>Login Admin</h2>
                <p class="login-lead">Masuk untuk mengelola konten dopagency.</p>

                <?php if ($error !== ''): ?>
                    <div class="login-alert is-error" role="alert">
                        <i class="fa fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php elseif ($locked['locked']): ?>
                    <div class="login-alert is-info" role="status">
                        <i class="fa fa-clock-o"></i>
                        <span>Terlalu banyak percobaan gagal. Coba lagi dalam <?= ceil($locked['remaining'] / 60) ?> menit.</span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/backend/admin/login.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="login-field">
                        <label for="email">Email</label>
                        <div class="login-input-wrap">
                            <i class="fa fa-envelope-o"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="login-input"
                                placeholder="admin@dopagency.com"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="username"
                                required
                                autofocus>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <div class="login-input-wrap">
                            <i class="fa fa-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="login-input"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                                style="padding-right: 52px;">
                            <button type="button" class="login-toggle" data-toggle-password="#password" aria-label="Tampilkan password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-options">
                        <label class="login-check">
                            <input type="checkbox" name="remember" value="1">
                            <span>Ingat saya</span>
                        </label>
                        <a href="mailto:info@dopagency.com?subject=Lupa%20password%20admin">Lupa password?</a>
                    </div>

                    <button type="submit" class="login-submit" <?= $locked['locked'] ? 'disabled' : '' ?>>Masuk</button>
                </form>

                <p class="login-foot">&copy; <?= date('Y') ?> dopagency &middot; <a href="/">Kembali ke website</a></p>
            </div>
        </main>

    </div>

    <script>
        (function () {
            var toggles = document.querySelectorAll('[data-toggle-password]');
            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var field = document.querySelector(toggle.getAttribute('data-toggle-password'));
                    if (!field) return;
                    var icon = toggle.querySelector('i');
                    var isHidden = field.getAttribute('type') === 'password';
                    field.setAttribute('type', isHidden ? 'text' : 'password');
                    icon.className = isHidden ? 'fa fa-eye-slash' : 'fa fa-eye';
                    toggle.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                });
            });
        })();
    </script>
</body>
</html>