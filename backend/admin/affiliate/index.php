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
    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0 && $action === 'delete') {
        $stmt = $db->prepare('SELECT photo_usaha FROM affiliate WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            $message = 'Mitra tidak ditemukan.';
            $messageType = 'info';
        } else {
            try {
                $del = $db->prepare('DELETE FROM affiliate WHERE id = ?');
                $del->execute([$id]);
                $message = 'Mitra berhasil dihapus.';

                if (!empty($row['photo_usaha'])) {
                    $file = __DIR__ . '/../../../uploads/affiliate/' . $row['photo_usaha'];
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            } catch (Exception $e) {
                $message = 'Gagal menghapus mitra: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $status = $action === 'approve' ? 'approve' : 'belum_approve';
        try {
            $upd = $db->prepare('UPDATE affiliate SET status = ? WHERE id = ?');
            $upd->execute([$status, $id]);
            $message = $status === 'approve'
                ? 'Mitra disetujui (Approve). Mitra sudah bisa masuk ke akunnya.'
                : 'Status mitra dikembalikan jadi Belum Approve.';
        } catch (Exception $e) {
            $message = 'Gagal memperbarui status: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$stats = [
    'total' => (int)$db->query('SELECT COUNT(*) FROM affiliate')->fetchColumn(),
    'approve' => (int)$db->query("SELECT COUNT(*) FROM affiliate WHERE status = 'approve'")->fetchColumn(),
    'pending' => (int)$db->query("SELECT COUNT(*) FROM affiliate WHERE status = 'belum_approve'")->fetchColumn(),
];

$affiliates = $db->query('SELECT * FROM affiliate ORDER BY id DESC')->fetchAll();

adminLayoutHeader(
    'Affiliate',
    'affiliate',
    $stats['total'] . ' mitra terdaftar',
    [
        ['label' => 'Tambah Mitra', 'url' => '/backend/admin/affiliate/create.php', 'icon' => 'person_add', 'material' => true, 'class' => 'admin-btn--primary'],
        ['label' => 'Halaman Pendaftaran', 'url' => '/register/affiliate', 'icon' => 'open_in_new', 'material' => true, 'class' => 'admin-btn--ghost'],
    ],
    ['bright' => true, 'css' => ['/css/affiliate-admin.css']]
);

adminAlert($message, $messageType);
?>

<div class="al-boxes">
    <a class="al-box is-blue" href="/backend/admin/affiliate/index.php">
        <h3><?= $stats['total'] ?></h3>
        <span>Total Mitra</span>
        <p>Semua mitra yang terdaftar</p>
        <i class="material-icons">groups</i>
    </a>
    <a class="al-box is-green" href="/backend/admin/affiliate/index.php">
        <h3><?= $stats['approve'] ?></h3>
        <span>Sudah Approve</span>
        <p>Mitra aktif, sudah bisa masuk</p>
        <i class="material-icons">verified_user</i>
    </a>
    <a class="al-box is-yellow" href="/backend/admin/affiliate/index.php">
        <h3><?= $stats['pending'] ?></h3>
        <span>Belum Approve</span>
        <p>Menunggu pemeriksaan Bapak/Ibu admin</p>
        <i class="material-icons">pending_actions</i>
    </a>
</div>

<div class="panel">
    <div class="panel__head">
        <h3>Daftar Mitra Affiliate</h3>
    </div>
    <div class="panel__body panel__body--flush">
        <?php if (empty($affiliates)): ?>
            <div class="empty-state">
                <i class="material-icons mi-xl">person_add</i>
                <p>Belum ada mitra terdaftar. Undang Bapak/Ibu untuk mendaftar lewat halaman pendaftaran.</p>
                <a class="admin-btn admin-btn--primary" href="/backend/admin/affiliate/create.php"><i class="material-icons">person_add</i> Tambah Mitra</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mitra / Tempat Usaha</th>
                            <th>Kota</th>
                            <th>No Telepon</th>
                            <th>Rekening</th>
                            <th>Status</th>
                            <th style="width:320px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($affiliates as $a): ?>
                            <?php $approved = $a['status'] === 'approve'; ?>
                            <tr>
                                <td>
                                    <div class="al-owner">
                                        <?php if (!empty($a['photo_usaha'])): ?>
                                            <img class="al-owner__thumb" src="/uploads/affiliate/<?= htmlspecialchars($a['photo_usaha'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                                        <?php else: ?>
                                            <span class="al-owner__thumb is-empty"><i class="material-icons">storefront</i></span>
                                        <?php endif; ?>
                                        <span>
                                            <b><a class="al-owner__link" href="/backend/admin/affiliate/view.php?id=<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['pic_pemilik'], ENT_QUOTES, 'UTF-8') ?></a></b>
                                            <small><?= htmlspecialchars($a['nama_usaha'], ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($a['username'], ENT_QUOTES, 'UTF-8') ?></small>
                                        </span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($a['kota'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-num"><?= htmlspecialchars($a['no_telepon'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="al-meta"><?= htmlspecialchars($a['no_rekening'] ?: '-', ENT_QUOTES, 'UTF-8') ?><br>
                                        a.n. <?= htmlspecialchars($a['atas_nama'] ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <span class="al-pill <?= $approved ? 'is-approve' : 'is-wait' ?>">
                                        <i class="material-icons"><?= $approved ? 'check_circle' : 'schedule' ?></i>
                                        <?= $approved ? 'Approve' : 'Belum Approve' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="al-actions">
                                        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/affiliate/view.php?id=<?= (int)$a['id'] ?>">
                                            <i class="material-icons">visibility</i> Detail
                                        </a>
                                        <?php if ($approved): ?>
                                            <form class="table-form" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" title="Kembalikan jadi Belum Approve">
                                                    <i class="material-icons">block</i> Tolak
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form class="table-form" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                                <button type="submit" class="admin-btn admin-btn--success admin-btn--sm">
                                                    <i class="material-icons">check_circle</i> Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a class="admin-btn admin-btn--ghost admin-btn--sm" href="/backend/admin/affiliate/edit.php?id=<?= (int)$a['id'] ?>">
                                            <i class="material-icons">edit</i> Edit
                                        </a>
                                        <form class="table-form" method="POST" onsubmit="return confirm('Hapus mitra &quot;<?= htmlspecialchars(addslashes($a['nama_usaha']), ENT_QUOTES, 'UTF-8') ?>&quot;? Tindakan ini tidak bisa dibatalkan.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm">
                                                <i class="material-icons">delete</i> Hapus
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
