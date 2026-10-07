<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-layout.php';

$db = getConnection();
$message = '';
$messageType = 'success';
$id = (int)($_GET['id'] ?? 0);
$uploadDir = __DIR__ . '/../../../uploads/affiliate/';
$maxSize = 2 * 1024 * 1024;
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$stmt = $db->prepare('SELECT * FROM affiliate WHERE id = ?');
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a) {
    header('Location: /backend/admin/affiliate/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRequireCsrf();

    $a['pic_pemilik'] = trim((string)($_POST['pic_pemilik'] ?? ''));
    $a['nama_usaha'] = trim((string)($_POST['nama_usaha'] ?? ''));
    $a['no_telepon'] = trim((string)($_POST['no_telepon'] ?? ''));
    $a['kota'] = trim((string)($_POST['kota'] ?? ''));
    $a['alamat'] = trim((string)($_POST['alamat'] ?? ''));
    $a['username'] = trim((string)($_POST['username'] ?? ''));
    $a['no_rekening'] = trim((string)($_POST['no_rekening'] ?? ''));
    $a['atas_nama'] = trim((string)($_POST['atas_nama'] ?? ''));
    $a['status'] = in_array($_POST['status'] ?? '', ['approve', 'belum_approve'], true) ? $_POST['status'] : 'belum_approve';

    $password = (string)($_POST['password'] ?? '');
    $hapusPhoto = !empty($_POST['hapus_photo']);
    $photo = $a['photo_usaha'];

    if ($a['pic_pemilik'] === '') {
        $message = 'PIC Nama Pemilik wajib diisi.';
    } elseif ($a['nama_usaha'] === '') {
        $message = 'Nama Tempat Usaha wajib diisi.';
    } elseif ($a['no_telepon'] === '') {
        $message = 'No Telepon wajib diisi.';
    } elseif ($a['kota'] === '') {
        $message = 'Kota wajib diisi.';
    } elseif ($a['alamat'] === '') {
        $message = 'Alamat wajib diisi.';
    } elseif ($a['username'] === '' || !preg_match('/^[A-Za-z0-9._\-]{4,50}$/', $a['username'])) {
        $message = 'Username wajib diisi, minimal 4 karakter, boleh huruf, angka, titik, strip, dan garis bawah.';
    } elseif ($password !== '' && strlen($password) < 6) {
        $message = 'Password minimal 6 karakter.';
    }

    if ($message === '' && isset($_FILES['photo_usaha']) && $_FILES['photo_usaha']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['photo_usaha']['size'] > $maxSize) {
            $message = 'Ukuran foto usaha maksimal 2MB.';
        } else {
            $ext = strtolower(pathinfo($_FILES['photo_usaha']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $message = 'Format foto usaha harus JPG, PNG, WEBP, atau GIF.';
            } else {
                $filename = date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (move_uploaded_file($_FILES['photo_usaha']['tmp_name'], $uploadDir . $filename)) {
                    $photo = $filename;
                }
            }
        }
    }

    if ($message === '') {
        try {
            $check = $db->prepare('SELECT id FROM affiliate WHERE username = :username AND id != :id LIMIT 1');
            $check->execute(['username' => $a['username'], 'id' => $id]);
            if ($check->fetch()) {
                $message = 'Username "' . $a['username'] . '" sudah dipakai mitra lain.';
            }
        } catch (Exception $e) {
            $message = 'Gagal memeriksa username: ' . $e->getMessage();
        }
    }

    if ($message === '') {
        $oldPhoto = (string)($a['photo_usaha'] ?? '');
        if ($hapusPhoto) {
            $photo = null;
        }

        try {
            if ($password !== '') {
                $stmt = $db->prepare(
                    'UPDATE affiliate SET pic_pemilik = :pic_pemilik, nama_usaha = :nama_usaha, no_telepon = :no_telepon,
                     photo_usaha = :photo_usaha, kota = :kota, alamat = :alamat, username = :username,
                     password = :password, status = :status, no_rekening = :no_rekening, atas_nama = :atas_nama
                     WHERE id = :id'
                );
                $stmt->execute([
                    'pic_pemilik' => $a['pic_pemilik'],
                    'nama_usaha' => $a['nama_usaha'],
                    'no_telepon' => $a['no_telepon'],
                    'photo_usaha' => $photo,
                    'kota' => $a['kota'],
                    'alamat' => $a['alamat'],
                    'username' => $a['username'],
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'status' => $a['status'],
                    'no_rekening' => $a['no_rekening'],
                    'atas_nama' => $a['atas_nama'],
                    'id' => $id,
                ]);
            } else {
                $stmt = $db->prepare(
                    'UPDATE affiliate SET pic_pemilik = :pic_pemilik, nama_usaha = :nama_usaha, no_telepon = :no_telepon,
                     photo_usaha = :photo_usaha, kota = :kota, alamat = :alamat, username = :username,
                     status = :status, no_rekening = :no_rekening, atas_nama = :atas_nama
                     WHERE id = :id'
                );
                $stmt->execute([
                    'pic_pemilik' => $a['pic_pemilik'],
                    'nama_usaha' => $a['nama_usaha'],
                    'no_telepon' => $a['no_telepon'],
                    'photo_usaha' => $photo,
                    'kota' => $a['kota'],
                    'alamat' => $a['alamat'],
                    'username' => $a['username'],
                    'status' => $a['status'],
                    'no_rekening' => $a['no_rekening'],
                    'atas_nama' => $a['atas_nama'],
                    'id' => $id,
                ]);
            }

            if ($oldPhoto !== '' && $oldPhoto !== $photo && is_file($uploadDir . $oldPhoto)) {
                @unlink($uploadDir . $oldPhoto);
            }

            header('Location: /backend/admin/affiliate/index.php?success=1');
            exit();
        } catch (Exception $e) {
            $message = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    adminAlert('Data mitra berhasil disimpan.', 'success');
}

adminLayoutHeader(
    'Edit Mitra',
    'affiliate',
    $a['nama_usaha'],
    [['label' => 'Kembali', 'url' => '/backend/admin/affiliate/index.php', 'icon' => 'arrow_back', 'material' => true, 'class' => 'admin-btn--ghost']],
    ['bright' => true, 'css' => ['/css/affiliate-admin.css']]
);

adminAlert($message, $messageType);

$isEdit = true;
require __DIR__ . '/_form.php';

adminLayoutFooter();
