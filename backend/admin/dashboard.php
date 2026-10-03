<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../config/database.php';

$db = getConnection();
$portfolioCount = $db->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
$kategoriCount = $db->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
$blogCount = $db->query("SELECT COUNT(*) FROM blog")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/backend/admin/dashboard.php">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/backend/admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Portfolio</h5>
                        <p class="card-text display-6"><?= $portfolioCount ?></p>
                        <a href="/backend/admin/portfolio/index.php" class="text-white">Kelola Portfolio</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Kategori</h5>
                        <p class="card-text display-6"><?= $kategoriCount ?></p>
                        <a href="/backend/admin/kategori/index.php" class="text-white">Kelola Kategori</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Blog</h5>
                        <p class="card-text display-6"><?= $blogCount ?></p>
                        <a href="/backend/admin/blog/index.php" class="text-white">Kelola Blog</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
