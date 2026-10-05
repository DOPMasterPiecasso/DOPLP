<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$id = (int)($_GET['id'] ?? 0);
$uploadDir = __DIR__ . '/../../../uploads/portfolio/';
$maxSize = 2 * 1024 * 1024;
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

function adminHandleUploadEdit($file, $uploadDir, $maxSize, $allowed) {
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
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return [$filename, null];
    }
    return [null, 'Gagal menyimpan gambar.'];
}

if ($id === 0) {
    header('Location: /backend/admin/portfolio/index.php');
    exit();
}

$stmt = $db->prepare('SELECT * FROM portfolio WHERE id = ?');
$stmt->execute([$id]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    header('Location: /backend/admin/portfolio/index.php');
    exit();
}

$message = '';
$messageType = 'success';
$kategoris = $db->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();

    $judul = trim($_POST['judul'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0) ?: null;
    $deskripsi = $_POST['deskripsi'] ?? '';
    $client = trim($_POST['client'] ?? '');
    $tahun = trim($_POST['tahun'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['aktif', 'nonaktif'], true) ? $_POST['status'] : 'aktif';
    $isPin = isset($_POST['is_pin']) ? 1 : 0;
    $gambar = $portfolio['gambar'];

    $newUpload = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        [$newUpload, $uploadError] = adminHandleUploadEdit($_FILES['gambar'], $uploadDir, $maxSize, $allowed);
        if ($uploadError !== null) {
            $message = $uploadError;
            $messageType = 'error';
        } else {
            $gambar = $newUpload;
        }
    }

    if ($isPin === 1 && (int)$portfolio['is_pin'] !== 1) {
        $pinnedCount = (int)$db->query("SELECT COUNT(*) FROM portfolio WHERE is_pin = 1")->fetchColumn();
        if ($pinnedCount >= 6) {
            $message = 'Maksimal 6 portofolio yang di-pin.';
            $messageType = 'error';
        }
    }

    if ($judul === '') {
        $message = 'Judul tidak boleh kosong.';
        $messageType = 'error';
    } elseif ($messageType === 'error') {
    } else {
        try {
            $update = $db->prepare('UPDATE portfolio SET judul = ?, slug = ?, kategori_id = ?, deskripsi = ?, gambar = ?, client = ?, tahun = ?, status = ?, is_pin = ? WHERE id = ?');
            $update->execute([$judul, adminSlugify($judul), $kategori_id, $deskripsi, $gambar, $client, $tahun, $status, $isPin, $id]);

            if ($newUpload !== null && !empty($portfolio['gambar'])) {
                $old = $uploadDir . $portfolio['gambar'];
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $message = 'Portfolio berhasil diperbarui.';

            $stmt = $db->prepare('SELECT * FROM portfolio WHERE id = ?');
            $stmt->execute([$id]);
            $portfolio = $stmt->fetch();
        } catch (Exception $e) {
            $message = 'Gagal memperbarui portfolio: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

adminLayoutHeader(
    'Edit Portfolio',
    'portfolio',
    $portfolio['judul'],
    [['label' => 'Kembali', 'url' => '/backend/admin/portfolio/index.php', 'icon' => 'fa-arrow-left', 'class' => 'admin-btn--ghost']]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Ubah Portfolio</h3>
    </div>
    <div class="panel__body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="judul">Judul</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($portfolio['judul'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id">
                        <option value="">-- Tanpa kategori --</option>
                        <?php foreach ($kategoris as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= (int)$portfolio['kategori_id'] === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="client">Client</label>
                    <input type="text" id="client" name="client" value="<?= htmlspecialchars($portfolio['client'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="tahun">Tahun</label>
                    <input type="text" id="tahun" name="tahun" value="<?= htmlspecialchars($portfolio['tahun'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field field--full">
                    <label for="gambar">Gambar</label>
                    <?php if (!empty($portfolio['gambar'])): ?>
                        <img class="image-preview" src="<?= (strpos($portfolio['gambar'], '/') !== false ? '/' : '/uploads/portfolio/') . htmlspecialchars($portfolio['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <?php endif; ?>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <div class="field__hint">Biarkan kosong jika tidak ingin mengganti gambar. Maksimal 2MB.</div>
                </div>

                <div class="field">
                    <label for="is_pin">Pin ke Index</label>
                    <label style="display:flex;align-items:center;gap:8px;font-weight:400;">
                        <input type="checkbox" id="is_pin" name="is_pin" value="1" <?= (int)($portfolio['is_pin'] ?? 0) === 1 ? 'checked' : '' ?>> Tampilkan di halaman utama (maks. 6)
                    </label>
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="aktif" <?= ($portfolio['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif (tampil di index)</option>
                        <option value="nonaktif" <?= ($portfolio['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="field field--full">
                    <label for="editor">Deskripsi</label>
                    <textarea id="editor" name="deskripsi" rows="10"><?= htmlspecialchars($portfolio['deskripsi'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn"><i class="fa fa-check"></i> Simpan Perubahan</button>
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