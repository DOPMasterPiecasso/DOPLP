<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('SELECT gambar FROM portfolio WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $message = 'Portfolio tidak ditemukan.';
                $messageType = 'info';
            } else {
                try {
                    $del = $db->prepare('DELETE FROM portfolio WHERE id = ?');
                    $del->execute([$id]);
                    $message = 'Portfolio berhasil dihapus.';

                    if (!empty($row['gambar'])) {
                        $file = __DIR__ . '/../../../uploads/portfolio/' . $row['gambar'];
                        if (is_file($file)) {
                            @unlink($file);
                        }
                    }
                } catch (Exception $e) {
                    $message = 'Gagal menghapus portfolio: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}

$keyword = trim($_GET['q'] ?? '');
$filterKategori = (int)($_GET['kategori'] ?? 0);

$sql = 'SELECT p.*, k.nama as kategori_nama
        FROM portfolio p
        LEFT JOIN kategori k ON p.kategori_id = k.id
        WHERE 1 = 1';
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (p.judul LIKE ? OR p.client LIKE ?)';
    $params[] = '%' . $keyword . '%';
    $params[] = '%' . $keyword . '%';
}
if ($filterKategori > 0) {
    $sql .= ' AND p.kategori_id = ?';
    $params[] = $filterKategori;
}
$sql .= ' ORDER BY p.id DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$portfolios = $stmt->fetchAll();

$kategoris = $db->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();
$totalPortfolio = (int)$db->query('SELECT COUNT(*) FROM portfolio')->fetchColumn();

adminLayoutHeader(
    'Portfolio',
    'portfolio',
    $totalPortfolio . ' proyek terdaftar',
    [
        ['label' => 'Tambah Portfolio', 'url' => '/backend/admin/portfolio/create.php', 'icon' => 'fa-plus', 'class' => ''],
        ['label' => 'Lihat Website', 'url' => '/', 'icon' => 'fa-external-link', 'class' => 'admin-btn--ghost'],
    ]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Filter</h3>
    </div>
    <div class="panel__body">
        <form method="GET" class="form-grid" style="align-items:end;">
            <div class="field" style="margin-bottom:0;">
                <label for="q">Cari</label>
                <input type="text" id="q" name="q" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>" placeholder="Judul atau client">
            </div>
            <div class="field" style="margin-bottom:0;">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($kategoris as $k): ?>
                        <option value="<?= (int)$k['id'] ?>" <?= $filterKategori === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="admin-btn"><i class="fa fa-filter"></i> Terapkan</button>
                <?php if ($keyword !== '' || $filterKategori > 0): ?>
                    <a class="admin-btn admin-btn--ghost" href="/backend/admin/portfolio/index.php">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3><?= count($portfolios) ?> Portfolio</h3>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($portfolios)): ?>
            <div class="empty-state">
                <i class="fa fa-briefcase"></i>
                <p>Tidak ada portfolio yang cocok.</p>
                <a class="admin-btn" href="/backend/admin/portfolio/create.php"><i class="fa fa-plus"></i> Tambah Portfolio</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Gambar</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Client</th>
                            <th>Tahun</th>
                            <th style="width:190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolios as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['gambar']): ?>
                                        <img class="table-thumb" src="/uploads/portfolio/<?= htmlspecialchars($p['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <?php else: ?>
                                        <span class="badge badge--muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($p['judul'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td>
                                    <?php if (!empty($p['kategori_nama'])): ?>
                                        <span class="badge"><?= htmlspecialchars($p['kategori_nama'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge--muted">Tanpa kategori</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['client'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-num"><?= htmlspecialchars($p['tahun'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/portfolio/edit.php?id=<?= (int)$p['id'] ?>">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <form class="table-form" method="POST" onsubmit="return confirm('Hapus portfolio &quot;<?= htmlspecialchars(addslashes($p['judul']), ENT_QUOTES, 'UTF-8') ?>&quot;? Tindakan ini tidak bisa dibatalkan.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">
                                                <i class="fa fa-trash-o"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
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