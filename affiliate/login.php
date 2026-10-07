<?php
require_once __DIR__ . '/includes.php';
require_once __DIR__ . '/layout.php';

$affiliatePage = 'login';

if (affiliateCurrent()) {
    header('Location: /affiliate/panel');
    exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $result = affiliateLogin($username, $password);

    if ($result['ok']) {
        header('Location: /affiliate/panel');
        exit();
    }

    $error = $result['message'];
}

$registered = isset($_GET['registered']);
$locked = authThrottleState();

affiliateLayoutHead('Masuk Akun Mitra Affiliate | dopagency', 'Masuk ke akun mitra affiliate dopagency untuk melihat status akun, data usaha, dan rekening komisi.');
?>

<div class="af-auth-split">
  <?php affiliateAuthVisual(); ?>
  <div class="af-auth-form-wrap">
    <div class="af-auth-form-container">
      <div class="af-hero" style="padding-top:0; margin-bottom: 24px;">
        <span class="af-hero__icon" style="width:76px; height:76px; margin-bottom:16px;"><i class="mi" style="font-size:40px;">login</i></span>
        <h1 style="font-size:26px;">Masuk Akun Mitra</h1>
        <p style="font-size:15px;">Gunakan username dan password yang Bapak/Ibu buat saat mendaftar.</p>
      </div>

      <div class="af-auth" style="max-width:100%;">
        <?php if ($registered): ?>
          <?= affiliateNotice('Pendaftaran berhasil. Sekarang tunggu admin memverifikasi data Bapak/Ibu, lalu masuk dengan username dan password tadi.', 'success') ?>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
          <?= affiliateNotice($error, 'error') ?>
        <?php elseif ($locked['locked']): ?>
          <?= affiliateNotice('Terlalu banyak percobaan gagal. Coba lagi dalam ' . ceil($locked['remaining'] / 60) . ' menit.', 'info') ?>
        <?php endif; ?>

        <section class="af-card">
          <div class="af-card__head is-teal">
            <i class="mi">account_circle</i>
            <div>
              <h2>Login Mitra</h2>
              <small>Isi data Bapak/Ibu dengan benar</small>
            </div>
          </div>
          <div class="af-card__body">
            <form method="POST" action="/affiliate/login" autocomplete="on">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

              <div class="af-grid" style="grid-template-columns: 1fr; gap: 0;">
                <?= affiliateField('username', 'Username', 'alternate_email', $username, ['required' => true, 'placeholder' => 'Username Bapak/Ibu', 'autocomplete' => 'username']) ?>
                <?= affiliateField('password', 'Password', 'lock', '', ['type' => 'password', 'required' => true, 'placeholder' => 'Password Bapak/Ibu', 'autocomplete' => 'current-password']) ?>
              </div>

              <div class="af-actions" style="margin-top: 20px;">
                <button type="submit" class="af-btn is-block" <?= $locked['locked'] ? 'disabled' : '' ?>>
                  <i class="mi">login</i> Masuk
                </button>
              </div>
            </form>

            <p class="af-switch">Belum punya akun mitra? <a href="/register/affiliate">Daftar di sini</a></p>
          </div>
        </section>

        <div class="af-tips">
          <h3><i class="mi">tips_and_updates</i> Catatan untuk Bapak/Ibu</h3>
          <ul>
            <li><i class="mi">check_circle</i><span>Akun baru bisa dipakai setelah status berubah jadi <b>Approve</b> oleh admin.</span></li>
            <li><i class="mi">check_circle</i><span>Lupa password? Hubungi admin dopagency lewat WhatsApp untuk direset.</span></li>
            <li><i class="mi">check_circle</i><span>Jangan bagikan password kepada siapa pun, termasuk orang yang mengaku admin.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<?php affiliateLayoutFoot(false); ?>
