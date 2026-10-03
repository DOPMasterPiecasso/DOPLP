<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$message = '';
$kategoris = $db->query("SELECT * FROM kategori ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '-', $judul));
    $kategori_id = intval($_POST['kategori_id'] ?? 0) ?: null;
    $deskripsi = $_POST['deskripsi'] ?? '';
    $client = trim($_POST['client'] ?? '');
    $tahun = trim($_POST['tahun'] ?? '');
    $gambar = '';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $uploadDir = __DIR__ . '/../../../uploads/portfolio/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['gambar']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
            $gambar = $filename;
        }
    }
    
    if (!empty($judul)) {
        try {
            $stmt = $db->prepare("INSERT INTO portfolio (judul, slug, kategori_id, deskripsi, gambar, client, tahun) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $slug, $kategori_id, $deskripsi, $gambar, $client, $tahun]);
            header('Location: /backend/admin/portfolio/index.php');
            exit();
        } catch (Exception $e) {
            $message = 'Gagal menambahkan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/backend/admin/dashboard.php">Admin</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="/backend/admin/portfolio/index.php">Portfolio</a>
                <a class="nav-link" href="/backend/admin/kategori/index.php">Kategori</a>
                <a class="nav-link" href="/backend/admin/blog/index.php">Blog</a>
                <a class="nav-link" href="/backend/admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Tambah Portfolio</h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="mt-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategoris as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Client</label>
                    <input type="text" name="client" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun</label>
                    <input type="text" name="tahun" class="form-control" placeholder="2025">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="editor" class="form-control" rows="8"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/backend/admin/portfolio/index.php" class="btn btn-secondary">Kembali</a>
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
