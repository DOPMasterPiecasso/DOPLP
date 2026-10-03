<?php
function adminNavItems() {
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer', 'url' => '/backend/admin/dashboard.php'],
        ['key' => 'portfolio', 'label' => 'Portfolio', 'icon' => 'fa-briefcase', 'url' => '/backend/admin/portfolio/index.php'],
        ['key' => 'kategori', 'label' => 'Kategori', 'icon' => 'fa-folder-open-o', 'url' => '/backend/admin/kategori/index.php'],
        ['key' => 'blog', 'label' => 'Blog', 'icon' => 'fa-newspaper-o', 'url' => '/backend/admin/blog/index.php'],
    ];
}

function adminNavCounts() {
    try {
        $pdo = getConnection();
        return [
            'portfolio' => (int)$pdo->query('SELECT COUNT(*) FROM portfolio')->fetchColumn(),
            'kategori' => (int)$pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn(),
            'blog' => (int)$pdo->query('SELECT COUNT(*) FROM blog')->fetchColumn(),
        ];
    } catch (Throwable $e) {
        return [];
    }
}

function adminInitial($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return 'A';
    }
    $parts = preg_split('/\s+/', $name);
    if (count($parts) > 1) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
    }
    return strtoupper(mb_substr($name, 0, 1));
}

function adminLayoutHeader($title, $active = '', $subtitle = '', array $actions = []) {
    $user = currentUser();
    $counts = adminNavCounts();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#75dab4">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | Admin dopagency</title>
    <link href="/ico/favicon.png" rel="shortcut icon">
    <link rel="stylesheet" href="/css/fontawesome.min.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-backdrop" id="adminBackdrop"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__brand">
        <div class="admin-sidebar__logo">da</div>
        <div class="admin-sidebar__name">
            dop<span>agency</span>
            <small>Admin Panel</small>
        </div>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav__label">Menu</div>
        <?php foreach (adminNavItems() as $item): ?>
            <?php $count = $counts[$item['key']] ?? null; ?>
            <a class="admin-nav__item <?= $active === $item['key'] ? 'is-active' : '' ?>" href="<?= $item['url'] ?>">
                <i class="fa <?= $item['icon'] ?>"></i>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($count !== null): ?>
                    <em class="admin-nav__count"><?= $count ?></em>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="admin-nav__label">Website</div>
        <a class="admin-nav__item" href="/" target="_blank" rel="noopener">
            <i class="fa fa-external-link"></i>
            <span>Lihat Website</span>
        </a>
    </nav>

    <div class="admin-sidebar__foot">
        <div class="admin-user">
            <div class="admin-user__avatar"><?= htmlspecialchars(adminInitial($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-user__meta">
                <b><?= htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></b>
                <small><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
            </div>
        </div>
        <a class="admin-logout" href="/backend/admin/logout.php">
            <i class="fa fa-sign-out"></i> Logout
        </a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <button type="button" class="admin-burger" id="adminBurger" aria-label="Buka menu">
            <i class="fa fa-bars"></i>
        </button>
        <div class="admin-topbar__title">
            <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
            <?php if ($subtitle !== ''): ?>
                <small><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></small>
            <?php endif; ?>
        </div>
        <?php if (!empty($actions)): ?>
            <div class="admin-topbar__actions">
                <?php foreach ($actions as $action): ?>
                    <a class="admin-btn <?= htmlspecialchars($action['class'] ?? '', ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($action['url'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($action['icon'])): ?><i class="fa <?= htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8') ?>"></i><?php endif; ?>
                        <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </header>

    <main class="admin-content">
    <?php
}

function adminLayoutFooter() {
    ?>
    </main>
    <footer class="admin-foot">
        &copy; <?= date('Y') ?> dopagency Admin Panel
    </footer>
</div>

<script>
    (function () {
        var sidebar = document.getElementById('adminSidebar');
        var backdrop = document.getElementById('adminBackdrop');
        var burger = document.getElementById('adminBurger');

        function close() {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
        }

        burger.addEventListener('click', function () {
            sidebar.classList.toggle('is-open');
            backdrop.classList.toggle('is-visible');
        });

        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });

        document.querySelectorAll('.admin-sidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) close();
            });
        });
    })();
</script>
</body>
</html>
    <?php
}

function adminAlert($message, $type = 'success') {
    if ($message === '' || $message === null) {
        return;
    }
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'info' => 'fa-info-circle',
    ];
    $icon = $icons[$type] ?? $icons['info'];
    $class = in_array($type, ['success', 'error', 'info'], true) ? $type : 'info';
    echo '<div class="alert alert--' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" role="alert">'
        . '<i class="fa ' . $icon . '"></i>'
        . '<span>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>'
        . '</div>';
}

function adminSlugify($text) {
    $text = strtolower(trim((string)$text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function adminRequireCsrf() {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo '<div class="alert alert--error" role="alert">'
            . '<i class="fa fa-exclamation-circle"></i>'
            . '<span>Sesi keamanan kedaluwarsa. Muat ulang halaman lalu coba lagi.</span>'
            . '</div>';
        exit;
    }
}