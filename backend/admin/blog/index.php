<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$message = '';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $db->prepare("DELETE FROM blog WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Blog berhasil dihapus';
    } catch (Exception $e) {
        $message = 'Gagal menghapus: ' . $e->getMessage();
    }
}

$blogs = $db->query("SELECT * FROM blog ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Kelola Blog</h2>
            <a href="/backend/admin/blog/create.php" class="btn btn-primary">Tambah Blog</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['judul']) ?></td>
                    <td><?= htmlspecialchars($b['slug']) ?></td>
                    <td><?= date('d/m/Y', strtotime($b['created_at'])) ?></td>
                    <td>
                        <a href="/backend/admin/blog/edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="/backend/admin/blog/index.php?delete=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus blog ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
