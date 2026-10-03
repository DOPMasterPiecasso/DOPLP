<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$message = '';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $db->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Kategori berhasil dihapus';
    } catch (Exception $e) {
        $message = 'Gagal menghapus: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '-', $nama));
    if (!empty($nama)) {
        try {
            $stmt = $db->prepare("INSERT INTO kategori (nama, slug) VALUES (?, ?)");
            $stmt->execute([$nama, $slug]);
            $message = 'Kategori berhasil ditambahkan';
        } catch (Exception $e) {
            $message = 'Gagal menambahkan: ' . $e->getMessage();
        }
    }
}

$kategoris = $db->query("SELECT * FROM kategori ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori</title>
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
        <h2>Kelola Kategori</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" class="row g-3 mt-2 mb-4">
            <div class="col-md-6">
                <input type="text" name="nama" class="form-control" placeholder="Nama Kategori" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategoris as $k): ?>
                <tr>
                    <td><?= $k['id'] ?></td>
                    <td><?= htmlspecialchars($k['nama']) ?></td>
                    <td><?= htmlspecialchars($k['slug']) ?></td>
                    <td>
                        <a href="/backend/admin/kategori/edit.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="/backend/admin/kategori/index.php?delete=<?= $k['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
