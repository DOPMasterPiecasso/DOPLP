<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getConnection();
$portfolioCount = (int)$db->query('SELECT COUNT(*) FROM portfolio')->fetchColumn();
$kategoriCount = (int)$db->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$blogCount = (int)$db->query('SELECT COUNT(*) FROM blog')->fetchColumn();

$recentPortfolio = $db->query('
    SELECT p.id, p.judul, p.client, p.tahun, p.gambar, k.nama as kategori_nama
    FROM portfolio p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    ORDER BY p.id DESC
    LIMIT 5
')->fetchAll();

$recentBlog = $db->query('SELECT id, judul, penulis, created_at FROM blog ORDER BY id DESC LIMIT 5')->fetchAll();

$user = currentUser();

adminLayoutHeader(
    'Dashboard',
    'dashboard',
    'Ringkasan konten website dopagency',
    [
        ['label' => 'Tambah Portfolio', 'url' => '/backend/admin/portfolio/create.php', 'icon' => 'fa-plus', 'class' => ''],
        ['label' => 'Lihat Website', 'url' => '/', 'icon' => 'fa-external-link', 'class' => 'admin-btn--ghost'],
    ]
);
?>

<div class="stat-grid">
    <div class="stat">
        <div class="stat__icon"><i class="fa fa-briefcase"></i></div>
        <b><?= $portfolioCount ?></b>
        <small>Portfolio</small>
        <a href="/backend/admin/portfolio/index.php">Kelola &rarr;</a>
    </div>
    <div class="stat">
        <div class="stat__icon"><i class="fa fa-folder-open-o"></i></div>
        <b><?= $kategoriCount ?></b>
        <small>Kategori</small>
        <a href="/backend/admin/kategori/index.php">Kelola &rarr;</a>
    </div>
    <div class="stat">
        <div class="stat__icon"><i class="fa fa-newspaper-o"></i></div>
        <b><?= $blogCount ?></b>
        <small>Blog</small>
        <a href="/backend/admin/blog/index.php">Kelola &rarr;</a>
    </div>
    <div class="stat">
        <div class="stat__icon"><i class="fa fa-user"></i></div>
        <b><?= htmlspecialchars(adminInitial($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b>
        <small><?= htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></small>
        <small style="margin-top:2px;">Masuk sebagai <?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3>Portfolio Terbaru</h3>
        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/portfolio/index.php">Lihat semua</a>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($recentPortfolio)): ?>
            <div class="empty-state">
                <i class="fa fa-folder-open-o"></i>
                <p>Belum ada portfolio. Tambahkan proyek pertama Anda.</p>
                <a class="admin-btn" href="/backend/admin/portfolio/create.php"><i class="fa fa-plus"></i> Tambah Portfolio</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Client</th>
                            <th>Tahun</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPortfolio as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['gambar']): ?>
                                        <img class="table-thumb" src="/uploads/portfolio/<?= htmlspecialchars($p['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($p['judul'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($p['kategori_nama'])): ?>
                                        <span class="badge"><?= htmlspecialchars($p['kategori_nama'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge--muted">Tanpa kategori</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['client'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-num"><?= htmlspecialchars($p['tahun'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-actions">
                                    <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/portfolio/edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3>Artikel Terbaru</h3>
        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/blog/index.php">Lihat semua</a>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($recentBlog)): ?>
            <div class="empty-state">
                <i class="fa fa-newspaper-o"></i>
                <p>Belum ada artikel blog.</p>
                <a class="admin-btn" href="/backend/admin/blog/create.php"><i class="fa fa-plus"></i> Tambah Artikel</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Dibuat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBlog as $b): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['judul'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($b['penulis'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-num"><?= htmlspecialchars(date('d M Y', strtotime($b['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-actions">
                                    <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/blog/edit.php?id=<?= (int)$b['id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php adminLayoutFooter(); ?>