<?php
// Partial formulir mitra affiliate. Butuh $a (data) dan $isEdit (bool).
$g = function ($key) use ($a) {
    return (string)($a[$key] ?? '');
};
?>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="panel">
        <div class="panel__head">
            <h3><i class="material-icons mi-lg">storefront</i> Data Tempat Usaha</h3>
        </div>
        <div class="panel__body">
            <div class="form-grid">
                <div class="field">
                    <label for="pic_pemilik"><i class="material-icons">badge</i> PIC Nama Pemilik</label>
                    <input type="text" id="pic_pemilik" name="pic_pemilik" value="<?= htmlspecialchars($g('pic_pemilik'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="field">
                    <label for="nama_usaha"><i class="material-icons">storefront</i> Nama Tempat Usaha</label>
                    <input type="text" id="nama_usaha" name="nama_usaha" value="<?= htmlspecialchars($g('nama_usaha'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Toko Maju Jaya" required>
                </div>

                <div class="field">
                    <label for="no_telepon"><i class="material-icons">phone</i> No Telepon</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($g('no_telepon'), ENT_QUOTES, 'UTF-8') ?>" placeholder="0812xxxxxxx" required>
                </div>

                <div class="field">
                    <label for="kota"><i class="material-icons">location_city</i> Kota</label>
                    <input type="text" id="kota" name="kota" value="<?= htmlspecialchars($g('kota'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Tangerang Selatan" required>
                </div>

                <div class="field field--full">
                    <label for="alamat"><i class="material-icons">map</i> Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Nama jalan, nomor, kelurahan, kecamatan" required><?= htmlspecialchars($g('alamat'), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field field--full">
                    <label for="photo_usaha"><i class="material-icons">photo_camera</i> Photo Usaha</label>
                    <input type="file" id="photo_usaha" name="photo_usaha" accept="image/*">
                    <?php if (!empty($a['photo_usaha'])): ?>
                        <img class="image-preview" src="/uploads/affiliate/<?= htmlspecialchars($a['photo_usaha'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto usaha">
                        <label class="field__hint"><input type="checkbox" name="hapus_photo" value="1"> Hapus foto saat ini</label>
                    <?php endif; ?>
                    <div class="field__hint">JPG, PNG, WEBP, atau GIF. Maksimal 2MB.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h3><i class="material-icons mi-lg">account_circle</i> Akun Login &amp; Status</h3>
        </div>
        <div class="panel__body">
            <div class="form-grid">
                <div class="field">
                    <label for="username"><i class="material-icons">alternate_email</i> Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($g('username'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: budi.maju" required>
                </div>

                <div class="field">
                    <label for="password"><i class="material-icons">lock</i> Password</label>
                    <input type="text" id="password" name="password" placeholder="<?= $isEdit ? 'Kosongkan jika tidak diganti' : 'Minimal 6 karakter' ?>" <?= $isEdit ? '' : 'required' ?>>
                    <div class="field__hint"><?= $isEdit ? 'Isi hanya jika ingin mengganti password mitra.' : 'Password dienkripsi otomatis oleh sistem.' ?></div>
                </div>

                <div class="field">
                    <label for="status"><i class="material-icons">verified_user</i> Status</label>
                    <select id="status" name="status" required>
                        <option value="approve" <?= $g('status') === 'approve' ? 'selected' : '' ?>>Approve</option>
                        <option value="belum_approve" <?= $g('status') !== 'approve' ? 'selected' : '' ?>>Belum Approve</option>
                    </select>
                    <div class="field__hint">Hanya mitra berstatus Approve yang bisa masuk ke akunnya.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <h3><i class="material-icons mi-lg">account_balance</i> Data Rekening (Rembursmen)</h3>
        </div>
        <div class="panel__body">
            <div class="form-grid">
                <div class="field">
                    <label for="no_rekening"><i class="material-icons">account_balance</i> No Rekening</label>
                    <input type="text" id="no_rekening" name="no_rekening" value="<?= htmlspecialchars($g('no_rekening'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 1234567890">
                </div>

                <div class="field">
                    <label for="atas_nama"><i class="material-icons">credit_card</i> Atas Nama</label>
                    <input type="text" id="atas_nama" name="atas_nama" value="<?= htmlspecialchars($g('atas_nama'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Nama sesuai buku rekening">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn--success"><i class="material-icons">save</i> Simpan Data Mitra</button>
                <a class="admin-btn admin-btn--ghost" href="/backend/admin/affiliate/index.php"><i class="material-icons">arrow_back</i> Kembali</a>
            </div>
        </div>
    </div>
</form>
