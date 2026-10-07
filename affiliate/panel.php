<?php
require_once __DIR__ . '/includes.php';
require_once __DIR__ . '/layout.php';

$affiliatePage = 'panel';

affiliateRequireAuth();

$me = affiliateCurrent();

try {
    $db = getConnection();
    $stmt = $db->prepare('SELECT * FROM affiliate WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $me['id']]);
    $row = $stmt->fetch();
} catch (Throwable $e) {
    $row = false;
}

if (!$row) {
    affiliateLogout();
    header('Location: /affiliate/login');
    exit();
}

$approved = affiliateIsApproved($row['status']);

$message = '';
$messageType = 'success';

// Data rekening (Rembursmen) hanya bisa diisi/diubah setelah akun di-approve.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi keamanan kedaluwarsa. Silakan muat ulang halaman lalu isi lagi.';
        $messageType = 'error';
    } elseif (!$approved) {
        $message = 'Data rekening baru bisa diisi setelah akun Bapak/Ibu di-approve admin.';
        $messageType = 'info';
    } else {
        $noRekening = trim((string)($_POST['no_rekening'] ?? ''));
        $atasNama = trim((string)($_POST['atas_nama'] ?? ''));

        if ($noRekening === '' || !preg_match('/^[0-9 \-]{5,40}$/', $noRekening)) {
            $message = 'No Rekening wajib diisi dengan angka yang benar (5-40 karakter).';
            $messageType = 'error';
        } elseif ($atasNama === '') {
            $message = 'Atas Nama rekening wajib diisi.';
            $messageType = 'error';
        } else {
            try {
                $upd = $db->prepare('UPDATE affiliate SET no_rekening = :no_rekening, atas_nama = :atas_nama WHERE id = :id');
                $upd->execute([
                    'no_rekening' => $noRekening,
                    'atas_nama' => $atasNama,
                    'id' => $row['id'],
                ]);

                $refetch = $db->prepare('SELECT * FROM affiliate WHERE id = :id LIMIT 1');
                $refetch->execute(['id' => $row['id']]);
                $row = $refetch->fetch() ?: $row;
                $approved = affiliateIsApproved($row['status']);

                $message = 'Data rekening (Rembursmen) berhasil disimpan.';
            } catch (Throwable $e) {
                $message = 'Data rekening gagal disimpan. Silakan coba lagi.';
                $messageType = 'error';
            }
        }
    }
}

affiliateLayoutHead('Panel Mitra Affiliate | dopagency', 'Panel mitra affiliate dopagency: melihat status akun, data usaha, dan data rekening.');
?>

<main class="af-main">
  <div class="af-hero">
    <span class="af-hero__icon"><i class="mi">waving_hand</i></span>
    <h1>Halo, <?= htmlspecialchars($row['pic_pemilik'], ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p>Berikut data usaha dan akun mitra Bapak/Ibu di dopagency.</p>
  </div>

  <?= affiliateNotice(
      $approved
          ? 'Akun Bapak/Ibu sudah APPROVE. Selamat bermitra bersama dopagency!'
          : 'Akun Bapak/Ibu masih BELUM APPROVE. Tim kami sedang memeriksa data usaha Bapak/Ibu.',
      $approved ? 'success' : 'info'
  ) ?>

  <div class="af-boxes">
    <div class="af-box <?= $approved ? 'is-green' : 'is-yellow' ?>">
      <h3>Status Akun</h3>
      <b><?= htmlspecialchars(affiliateStatusLabel($row['status']), ENT_QUOTES, 'UTF-8') ?></b>
      <p><?= $approved ? 'Siap dipakai untuk promosi' : 'Menunggu verifikasi admin' ?></p>
      <i class="mi"><?= $approved ? 'verified' : 'pending' ?></i>
    </div>
    <div class="af-box is-blue">
      <h3>No Telepon</h3>
      <b><?= htmlspecialchars($row['no_telepon'], ENT_QUOTES, 'UTF-8') ?></b>
      <p>Nomor WhatsApp aktif</p>
      <i class="mi">phone</i>
    </div>
    <div class="af-box is-teal">
      <h3>Kota</h3>
      <b><?= htmlspecialchars($row['kota'], ENT_QUOTES, 'UTF-8') ?></b>
      <p>Lokasi tempat usaha</p>
      <i class="mi">location_city</i>
    </div>
  </div>

  <section class="af-card">
    <div class="af-card__head">
      <i class="mi">storefront</i>
      <div>
        <h2>Data Tempat Usaha</h2>
        <small>Dipakai untuk verifikasi dan komunikasi</small>
      </div>
    </div>
    <div class="af-card__body">
      <?php if (!empty($row['photo_usaha'])): ?>
        <img src="<?= htmlspecialchars(affiliatePhotoUrl($row['photo_usaha']), ENT_QUOTES, 'UTF-8') ?>"
          alt="Foto <?= htmlspecialchars($row['nama_usaha'], ENT_QUOTES, 'UTF-8') ?>"
          style="width:100%;max-width:340px;height:auto;border-radius:8px;border:2px solid var(--af-line);margin-bottom:20px;display:block;">
      <?php endif; ?>

      <table class="af-detail">
        <tr>
          <th>PIC Nama Pemilik</th>
          <td><?= htmlspecialchars($row['pic_pemilik'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>Nama Tempat Usaha</th>
          <td><?= htmlspecialchars($row['nama_usaha'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>No Telepon</th>
          <td><?= htmlspecialchars($row['no_telepon'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>Kota</th>
          <td><?= htmlspecialchars($row['kota'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>Alamat</th>
          <td><?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>Username</th>
          <td><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
          <th>Status</th>
          <td>
            <span class="af-badge <?= $approved ? 'is-approve' : 'is-wait' ?>">
              <i class="mi"><?= $approved ? 'check_circle' : 'schedule' ?></i>
              <?= htmlspecialchars(affiliateStatusLabel($row['status']), ENT_QUOTES, 'UTF-8') ?>
            </span>
          </td>
        </tr>
        <tr>
          <th>Tanggal Daftar</th>
          <td><?= htmlspecialchars(date('d M Y', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      </table>
    </div>
  </section>

  <section class="af-card">
    <div class="af-card__head is-green">
      <i class="mi">account_balance</i>
      <div>
        <h2>Data Rekening (Rembursmen)</h2>
        <small>Rekening pencairan komisi mitra</small>
      </div>
    </div>
    <div class="af-card__body">
      <?php if ($message !== ''): ?>
        <?= affiliateNotice($message, $messageType) ?>
      <?php endif; ?>

      <?php if ($approved): ?>
        <p class="af-field__hint">Data ini dipakai untuk pencairan komisi (Rembursmen) dan bisa diubah kapan saja selama akun aktif.</p>
        <form method="POST" action="/affiliate/panel">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
          <div class="af-grid">
            <?= affiliateField('no_rekening', 'No Rekening', 'account_balance', (string)($row['no_rekening'] ?? ''), ['required' => true, 'placeholder' => 'Contoh: 1234567890', 'hint' => 'Isi dengan angka rekening bank Bapak/Ibu.']) ?>
            <?= affiliateField('atas_nama', 'Atas Nama', 'credit_card', (string)($row['atas_nama'] ?? ''), ['required' => true, 'placeholder' => 'Nama sesuai buku rekening']) ?>
          </div>
          <div class="af-actions">
            <button type="submit" class="af-btn"><i class="mi">save</i> Simpan Data Rekening</button>
          </div>
        </form>
      <?php else: ?>
        <div class="af-status">
          <i class="mi">hourglass_top</i>
          <div>
            <b>Data rekening diisi setelah akun di-approve</b>
            <span>No Rekening dan Atas Nama untuk pencairan komisi diisi lewat halaman ini setelah admin menyetujui akun Bapak/Ibu.</span>
          </div>
        </div>
        <table class="af-detail">
          <tr>
            <th>No Rekening</th>
            <td><?= htmlspecialchars($row['no_rekening'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th>Atas Nama</th>
            <td><?= htmlspecialchars($row['atas_nama'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        </table>
      <?php endif; ?>

      <div class="af-actions">
        <a class="af-btn is-ghost is-small" href="/affiliate/logout"><i class="mi">logout</i> Keluar dari Akun</a>
        <a class="af-btn is-ghost is-small" href="/"><i class="mi">home</i> Kembali ke Website</a>
      </div>
    </div>
  </section>
</main>

<?php affiliateLayoutFoot(); ?>
