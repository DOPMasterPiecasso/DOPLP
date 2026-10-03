<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /backend/admin/kategori/index.php');
    exit();
}

$stmt = $db->prepare('SELECT * FROM kategori WHERE id = ?');
$stmt->execute([$id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    header('Location: /backend/admin/kategori/index.php');
    exit();
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();

    $nama = trim($_POST['nama'] ?? '');
    if ($nama === '') {
        $message = 'Nama kategori tidak boleh kosong.';
        $messageType = 'error';
    } else {
        try {
            $update = $db->prepare('UPDATE kategori SET nama = ?, slug = ? WHERE id = ?');
            $update->execute([$nama, adminSlugify($nama), $id]);
            $message = 'Kategori berhasil diperbarui.';

            $stmt = $db->prepare('SELECT * FROM kategori WHERE id = ?');
            $stmt->execute([$id]);
            $kategori = $stmt->fetch();
        } catch (Exception $e) {
            $message = 'Gagal memperbarui kategori: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$stmt = $db->prepare('SELECT COUNT(*) FROM portfolio WHERE kategori_id = ?');
$stmt->execute([$id]);
$portfolioCount = (int)$stmt->fetchColumn();

adminLayoutHeader(
    'Edit Kategori',
    'kategori',
    'ID ' . $id,
    [['label' => 'Kembali', 'url' => '/backend/admin/kategori/index.php', 'icon' => 'fa-arrow-left', 'class' => 'admin-btn--ghost']]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Ubah Kategori</h3>
    </div>
    <div class="panel__body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="field">
                <label for="nama">Nama Kategori</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($kategori['nama'], ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                <div class="field__hint">Slug saat ini: <code><?= htmlspecialchars($kategori['slug'], ENT_QUOTES, 'UTF-8') ?></code> &mdash; akan dibuat ulang otomatis.</div>
            </div>

            <?php if ($portfolioCount > 0): ?>
                <div class="alert alert--info">
                    <i class="fa fa-info-circle"></i>
                    <span>Kategori ini dipakai oleh <?= $portfolioCount ?> portfolio. Slug hanya berubah jika nama kategori diubah.</span>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="admin-btn"><i class="fa fa-check"></i> Simpan Perubahan</button>
                <a class="admin-btn admin-btn--ghost" href="/backend/admin/kategori/index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php adminLayoutFooter(); ?>