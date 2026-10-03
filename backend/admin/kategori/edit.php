<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$id = intval($_GET['id'] ?? 0);
$message = '';

if ($id === 0) {
    header('Location: /backend/admin/kategori/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '-', $nama));
    if (!empty($nama)) {
        try {
            $stmt = $db->prepare("UPDATE kategori SET nama=?, slug=? WHERE id=?");
            $stmt->execute([$nama, $slug, $id]);
            $message = 'Kategori berhasil diperbarui';
        } catch (Exception $e) {
            $message = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}

$stmt = $db->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->execute([$id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    header('Location: /backend/admin/kategori/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/backend/admin/dashboard.php">Admin</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/backend/admin/portfolio/index.php">Portfolio</a>
                <a class="nav-link active" href="/backend/admin/kategori/index.php">Kategori</a>
                <a class="nav-link" href="/backend/admin/blog/index.php">Blog</a>
                <a class="nav-link" href="/backend/admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Edit Kategori</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($kategori['nama']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/backend/admin/kategori/index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
