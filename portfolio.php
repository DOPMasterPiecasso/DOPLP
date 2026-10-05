<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="format-detection" content="telephone=no">
  <meta name="theme-color" content="#00ff39" />
  <!-- htmx component: google-site-verification -->
  <div hx-get="components/google-site-verification.html" hx-trigger="load" hx-target="head" hx-swap="beforeend"></div>
  <title>Portfolio Kami | dopagency</title>
  <meta name="author" content="dopagency">
  <meta name="description"
    content="Portofolio dopagency: 23 proyek unggulan web development, mobile app, e-commerce, branding, enterprise system, dan lainnya.">
  <meta name="keywords"
    content="dopagency, digital agency, digital marketing, web development, mobile apps, branding, tangerang selatan">

  <!-- SOCIAL MEDIA META -->
  <meta property="og:description"
    content="Portofolio dopagency: 23 proyek unggulan web development, mobile app, e-commerce, branding, enterprise system, dan lainnya.">
  <meta property="og:site_name" content="dopagency">
  <meta property="og:title" content="Portfolio Kami | dopagency">
  <meta property="og:type" content="website">

  <!-- FAVICON FILES -->
  <link href="ico/apple-touch-icon-144-precomposed.png" rel="apple-touch-icon" sizes="144x144">
  <link href="ico/apple-touch-icon-114-precomposed.png" rel="apple-touch-icon" sizes="114x114">
  <link href="ico/apple-touch-icon-72-precomposed.png" rel="apple-touch-icon" sizes="72x72">
  <link href="ico/apple-touch-icon-57-precomposed.png" rel="apple-touch-icon">
  <link href="ico/favicon.png" rel="shortcut icon">

  <!-- CSS FILES -->
  <link rel="stylesheet" href="css/fontawesome.min.css">
  <link rel="stylesheet" href="css/fancybox.min.css">
  <link rel="stylesheet" href="css/hamburger.min.css">
  <link rel="stylesheet" href="css/odometer.min.css">
  <link rel="stylesheet" href="css/swiper.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">

  <!-- htmx: SPA page navigation (hx-boost) -->
  <script src="js/htmx.min.js"></script>
  <script>
    // Swapped fragments already reuse the globally loaded libraries,
    // so htmx must not re-execute <script> tags found in them.
    if (window.htmx) { htmx.config.allowScriptTags = false; }
  </script>
  <style>
  </style>
</head>

<body hx-boost="true">
  <div class="preloader">
    <div class="layer"></div>
    <!-- end layer -->
    <div class="inner">
      <figure> <img src="images/preloader.gif" alt="dopagency"> </figure>
      <span>dopagency | Digital Agency</span>
    </div>
    <!-- end inner -->
  </div>
  <!-- end preloader -->
  <div class="page-transition">
    <div class="layer"></div>
    <!-- end layer -->
  </div>
  <!-- end page-transition -->
  <!-- htmx component: site-navigation -->
  <div hx-get="components/site-navigation.html" hx-trigger="load" hx-swap="outerHTML"></div>
  <!-- htmx component: social-media -->
  <div hx-get="components/social-media.html" hx-trigger="load" hx-swap="outerHTML"></div>
  <!-- htmx component: all-cases -->
  <div hx-get="components/all-cases.html" hx-trigger="load" hx-swap="outerHTML"></div>
  <main>
    <!-- htmx component: sidebar -->
    <div hx-get="components/sidebar.html" hx-trigger="load" hx-swap="outerHTML"></div>
    <header class="page-header">
      <div class="video-bg">
        <video src="asset/video/video.mp4" muted loop autoplay></video>
      </div>
      <!-- end video-bg -->
      <div class="inner">
        <div class="container">
          <h1>PORTFOLIO KAMI</h1>
          <p>Karya &amp; Proyek Unggulan</p>
        </div>
        <!-- end container -->
      </div>
      <!-- end inner -->
    </header>
    <!-- end page-header -->
<?php
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/includes/text.php';
$db = getConnection();

// Tombol filter diambil dari tabel kategori (hanya kategori yang dipakai portfolio aktif)
$filters = [];
try {
    $filters = $db->query("SELECT DISTINCT k.id, k.nama FROM kategori k INNER JOIN portfolio p ON p.kategori_id = k.id WHERE p.status = 'aktif' ORDER BY k.id ASC")->fetchAll();
} catch (Throwable $e) {
    error_log('portfolio filter gagal load kategori: ' . $e->getMessage());
}
// Fallback bila query gagal / belum ada data
if (!$filters) {
    $filters = [
        ['nama' => 'Web Development'],
        ['nama' => 'Mobile App'],
        ['nama' => 'E-Commerce'],
        ['nama' => 'Branding'],
        ['nama' => 'Enterprise System'],
        ['nama' => 'Lifestyle'],
    ];
}
?>
    <section class="portfolio-filter">
      <div class="container">
        <button type="button" class="active" data-filter="Semua">Semua</button>
<?php foreach ($filters as $f): ?>
        <button type="button" data-filter="<?= htmlspecialchars($f['nama'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($f['nama'], ENT_QUOTES, 'UTF-8') ?></button>
<?php endforeach; ?>
      </div>
      <!-- end container -->
    </section>
    <!-- end portfolio-filter -->
    <section class="works portfolio-works">
      <ul>
<?php
$items = $db->query("SELECT p.judul, p.slug, p.deskripsi, p.teknologi, p.gambar, k.nama AS kategori FROM portfolio p LEFT JOIN kategori k ON k.id = p.kategori_id WHERE p.status = 'aktif' ORDER BY p.id ASC")->fetchAll();
// CKEditor menyimpan deskripsi dengan tag <p>, bersihkan jadi teks biasa
foreach ($items as &$_it) { $_it['deskripsi'] = plainText($_it['deskripsi']); }
unset($_it);
foreach ($items as $item):
?>
        <li id="<?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>" data-category="<?= htmlspecialchars($item['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <figure class="reveal-effect masker wow"> <a hx-boost="false" href="<?= htmlspecialchars($item['gambar'], ENT_QUOTES, 'UTF-8') ?>"><img
                src="<?= htmlspecialchars($item['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>"></a> </figure>
          <div class="caption wow" data-splitting>
            <span class="cat-badge"><?= htmlspecialchars($item['kategori'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <h3><?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?></h3>
            <small><?= htmlspecialchars($item['teknologi'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
            <p><?= htmlspecialchars($item['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <!-- end caption -->
        </li>
<?php endforeach; ?>
      </ul>
    </section>
    <!-- end portfolio -->
  </main>
  <!-- end main -->
  <!-- htmx component: footer -->
  <div hx-get="components/footer.html" hx-trigger="load" hx-swap="outerHTML"></div>

  <!-- JS FILES -->
  
    <!-- portfolio detail modal -->
  <div class="modal fade" id="portfolioModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-fullscreen" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="portfolioModalTitle"></h5>
          <button type="button" class="portfolio-modal-close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="portfolio-modal-media">
            <img src="" id="portfolioModalImg" alt="">
          </div>
          <div class="portfolio-modal-meta">
            <span class="cat-badge" id="portfolioModalKategori"></span>
            <small id="portfolioModalTeknologi"></small>
            <p id="portfolioModalDeskripsi"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>window.PORTFOLIO_DATA = <?php $__m = []; foreach ($items as $it) { $__m[$it["slug"]] = ["kategori" => $it["kategori"], "judul" => $it["judul"], "teknologi" => $it["teknologi"], "deskripsi" => $it["deskripsi"], "gambar" => $it["gambar"]]; } echo json_encode($__m, JSON_UNESCAPED_UNICODE); ?>;</script>
  <script>
    // Buang tag HTML & decode entity: deskripsi/kategori datang dari CKEditor
    function portfolioToText(value) {
      if (value === null || value === undefined) return '';
      var text = String(value).replace(/<[^>]*>/g, ' ');
      var ta = document.createElement('textarea');
      ta.innerHTML = text;
      return ta.value.replace(/\s+/g, ' ').trim();
    }
    document.addEventListener('click', function (e) {
      var a = e.target.closest('.works li figure a, .portfolio-works li figure a');
      if (!a) return;
      e.preventDefault();
      var byImg = {};
      Object.keys(window.PORTFOLIO_DATA).forEach(function (k) {
        byImg[window.PORTFOLIO_DATA[k].gambar.split('/').pop()] = window.PORTFOLIO_DATA[k];
      });
      var img = a.querySelector('img');
      var src = img ? img.getAttribute('src') : a.getAttribute('href');
      var item = byImg[src.split('/').pop()];
      if (!item) return;
      document.getElementById('portfolioModalTitle').textContent = portfolioToText(item.judul);
      var kategoriEl = document.getElementById('portfolioModalKategori');
      kategoriEl.textContent = portfolioToText(item.kategori);
      kategoriEl.style.display = kategoriEl.textContent === '' ? 'none' : '';
      document.getElementById('portfolioModalTeknologi').textContent = portfolioToText(item.teknologi);
      document.getElementById('portfolioModalDeskripsi').textContent = portfolioToText(item.deskripsi);
      var mImg = document.getElementById('portfolioModalImg');
      mImg.src = item.gambar;
      mImg.alt = item.judul;
      $('#portfolioModal').modal('show');
    });
  </script>

  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/swiper.min.js"></script>
  <script src="js/wow.min.js"></script>
  <script src="js/splitting.min.js"></script>
  <script src="js/odometer.min.js"></script>
  <script src="js/fancybox.min.js"></script>
  <script src="js/scripts.js"></script>

</body>

</html>