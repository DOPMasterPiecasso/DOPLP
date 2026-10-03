<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare('SELECT gambar FROM blog WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            $message = 'Artikel tidak ditemukan.';
            $messageType = 'info';
        } else {
            try {
                $del = $db->prepare('DELETE FROM blog WHERE id = ?');
                $del->execute([$id]);
                $message = 'Artikel berhasil dihapus.';

                if (!empty($row['gambar'])) {
                    $file = __DIR__ . '/../../../uploads/blog/' . $row['gambar'];
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            } catch (Exception $e) {
                $message = 'Gagal menghapus artikel: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$blogs = $db->query('SELECT * FROM blog ORDER BY id DESC')->fetchAll();

adminLayoutHeader(
    'Blog',
    'blog',
    count($blogs) . ' artikel terdaftar',
    [
        ['label' => 'Tambah Artikel', 'url' => '/backend/admin/blog/create.php', 'icon' => 'fa-plus', 'class' => ''],
        ['label' => 'Lihat Website', 'url' => '/', 'icon' => 'fa-external-link', 'class' => 'admin-btn--ghost'],
    ]
);

adminAlert($message, $messageType);
?>

<div class="panel">
    <div class="panel__head">
        <h3>Daftar Artikel</h3>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($blogs)): ?>
            <div class="empty-state">
                <i class="fa fa-newspaper-o"></i>
                <p>Belum ada artikel. Tulislah artikel pertama Anda.</p>
                <a class="admin-btn" href="/backend/admin/blog/create.php"><i class="fa fa-plus"></i> Tambah Artikel</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Gambar</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Dibuat</th>
                            <th style="width:190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogs as $b): ?>
                            <tr>
                                <td>
                                    <?php if ($b['gambar']): ?>
                                        <img class="table-thumb" src="/uploads/blog/<?= htmlspecialchars($b['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <?php else: ?>
                                        <span class="badge badge--muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($b['judul'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($b['penulis'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-num"><?= htmlspecialchars(date('d M Y', strtotime($b['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/blog/edit.php?id=<?= (int)$b['id'] ?>">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <form class="table-form" method="POST" onsubmit="return confirm('Hapus artikel &quot;<?= htmlspecialchars(addslashes($b['judul']), ENT_QUOTES, 'UTF-8') ?>&quot;? Tindakan ini tidak bisa dibatalkan.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">
                                                <i class="fa fa-trash-o"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php adminLayoutFooter(); ?>