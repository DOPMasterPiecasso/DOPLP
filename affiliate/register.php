<?php
require_once __DIR__ . '/includes.php';
require_once __DIR__ . '/layout.php';

$affiliatePage = 'register';

if (affiliateCurrent()) {
    header('Location: /affiliate/panel');
    exit();
}

$old = [
    'pic_pemilik' => '',
    'nama_usaha' => '',
    'no_telepon' => '',
    'kota' => '',
    'alamat' => '',
    'username' => '',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi keamanan kedaluwarsa. Silakan muat ulang halaman lalu isi lagi.';
    } else {
        foreach ($old as $key => $value) {
            $old[$key] = trim((string)($_POST[$key] ?? ''));
        }
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password_confirm'] ?? '');

        if ($old['pic_pemilik'] === '') {
            $error = 'PIC / Nama Pemilik wajib diisi.';
        } elseif ($old['nama_usaha'] === '') {
            $error = 'Nama Tempat Usaha wajib diisi.';
        } elseif ($old['no_telepon'] === '' || !preg_match('/^[0-9+\-\s]{8,20}$/', $old['no_telepon'])) {
            $error = 'No Telepon wajib diisi dengan angka yang benar (8-20 digit).';
        } elseif ($old['kota'] === '') {
            $error = 'Kota wajib diisi.';
        } elseif ($old['alamat'] === '') {
            $error = 'Alamat wajib diisi.';
        } elseif ($old['username'] === '' || !preg_match('/^[A-Za-z0-9._\-]{4,50}$/', $old['username'])) {
            $error = 'Username wajib diisi, minimal 4 karakter, boleh huruf, angka, titik, strip, dan garis bawah.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($password !== $password2) {
            $error = 'Konfirmasi password tidak sama dengan password.';
        }
    }

    $photo = null;
    if ($error === '' && (!isset($_FILES['photo_usaha']) || $_FILES['photo_usaha']['error'] === UPLOAD_ERR_NO_FILE)) {
        $error = 'Photo Usaha wajib diunggah.';
    }

    if ($error === '') {
        $uploadError = '';
        $photo = affiliateHandlePhotoUpload($uploadError);
        if ($uploadError !== '') {
            $error = $uploadError;
        }
    }

    if ($error === '') {
        try {
            $db = getConnection();
            $check = $db->prepare('SELECT id FROM affiliate WHERE username = :username LIMIT 1');
            $check->execute(['username' => $old['username']]);
            if ($check->fetch()) {
                $error = 'Username "' . $old['username'] . '" sudah dipakai mitra lain. Silakan pilih username lain.';
            }
        } catch (Throwable $e) {
            $error = 'Database tidak dapat diakses. Silakan coba lagi nanti.';
        }
    }

    if ($error === '') {
        try {
            $db = getConnection();
            $stmt = $db->prepare(
                'INSERT INTO affiliate (pic_pemilik, nama_usaha, no_telepon, photo_usaha, kota, alamat, username, password, status)
                 VALUES (:pic_pemilik, :nama_usaha, :no_telepon, :photo_usaha, :kota, :alamat, :username, :password, "belum_approve")'
            );
            $stmt->execute([
                'pic_pemilik' => $old['pic_pemilik'],
                'nama_usaha' => $old['nama_usaha'],
                'no_telepon' => $old['no_telepon'],
                'photo_usaha' => $photo,
                'kota' => $old['kota'],
                'alamat' => $old['alamat'],
                'username' => $old['username'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            header('Location: /affiliate/login?registered=1');
            exit();
        } catch (Throwable $e) {
            $error = 'Pendaftaran gagal disimpan. Silakan coba lagi.';
        }
    }
}

affiliateLayoutHead('Daftar Jadi Mitra Affiliate | dopagency', 'Daftar jadi mitra affiliate dopagency. Isi data usaha dan akun login untuk mendaftar jadi mitra.');
?>

<div class="af-auth-split">
  <?php affiliateAuthVisual(); ?>
  <div class="af-auth-form-wrap">
    <div class="af-auth-form-container" style="max-width: 580px;">
      <div class="af-hero" style="padding-top:0; margin-bottom: 24px;">
        <span class="af-hero__icon" style="width:76px; height:76px; margin-bottom:16px;"><i class="mi" style="font-size:40px;">handshake</i></span>
        <h1 style="font-size:26px;">Daftar Jadi Mitra Affiliate</h1>
        <p style="font-size:15px;">Isi data di bawah ini dengan lengkap. Setelah itu tim kami akan memeriksa datanya, dan Bapak/Ibu bisa masuk ke akun mitra.</p>
      </div>

      <?= affiliateNotice($error, 'error') ?>

      <form method="POST" action="/register/affiliate" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

        <section class="af-card">
          <div class="af-card__head is-teal">
            <i class="mi">storefront</i>
            <div>
              <h2>1. Data Tempat Usaha</h2>
              <small>Cantumkan data usaha Bapak/Ibu sebenarnya</small>
            </div>
          </div>
          <div class="af-card__body">
            <div class="af-grid">
              <?= affiliateField('pic_pemilik', 'PIC Nama Pemilik', 'badge', $old['pic_pemilik'], ['required' => true, 'placeholder' => 'Contoh: Budi Santoso', 'autocomplete' => 'name']) ?>
              <?= affiliateField('nama_usaha', 'Nama Tempat Usaha', 'storefront', $old['nama_usaha'], ['required' => true, 'placeholder' => 'Contoh: Toko Maju Jaya']) ?>
              <?= affiliateField('no_telepon', 'No Telepon (WhatsApp)', 'phone', $old['no_telepon'], ['required' => true, 'placeholder' => '0812xxxxxxx', 'autocomplete' => 'tel']) ?>
              <?= affiliateField('kota', 'Kota', 'location_city', $old['kota'], ['required' => true, 'placeholder' => 'Contoh: Tangerang Selatan']) ?>
              <?= affiliateField('alamat', 'Alamat Lengkap', 'map', $old['alamat'], ['type' => 'textarea', 'full' => true, 'required' => true, 'placeholder' => 'Nama jalan, nomor, kelurahan, kecamatan']) ?>
              <div class="af-field af-field--full">
                <label for="af-photo_usaha"><i class="mi">photo_camera</i> Photo Usaha <span class="req">*</span></label>
                <div class="af-file">
                  <input type="file" id="af-photo_usaha" name="photo_usaha" accept="image/*" class="af-file__input">
                  <label class="af-file__box" for="af-photo_usaha" id="afPhotoBox">
                    <img class="af-file__preview" id="afPhotoPreview" alt="Pratinjau foto usaha">
                    <span class="af-file__placeholder">
                      <i class="mi">add_photo_alternate</i>
                      <b>Pilih Gambar</b>
                    </span>
                  </label>
                </div>
                <span class="af-field__hint">Klik kotak untuk memilih foto depan toko / tempat usaha. Format JPG, PNG, WEBP, atau GIF. Maksimal 2MB.</span>
              </div>
            </div>
          </div>
        </section>

        <section class="af-card">
          <div class="af-card__head is-blue">
            <i class="mi">lock</i>
            <div>
              <h2>2. Akun Login</h2>
              <small>Dipakai untuk masuk ke halaman mitra</small>
            </div>
          </div>
          <div class="af-card__body">
            <div class="af-grid">
              <?= affiliateField('username', 'Username', 'alternate_email', $old['username'], ['required' => true, 'placeholder' => 'Contoh: budi.maju', 'autocomplete' => 'username', 'hint' => 'Minimal 4 karakter. Huruf, angka, titik, strip, atau garis bawah.']) ?>
              <?= affiliateField('password', 'Password', 'lock', '', ['type' => 'password', 'required' => true, 'placeholder' => 'Minimal 6 karakter', 'autocomplete' => 'new-password']) ?>
              <?= affiliateField('password_confirm', 'Ulangi Password', 'lock_reset', '', ['type' => 'password', 'required' => true, 'placeholder' => 'Ketik ulang password', 'autocomplete' => 'new-password']) ?>
              <div class="af-field af-field--full">
                <div class="af-status">
                  <i class="mi">hourglass_top</i>
                  <div>
                    <b>Status Akun: Belum Approve</b>
                    <span>Menunggu pemeriksaan dari admin dopagency. Bapak/Ibu akan diberi tahu lewat WhatsApp setelah disetujui.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <div class="af-actions">
          <button type="submit" class="af-btn"><i class="mi">how_to_reg</i> Daftar Sekarang</button>
          <span class="af-field__hint">Sudah punya akun? <a href="/affiliate/login">Masuk di sini</a></span>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    var input = document.getElementById('af-photo_usaha');
    var box = document.getElementById('afPhotoBox');
    var preview = document.getElementById('afPhotoPreview');
    if (!input || !box || !preview) return;
    input.addEventListener('change', function () {
      if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
        box.classList.add('is-selected');
      } else {
        preview.removeAttribute('src');
        box.classList.remove('is-selected');
      }
    });
  })();
</script>

<?php affiliateLayoutFoot(false); ?>
