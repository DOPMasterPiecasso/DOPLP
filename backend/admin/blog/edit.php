<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$id = intval($_GET['id'] ?? 0);
$message = '';

if ($id === 0) {
    header('Location: /backend/admin/blog/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '-', $judul));
    $konten = $_POST['konten'] ?? '';
    $penulis = trim($_POST['penulis'] ?? 'Admin');
    $gambar = $_POST['existing_gambar'] ?? '';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $uploadDir = __DIR__ . '/../../../uploads/blog/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['gambar']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
            $gambar = $filename;
        }
    }
    
    if (!empty($judul) && !empty($konten)) {
        try {
            $stmt = $db->prepare("UPDATE blog SET judul=?, slug=?, konten=?, gambar=?, penulis=? WHERE id=?");
            $stmt->execute([$judul, $slug, $konten, $gambar, $penulis, $id]);
            $message = 'Blog berhasil diperbarui';
        } catch (Exception $e) {
            $message = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}

$stmt = $db->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: /backend/admin/blog/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/backend/admin/dashboard.php">Admin</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/backend/admin/portfolio/index.php">Portfolio</a>
                <a class="nav-link" href="/backend/admin/kategori/index.php">Kategori</a>
                <a class="nav-link active" href="/backend/admin/blog/index.php">Blog</a>
                <a class="nav-link" href="/backend/admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Edit Blog</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="mt-3">
            <input type="hidden" name="existing_gambar" value="<?= htmlspecialchars($blog['gambar']) ?>">
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($blog['judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Penulis</label>
                <input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($blog['penulis']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <?php if ($blog['gambar']): ?>
                    <div class="mb-2">
                        <img src="/uploads/blog/<?= htmlspecialchars($blog['gambar']) ?>" width="120" alt="">
                    </div>
                <?php endif; ?>
                <input type="file" name="gambar" class="form-control" accept="image/*">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Konten</label>
                <textarea name="konten" id="editor" class="form-control" rows="10"><?= htmlspecialchars($blog['konten']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/backend/admin/blog/index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>
