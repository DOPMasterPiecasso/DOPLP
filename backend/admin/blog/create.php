<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$message = '';
$messageType = 'success';
$uploadDir = __DIR__ . '/../../../uploads/blog/';
$maxSize = 2 * 1024 * 1024;
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();

    $judul = trim($_POST['judul'] ?? '');
    $konten = $_POST['konten'] ?? '';
    $penulis = trim($_POST['penulis'] ?? ($user['name'] ?? 'Admin'));
    $gambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['gambar']['size'] > $maxSize) {
            $message = 'Ukuran gambar maksimal 2MB.';
            $messageType = 'error';
        } else {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $message = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
                $messageType = 'error';
            } else {
                $filename = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . $filename)) {
                    $gambar = $filename;
                }
            }
        }
    }

    if ($judul === '') {
        $message = 'Judul tidak boleh kosong.';
        $messageType = 'error';
    } elseif (trim($konten) === '') {
        $message = 'Konten artikel tidak boleh kosong.';
        $messageType = 'error';
    } elseif ($messageType === 'error') {
    } else {
        try {
            $stmt = $db->prepare('INSERT INTO blog (judul, slug, konten, gambar, penulis) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$judul, adminSlugify($judul), $konten, $gambar, $penulis]);
            header('Location: /backend/admin/blog/index.php?success=1');
            exit();
        } catch (Exception $e) {
            $message = 'Gagal menambahkan artikel: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if (isset($_GET['success'])) {
    adminAlert('Artikel berhasil ditambahkan.', 'success');
}

adminLayoutHeader(
    'Tambah Artikel',
    'blog',
    'Formulir artikel baru',
    [['label' => 'Kembali', 'url' => '/backend/admin/blog/index.php', 'icon' => 'fa-arrow-left', 'class' => 'admin-btn--ghost']]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Detail Artikel</h3>
    </div>
    <div class="panel__body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-grid">
                <div class="field field--full">
                    <label for="judul">Judul</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Tren Digital Marketing 2026" required>
                </div>

                <div class="field">
                    <label for="penulis">Penulis</label>
                    <input type="text" id="penulis" name="penulis" value="<?= htmlspecialchars($_POST['penulis'] ?? ($user['name'] ?? 'Admin'), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field">
                    <label for="gambar">Gambar</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <div class="field__hint">JPG, PNG, WEBP, atau GIF. Maksimal 2MB.</div>
                </div>

                <div class="field field--full">
                    <label for="konten">Konten</label>
                    <textarea id="konten" name="konten" rows="14"><?= htmlspecialchars($_POST['konten'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn"><i class="fa fa-check"></i> Simpan Artikel</button>
                <a class="admin-btn admin-btn--ghost" href="/backend/admin/blog/index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    if (window.ClassicEditor && document.querySelector('#konten')) {
        ClassicEditor.create(document.querySelector('#konten')).catch(function (error) {
            console.error(error);
        });
    }
</script>

<?php adminLayoutFooter(); ?>