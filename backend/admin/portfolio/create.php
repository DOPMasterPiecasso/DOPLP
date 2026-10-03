<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$message = '';
$messageType = 'success';
$uploadDir = __DIR__ . '/../../../uploads/portfolio/';
$maxSize = 2 * 1024 * 1024;
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

function adminHandleUpload($file, $uploadDir, $maxSize, $allowed) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [null, null];
    }

    if ($file['size'] > $maxSize) {
        return [null, 'Ukuran gambar maksimal 2MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return [null, 'Format gambar harus JPG, PNG, WEBP, atau GIF.'];
    }

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $filename = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return [$filename, null];
    }

    return [null, 'Gagal menyimpan gambar.'];
}

$kategoris = $db->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();

    $judul = trim($_POST['judul'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
    $deskripsi = $_POST['deskripsi'] ?? '';
    $client = trim($_POST['client'] ?? '');
    $tahun = trim($_POST['tahun'] ?? '');
    $gambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        [$gambar, $uploadError] = adminHandleUpload($_FILES['gambar'], $uploadDir, $maxSize, $allowed);
        if ($uploadError !== null) {
            $message = $uploadError;
            $messageType = 'error';
        }
    }

    if ($judul === '') {
        $message = 'Judul tidak boleh kosong.';
        $messageType = 'error';
    } elseif ($messageType === 'error') {
    } else {
        try {
            $stmt = $db->prepare('INSERT INTO portfolio (judul, slug, kategori_id, deskripsi, gambar, client, tahun) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$judul, adminSlugify($judul), $kategori_id, $deskripsi, $gambar, $client, $tahun]);
            header('Location: /backend/admin/portfolio/index.php?success=1');
            exit();
        } catch (Exception $e) {
            $message = 'Gagal menambahkan portfolio: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if (isset($_GET['success'])) {
    adminAlert('Portfolio berhasil ditambahkan.', 'success');
}

adminLayoutHeader(
    'Tambah Portfolio',
    'portfolio',
    'Formulir proyek baru',
    [['label' => 'Kembali', 'url' => '/backend/admin/portfolio/index.php', 'icon' => 'fa-arrow-left', 'class' => 'admin-btn--ghost']]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Detail Portfolio</h3>
    </div>
    <div class="panel__body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="judul">Judul</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Redesign Website Kopi" required>
                </div>

                <div class="field">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id">
                        <option value="">-- Tanpa kategori --</option>
                        <?php foreach ($kategoris as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= (int)($_POST['kategori_id'] ?? 0) === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="client">Client</label>
                    <input type="text" id="client" name="client" value="<?= htmlspecialchars($_POST['client'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Kopi Nusantara">
                </div>

                <div class="field">
                    <label for="tahun">Tahun</label>
                    <input type="text" id="tahun" name="tahun" value="<?= htmlspecialchars($_POST['tahun'] ?? date('Y'), ENT_QUOTES, 'UTF-8') ?>" placeholder="2026">
                </div>

                <div class="field field--full">
                    <label for="gambar">Gambar</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <div class="field__hint">JPG, PNG, WEBP, atau GIF. Maksimal 2MB. Disarankan rasio 4:3.</div>
                </div>

                <div class="field field--full">
                    <label for="editor">Deskripsi</label>
                    <textarea id="editor" name="deskripsi" rows="10"><?= htmlspecialchars($_POST['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn"><i class="fa fa-check"></i> Simpan Portfolio</button>
                <a class="admin-btn admin-btn--ghost" href="/backend/admin/portfolio/index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    if (window.ClassicEditor && document.querySelector('#editor')) {
        ClassicEditor.create(document.querySelector('#editor')).catch(function (error) {
            console.error(error);
        });
    }
</script>

<?php adminLayoutFooter(); ?>