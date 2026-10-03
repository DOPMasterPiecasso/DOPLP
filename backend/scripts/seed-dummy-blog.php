<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya dapat dijalankan melalui command line.');
}

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../config/database.php';

$clear = in_array('--clear', $argv, true);

try {
    $pdo = getConnection();
} catch (Throwable $e) {
    fwrite(STDERR, "[GAGAL] Koneksi database gagal: " . $e->getMessage() . "\n");
    exit(1);
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/blog';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Mode kosongkan: hapus artikel dummy + gambarnya
if ($clear) {
    $pdo->exec("DELETE FROM blog");
    foreach (glob($uploadDir . '/dummy-*.jpg') ?: [] as $file) {
        @unlink($file);
    }
    echo "[OK] Semua artikel dummy dihapus.\n";
    exit(0);
}

$dummyArticles = [
    [
        'judul' => '5 Tips Memilih Hosting untuk Website Bisnis',
        'slug' => 'tips-memilih-hosting-website-bisnis',
        'penulis' => 'Tim dopagency',
        'gambar' => 'dummy-hosting.jpg',
        'sumber' => 'asset/projek/erpagency.png',
        'created_at' => '2026-09-28 09:15:00',
        'konten' => '<p>Hosting adalah tulang punggung website bisnis Anda. Salah memilih, waktu muat
            halaman bisa melambat drastis dan pengunjung akan pergi sebelum halaman selesai tampil.</p>
            <h3>1. Perhatikan lokasi server</h3>
            <p>Sebagian besar pengunjung Indonesia berada di Asia Tenggara. Server di Singapura atau
                Jakarta umumnya memberi latensi lebih rendah dibandingkan server di Eropa atau Amerika.</p>
            <h3>2. Jangan hanya melihat harga</h3>
            <p>Hosting termurah sering punya batas memori dan CPU yang ketat. Untuk website yang ramai,
                alokasi sumber daya jauh lebih penting daripada selisih harga yang kecil.</p>
            <h3>3. Cek dukungan HTTP/2 dan HTTPS</h3>
            <p>Semua hosting modern sudah mendukung HTTPS, namun tidak semuanya mengaktifkan HTTP/2 yang
                membuat halaman terasa jauh lebih cepat.</p>',
    ],
    [
        'judul' => 'Meningkatkan Kecepatan Website dengan Caching',
        'slug' => 'meningkatkan-kecepatan-website-caching',
        'penulis' => 'Rina Prasetyo',
        'gambar' => 'dummy-caching.jpg',
        'sumber' => 'asset/projek/binary.jpg',
        'created_at' => '2026-09-21 14:40:00',
        'konten' => '<p>Caching adalah cara paling efektif menurunkan waktu muat halaman tanpa menambah
            biaya server. Pada dasarnya caching menyimpan hasil pemrosesan agar tidak dihitung ulang.</p>
            <h3>Browser cache</h3>
            <p>File statis seperti gambar, CSS, dan JavaScript bisa disimpan di browser pengunjung.
                Tambahkan header <strong>Cache-Control</strong> yang tepat agar aset ini tidak diunduh
                ulang setiap kali halaman dibuka.</p>
            <h3>Server cache</h3>
            <p>Untuk halaman yang isinya jarang berubah, simpan hasil query database di memori server.
                Redux atau APCu bisa dipakai pada PHP.</p>
            <h3>Content Delivery Network</h3>
            <p>CDN/mmad distributing file dari server terdekat dengan pengunjung, sehingga waktu tempuh
                jauh lebih singkat.</p>',
    ],
    [
        'judul' => 'Strategi Konten yang Relevan untuk Bisnis',
        'slug' => 'strategi-konten-relevan-untuk-bisnis',
        'penulis' => 'Tim dopagency',
        'gambar' => 'dummy-konten.jpg',
        'sumber' => 'asset/projek/pos1.png',
        'created_at' => '2026-09-12 10:05:00',
        'konten' => '<p>Konten berkualitas bukan soal banyaknya artikel yang terbit, melainkan seberapa
            relevan isinya dengan kebutuhan pembaca.</p>
            <h3>Mulai dari pertanyaan pelanggan</h3>
            <p>Catat pertanyaan yang sering muncul lewat chat WhatsApp, email, atau kolom komentar.
                Pertanyaan itu adalah ide artikel paling Gratis yang Anda punya.</p>
            <h3>Satu artikel, satu tujuan</h3>
            <p>Usahakan setiap artikel menyelesaikan satu masalah dengan tuntas. Artikel yang terlalu
                luas biasanya sulit dibaca sampai habis.</p>',
    ],
    [
        'judul' => 'Memahami Funnel Marketing untuk Produk Digital',
        'slug' => 'memahami-funnel-marketing-produk-digital',
        'penulis' => 'Andi Saputra',
        'gambar' => 'dummy-funnel.jpg',
        'sumber' => 'asset/projek/jamsembilan.jpg',
        'created_at' => '2026-09-05 16:20:00',
        'konten' => '<p>Funnel marketing memetakan perjalanan pelanggan dari belum mengenal brand sampai
            menjadi pembeli. Memahaminya membantu Anda tahu di mana kebocoran terjadi.</p>
            <h3>Awareness</h3>
            <p>Tahap pertama adalah dikenal. Konten edukatif dan media sosial biasanya jadi pintu masuk.</p>
            <h3>Consideration</h3>
            <p>Di sini calon pembeli membandingkan pilihan. Studi kasus, demo produk, dan ulasan pelanggan
                sangat berpengaruh.</p>
            <h3>Conversion</h3>
            <p>Tahap akhir butuh dorongan jelas: tombol aksi yang menonjol, harga yang transparan, dan
                proses checkout yang ringkas.</p>',
    ],
    [
        'judul' => 'Design System untuk Tim Produk Kecil',
        'slug' => 'design-system-untuk-tim-produk-kecil',
        'penulis' => 'Tim dopagency',
        'gambar' => 'dummy-design-system.jpg',
        'sumber' => 'asset/projek/eyearbook.png',
        'created_at' => '2026-08-28 11:30:00',
        'konten' => '<p>Design system bukan privilege perusahaan besar. collided Untuk tim kecil, satu
            file berisi warna, font, dan komponen dasar sudah cukup untuk membuat tampilan konsisten.</p>
            <h3>Mulai dari yang paling sering dipakai</h3>
            <p>Tombol, form, dan kartu adalah komponen yang paling sering muncul. Selesaikan ketiganya
                dulu sebelum menambah komponen lain.</p>',
    ],
    [
        'judul' => 'Kenapa Sertifikat SSL Penting untuk Toko Online',
        'slug' => 'kenapa-sertifikat-ssl-penting-untuk-toko-online',
        'penulis' => 'Rina Prasetyo',
        'gambar' => 'dummy-ssl.jpg',
        'sumber' => 'asset/projek/garansi.png',
        'created_at' => '2026-08-20 08:50:00',
        'konten' => '<p>Browser modern menandai situs tanpa SSL dengan peringatan "Not Secure". Pengunjung
                yang melihat peringatan ini cenderung membatalkan transaksi.</p>
            <h3>Dampak utama</h3>
            <ul>
                <li>Menurunkan tingkat konversi karena pengunjung ragu memasukkan data kartu.</li>
                <li>Menurunkan peringkat pencarian karena Google lebih memprioritaskan situs aman.</li>
                <li>Data formulir tidak terenkripsi saat dikirim.</li>
            </ul>
            <p>Sekarang sertifikat SSL tersedia gratis dan bisa diaktifkan dalam beberapa menit, jadi
                tidak ada alasan menundanya.</p>',
    ],
    [
        'judul' => 'Mengoptimalkan Gambar agar Website Lebih Ringan',
        'slug' => 'mengoptimalkan-gambar-agar-website-lebih-ringan',
        'penulis' => 'Tim dopagency',
        'gambar' => 'dummy-gambar.jpg',
        'sumber' => 'asset/projek/zencave.jpg',
        'created_at' => '2026-08-13 13:10:00',
        'konten' => '<p>Gambar sering kali mengambil sebagian besar bobot halaman.+{\n            Mengoptimalkannya memberi dampak besar dengan usaha yang relatif kecil.</p>
            <h3>Gunakan format modern</h3>
            <p>Format WebP dan AVIF bisa memangkas ukuran file 30 sampai 70 persen dibanding JPEG dengan
                kualitas tampilan yang hampir sama.</p>
            <h3>Sesuaikan ukuran dengan tampilan</h3>
            <p>Jangan menampilkan gambar 2000px di kolom yang hanya 600px. Atur <em>width</em> dan
                <em>height</em> agar browser tahu ukuran akhir dan tidak terjadi layout shift.</p>',
    ],
    [
        'judul' => 'Checklist SEO Teknis untuk Pemula',
        'slug' => 'checklist-seo-teknis-untuk-pemula',
        'penulis' => 'Andi Saputra',
        'gambar' => 'dummy-seo.jpg',
        'sumber' => 'asset/projek/fluxury.png',
        'created_at' => '2026-08-06 09:00:00',
        'konten' => '<p>Fondasi teknis SEO tidak serumit yang dibayangkan. Pastikan poin berikut sudah benar
                sebelum mulai menulis artikel sebanyak apa pun.</p>
            <h3>Yang perlu dicek</h3>
            <ul>
                <li>Setiap halaman punya tag title dan meta description yang unik.</li>
                <li>Website bisa dibuka lewat HTTPS tanpa peringatan.</li>
                <li>Struktur URL sederhana dan deskriptif, tanpa huruf besar atau spasi.</li>
                <li>Sitemap tersedia dandaftarkan di Google Search Console.</li>
                <li>Gambar memiliki atribut alt yang relevan.</li>
            </ul>',
    ],
];

$stmt = $pdo->prepare(
    'INSERT INTO blog (judul, slug, konten, gambar, penulis, created_at)
     VALUES (:judul, :slug, :konten, :gambar, :penulis, :created_at)
     ON DUPLICATE KEY UPDATE
        judul = VALUES(judul),
        konten = VALUES(konten),
        gambar = VALUES(gambar),
        penulis = VALUES(penulis),
        created_at = VALUES(created_at)'
);

$rootDir = dirname(__DIR__, 2);
$tersalin = 0;

foreach ($dummyArticles as $article) {
    $target = $uploadDir . '/' . $article['gambar'];
    $source = $rootDir . '/' . $article['sumber'];

    if (!is_file($target) && is_file($source)) {
        copy($source, $target);
        $tersalin++;
    }

    $stmt->execute([
        'judul' => $article['judul'],
        'slug' => $article['slug'],
        'konten' => $article['konten'],
        'gambar' => $article['gambar'],
        'penulis' => $article['penulis'],
        'created_at' => $article['created_at'],
    ]);
}

$total = (int)$pdo->query('SELECT COUNT(*) FROM blog')->fetchColumn();

echo "[OK] Artikel dummy siap.\n";
echo "     - Gambar cover disalin: {$tersalin}\n";
echo "     - Total artikel di database: {$total}\n";
echo "     - Buka: /blog\n";
echo "     - Hapus semua: php backend/scripts/seed-dummy-blog.php --clear\n";
