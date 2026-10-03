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

    if ($action === 'create') {
        $nama = trim($_POST['nama'] ?? '');
        if ($nama === '') {
            $message = 'Nama kategori tidak boleh kosong.';
            $messageType = 'error';
        } else {
            try {
                $stmt = $db->prepare('INSERT INTO kategori (nama, slug) VALUES (?, ?)');
                $stmt->execute([$nama, adminSlugify($nama)]);
                $message = 'Kategori "' . $nama . '" berhasil ditambahkan.';
            } catch (Exception $e) {
                $message = 'Gagal menambahkan kategori: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $db->prepare('DELETE FROM kategori WHERE id = ?');
                $stmt->execute([$id]);
                $message = $stmt->rowCount() > 0 ? 'Kategori berhasil dihapus.' : 'Kategori tidak ditemukan.';
                if ($stmt->rowCount() === 0) {
                    $messageType = 'info';
                }
            } catch (Exception $e) {
                $message = 'Gagal menghapus kategori: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$kategoris = $db->query('SELECT k.*, (SELECT COUNT(*) FROM portfolio p WHERE p.kategori_id = k.id) as jumlah_portfolio FROM kategori k ORDER BY k.id DESC')->fetchAll();

adminLayoutHeader(
    'Kategori',
    'kategori',
    count($kategoris) . ' kategori terdaftar',
    [['label' => 'Lihat Website', 'url' => '/', 'icon' => 'fa-external-link', 'class' => 'admin-btn--ghost']]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Tambah Kategori</h3>
    </div>
    <div class="panel__body">
        <form method="POST" class="form-grid" style="align-items:end;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">
            <div class="field" style="margin-bottom:0;">
                <label for="nama">Nama Kategori</label>
                <input type="text" id="nama" name="nama" placeholder="Contoh: Branding" required>
                <div class="field__hint">Slug dibuat otomatis dari nama kategori.</div>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="admin-btn"><i class="fa fa-plus"></i> Tambah</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3>Daftar Kategori</h3>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($kategoris)): ?>
            <div class="empty-state">
                <i class="fa fa-folder-open-o"></i>
                <p>Belum ada kategori. Tambahkan kategori pertama di atas.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">ID</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Portfolio</th>
                            <th style="width:190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategoris as $k): ?>
                            <tr>
                                <td class="is-num"><?= (int)$k['id'] ?></td>
                                <td><strong><?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><span class="badge badge--muted"><code><?= htmlspecialchars($k['slug'], ENT_QUOTES, 'UTF-8') ?></code></span></td>
                                <td>
                                    <?php if ((int)$k['jumlah_portfolio'] > 0): ?>
                                        <span class="badge"><?= (int)$k['jumlah_portfolio'] ?> proyek</span>
                                    <?php else: ?>
                                        <span class="badge badge--muted">kosong</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/kategori/edit.php?id=<?= (int)$k['id'] ?>">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <form class="table-form" method="POST" onsubmit="return confirm('Hapus kategori &quot;<?= htmlspecialchars(addslashes($k['nama']), ENT_QUOTES, 'UTF-8') ?>&quot;? Portfolio di dalamnya tidak ikut terhapus.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
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