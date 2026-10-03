<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';

$db = getConnection();
$message = '';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $db->prepare("DELETE FROM portfolio WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Portfolio berhasil dihapus';
    } catch (Exception $e) {
        $message = 'Gagal menghapus: ' . $e->getMessage();
    }
}

$portfolios = $db->query("
    SELECT p.*, k.nama as kategori_nama 
    FROM portfolio p 
    LEFT JOIN kategori k ON p.kategori_id = k.id 
    ORDER BY p.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Kelola Portfolio</h2>
            <a href="/backend/admin/portfolio/create.php" class="btn btn-primary">Tambah Portfolio</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Client</th>
                    <th>Tahun</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($portfolios as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['judul']) ?></td>
                    <td><?= htmlspecialchars($p['kategori_nama'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['client'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['tahun'] ?? '-') ?></td>
                    <td>
                        <?php if ($p['gambar']): ?>
                            <img src="/uploads/portfolio/<?= htmlspecialchars($p['gambar']) ?>" width="60" alt="">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/backend/admin/portfolio/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="/backend/admin/portfolio/index.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus portfolio ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
