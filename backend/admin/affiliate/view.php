<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';
require_once __DIR__ . '/../../../affiliate/includes.php';

$db = getConnection();
$message = '';
$messageType = 'success';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();
    $action = (string)($_POST['action'] ?? '');
    $postId = (int)($_POST['id'] ?? 0);

    if ($postId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $status = $action === 'approve' ? 'approve' : 'belum_approve';
        try {
            $upd = $db->prepare('UPDATE affiliate SET status = ? WHERE id = ?');
            $upd->execute([$status, $postId]);
            $message = $status === 'approve'
                ? 'Mitra disetujui (Approve). Mitra sudah bisa masuk ke akunnya.'
                : 'Status mitra dikembalikan jadi Belum Approve.';
        } catch (Exception $e) {
            $message = 'Gagal memperbarui status: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Aksi tidak dikenali.';
        $messageType = 'info';
    }

    $id = $id > 0 ? $id : $postId;
}

$stmt = $db->prepare('SELECT * FROM affiliate WHERE id = ?');
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a) {
    header('Location: /backend/admin/affiliate/index.php');
    exit();
}

$approved = $a['status'] === 'approve';

adminLayoutHeader(
    'Detail Mitra',
    'affiliate',
    $a['nama_usaha'],
    [
        ['label' => 'Edit', 'url' => '/backend/admin/affiliate/edit.php?id=' . (int)$a['id'], 'icon' => 'edit', 'material' => true, 'class' => 'admin-btn--primary'],
        ['label' => 'Kembali', 'url' => '/backend/admin/affiliate/index.php', 'icon' => 'arrow_back', 'material' => true, 'class' => 'admin-btn--ghost'],
    ],
    ['bright' => true, 'css' => ['/css/affiliate-admin.css']]
);

adminAlert($message, $messageType);
?>

<div class="al-boxes">
    <div class="al-box <?= $approved ? 'is-green' : 'is-yellow' ?>">
        <h3><?= htmlspecialchars(affiliateStatusLabel($a['status']), ENT_QUOTES, 'UTF-8') ?></h3>
        <span>Status Akun</span>
        <p><?= $approved ? 'Sudah bisa masuk ke akun mitra' : 'Menunggu pemeriksaan admin' ?></p>
        <i class="material-icons"><?= $approved ? 'verified_user' : 'pending_actions' ?></i>
    </div>
    <div class="al-box is-blue">
        <h3><?= htmlspecialchars(date('d M Y', strtotime($a['created_at'])), ENT_QUOTES, 'UTF-8') ?></h3>
        <span>Tanggal Daftar</span>
        <p>Mitra baru terdaftar</p>
        <i class="material-icons">event</i>
    </div>
    <a class="al-box is-teal" href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $a['no_telepon']), ENT_QUOTES, 'UTF-8') ?>">
        <h3><?= htmlspecialchars($a['no_telepon'], ENT_QUOTES, 'UTF-8') ?></h3>
        <span>No Telepon</span>
        <p>Klik untuk menelepon</p>
        <i class="material-icons">phone</i>
    </a>
</div>

<div class="panel">
    <div class="panel__head">
        <h3><i class="material-icons mi-lg">storefront</i> Data Tempat Usaha</h3>
        <?php if (!$approved): ?>
            <form class="table-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button type="submit" class="admin-btn admin-btn--success admin-btn--sm">
                    <i class="material-icons">check_circle</i> Approve Mitra
                </button>
            </form>
        <?php else: ?>
            <form class="table-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm" title="Kembalikan jadi Belum Approve">
                    <i class="material-icons">block</i> Tolak
                </button>
            </form>
        <?php endif; ?>
    </div>
    <div class="panel__body">
        <?php if (!empty($a['photo_usaha'])): ?>
            <img class="al-photo" src="/uploads/affiliate/<?= htmlspecialchars($a['photo_usaha'], ENT_QUOTES, 'UTF-8') ?>"
                alt="Foto <?= htmlspecialchars($a['nama_usaha'], ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <table class="al-detail">
            <tr>
                <th><i class="material-icons">badge</i> PIC Nama Pemilik</th>
                <td><?= htmlspecialchars($a['pic_pemilik'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">storefront</i> Nama Tempat Usaha</th>
                <td><?= htmlspecialchars($a['nama_usaha'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">phone</i> No Telepon</th>
                <td><?= htmlspecialchars($a['no_telepon'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">location_city</i> Kota</th>
                <td><?= htmlspecialchars($a['kota'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">map</i> Alamat</th>
                <td><?= htmlspecialchars($a['alamat'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3><i class="material-icons mi-lg">account_circle</i> Akun Login &amp; Status</h3>
    </div>
    <div class="panel__body">
        <table class="al-detail">
            <tr>
                <th><i class="material-icons">alternate_email</i> Username</th>
                <td><?= htmlspecialchars($a['username'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">lock</i> Password</th>
                <td>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull; <span class="field__hint">Tersimpan terenkripsi. Reset lewat Edit.</span></td>
            </tr>
            <tr>
                <th><i class="material-icons">verified_user</i> Status</th>
                <td>
                    <span class="al-pill <?= $approved ? 'is-approve' : 'is-wait' ?>">
                        <i class="material-icons"><?= $approved ? 'check_circle' : 'schedule' ?></i>
                        <?= htmlspecialchars(affiliateStatusLabel($a['status']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h3><i class="material-icons mi-lg">account_balance</i> Data Rekening (Rembursmen)</h3>
    </div>
    <div class="panel__body">
        <table class="al-detail">
            <tr>
                <th><i class="material-icons">account_balance</i> No Rekening</th>
                <td><?= htmlspecialchars($a['no_rekening'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th><i class="material-icons">credit_card</i> Atas Nama</th>
                <td><?= htmlspecialchars($a['atas_nama'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        </table>

        <div class="form-actions">
            <a class="admin-btn admin-btn--primary" href="/backend/admin/affiliate/edit.php?id=<?= (int)$a['id'] ?>">
                <i class="material-icons">edit</i> Edit Data Mitra
            </a>
            <a class="admin-btn admin-btn--ghost" href="/backend/admin/affiliate/index.php">
                <i class="material-icons">arrow_back</i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<?php adminLayoutFooter(); ?>
