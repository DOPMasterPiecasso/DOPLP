<?php
// ---------------------------------------------------------------------------
// SECURITY GUARD - blokir akses publik ke file/direktori sensitif
// Berlaku untuk PHP built-in server (router ini) dan melengkapi .htaccess.
// ---------------------------------------------------------------------------
if (!function_exists('dop_deny')) {
    function dop_deny()
    {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        exit("403 Forbidden\n");
    }
}

if (PHP_SAPI !== 'cli') {
    // Security headers
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    // Jangan tampilkan error (path/kredensial) ke pengunjung
    @ini_set('display_errors', '0');

    $reqPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $reqPath = rawurldecode($reqPath);
    $reqPath = (string) preg_replace('#/{2,}#', '/', '/' . ltrim($reqPath, '/'));

    // Cegah path traversal: /../../.env, %2e%2e%2f.env, dst.
    if ($reqPath === '' || strpos($reqPath, '..') !== false) {
        dop_deny();
    }

    // .env dan semua variannya (.env.local, .env.production, .env.example, ...)
    if (preg_match('#(?:^|/)\.env(?:\.[^/]*)?$#i', $reqPath)) {
        dop_deny();
    }

    // File tersembunyi lain: /.git/*, /.htpasswd, /.user.ini, /.gitignore, dst.
    if (preg_match('#(?:^|/)\.(?!well-known(?:/|$))[^/]+$#i', $reqPath)) {
        dop_deny();
    }

    // File backup/konfigurasi/dokumen: .ini .log .bak .sql .yml .md .sh, dst.
    if (preg_match('#\.(ini|log|bak|old|orig|save|swp|sql|ya?ml|sh|md|lock|dist|patch|diff)$#i', $reqPath)) {
        dop_deny();
    }

    // Direktori internal: /backend/config, /backend/includes, /backend/scripts, /docker, /app
    if (preg_match('#^/(?:backend/(?:config|includes|scripts)|docker|app)(?:/|$)#i', $reqPath)) {
        dop_deny();
    }
}

// Simple router for clean URLs
error_log('Router called for: ' . ($_SERVER['REQUEST_URI'] ?? 'NONE'));

$request = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($request, PHP_URL_PATH);

// Remove leading/trailing slashes
$path = trim($path, '/');
error_log('Cleaned path: [' . $path . ']');

// Serve static files directly (CSS, JS, images, etc)
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|mp4|webm)$/i', $path)) {
    error_log('Static file, skipping');
    return false; // Let PHP built-in server handle it
}

// Friendly aliases for backend pages
$routes = [
    'login' => '/backend/admin/login.php',
    'logout' => '/backend/admin/logout.php',
    'admin' => '/backend/admin/dashboard.php',
    'dashboard' => '/backend/admin/dashboard.php',
    'sitemap.xml' => '/sitemap.php',
    'sitemap' => '/sitemap.php',
    'blog' => '/blog.php',
    'portfolio' => '/portfolio.php',
    'affiliate' => '/affiliate/login.php',
    'affiliate/login' => '/affiliate/login.php',
    'affiliate/register' => '/affiliate/register.php',
    'affiliate/logout' => '/affiliate/logout.php',
    'affiliate/panel' => '/affiliate/panel.php',
    'register/affiliate' => '/affiliate/register.php',
];

if (isset($routes[$path])) {
    error_log('Route match: ' . $path . ' -> ' . $routes[$path]);
    require __DIR__ . $routes[$path];
    exit;
}

// /blog/<slug> -> blog-detail.php
if (preg_match('#^blog/([A-Za-z0-9\-_]+)$#', $path, $matches)) {
    error_log('Route match: blog detail -> ' . $matches[1]);
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/blog-detail.php';
    exit;
}

// Treat /index.php as the home page
if ($path === 'index.php') {
    $path = '';
}

// If empty path, serve the dynamic home below (index.php acts as the homepage)
if (!empty($path)) {

// Try .html file
$htmlFile = __DIR__ . '/' . $path . '.html';
error_log('Trying: ' . $htmlFile);
if (file_exists($htmlFile)) {
    error_log('Found! Serving: ' . $htmlFile);
    readfile($htmlFile);
    exit;
}

    // Try exact path: let the server handle it so .php files are executed
    if (is_file(__DIR__ . '/' . $path)) {
        error_log('Found exact path, deferring to server');
        return false;
    }

    // 404
    error_log('404 Not Found');
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
// empty path: fall through and render home below
?>
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
  <title>dopagency | Solusi Digital Terpercaya untuk Bisnis Anda</title>
  <meta name="author" content="dopagency">
  <meta name="description"
    content="dopagency - solusi digital terpercaya untuk bisnis Anda: digital marketing, web development, mobile apps, desain kreatif, dan strategi bisnis.">
  <meta name="keywords"
    content="dopagency, digital agency, digital marketing, web development, mobile apps, branding, tangerang selatan">

  <!-- SOCIAL MEDIA META -->
  <meta property="og:description"
    content="dopagency - solusi digital terpercaya untuk bisnis Anda: digital marketing, web development, mobile apps, desain kreatif, dan strategi bisnis.">
  <meta property="og:site_name" content="dopagency">
  <meta property="og:title" content="dopagency | Solusi Digital Terpercaya untuk Bisnis Anda">
  <meta property="og:type" content="website">
<meta name="google-site-verification" content="6_Lkooo6wxh5gLv082LvfbkP7xVN8ZqywlcwZGurxcU" />
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
    .slider {
      position: relative;
      height: 100vh;
      overflow: hidden;
      background: #161619;
    }

    .slider .video-bg {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
      background: #fff;
    }

    .slider .video-bg video {
      min-width: 100%;
      min-height: 100%;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      object-fit: cover;
      opacity: 0.6;
    }

    .slider .video-bg::after {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(18, 19, 23, 0.45);
      z-index: 1;
    }

    .slider .gallery-top {
      position: absolute !important;
      left: 0;
      top: 0;
      width: 100% !important;
      height: 100% !important;
      background: transparent !important;
      z-index: 1;
      pointer-events: none;
    }

    .slider .gallery-top .swiper-slide {
      background: transparent !important;
    }

    .slider .gallery-top .swiper-slide:before {
      display: none;
    }

    .slider .gallery-thumbs {
      position: absolute !important;
      top: 50% !important;
      bottom: auto !important;
      left: 0;
      right: 0;
      transform: translateY(-50%) !important;
      z-index: 2;
      width: calc(100% - 240px);
      margin: 0 120px;
      height: auto;
      overflow: visible;
    }

    .slider .gallery-thumbs .swiper-wrapper {
      align-items: center;
    }

    .slider .gallery-thumbs .swiper-slide {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      opacity: 0.35;
      filter: blur(2px);
      transition: all 0.5s ease;
      cursor: pointer;
    }

    .slider .gallery-thumbs .swiper-slide span {
      display: block;
      float: none;
      margin: 0;
      transform: none;
      font-family: "Fjalla One", sans-serif;
      font-size: 3.5vw;
      line-height: 1.2;
      font-weight: 800;
      letter-spacing: 2px;
      color: #fff;
      text-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
    }

    .slider .gallery-thumbs .swiper-slide a {
      display: none;
      margin-top: 15px;
      font-size: 13px;
      color: #00ff39;
      letter-spacing: 2px;
      font-family: "Fjalla One", sans-serif;
      padding-bottom: 5px;
      position: relative;
      text-transform: uppercase;
      text-decoration: none;
    }

    .slider .gallery-thumbs .swiper-slide a:before {
      content: "";
      width: 20px;
      height: 2px;
      background: #00ff39;
      position: absolute;
      left: 0;
      bottom: 0;
      transition: 0.25s ease-in-out;
    }

    .slider .gallery-thumbs .swiper-slide a:hover:before {
      width: 100%;
    }

    .slider .gallery-thumbs .swiper-slide-active {
      opacity: 1;
      filter: blur(0px);
      transform: scale(1.15);
    }

    .slider .gallery-thumbs .swiper-slide-active span {
      font-size: 4.8vw;
      color: #ffffff;
    }

    .slider .gallery-thumbs .swiper-slide-active a {
      display: inline-block;
    }

    @media (max-width: 992px) {
      .slider .gallery-thumbs {
        width: calc(100% - 100px) !important;
        margin: 0 50px !important;
      }

      .slider .gallery-thumbs .swiper-slide span {
        font-size: 4vw;
      }

      .slider .gallery-thumbs .swiper-slide-active span {
        font-size: 6vw;
      }
    }

    @media (max-width: 768px) {
      .slider .gallery-thumbs {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 20px !important;
      }

      .slider .gallery-thumbs .swiper-slide span {
        font-size: 28px !important;
      }

      .slider .gallery-thumbs .swiper-slide-active span {
        font-size: 42px !important;
      }
    }

    .intro-badge {
      display: inline-block;
      padding: 12px 26px;
      border: 1px solid #00ff39;
      color: #00ff39;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .works-more {
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 70px 20px 30px;
    }

    .works-more a {
      display: inline-block;
      padding: 16px 44px;
      background: #00ff39;
      border: 1px solid #00ff39;
      color: #222327;
      font-family: "Fjalla One", sans-serif;
      font-size: 14px;
      letter-spacing: 2px;
      text-transform: uppercase;
      transition: .25s ease-in-out;
    }

    .works-more a:hover {
      background: #fff;
      border-color: #fff;
      color: #222327;
      text-decoration: none;
    }

    @media only screen and (max-width: 767px) {
      .works-more {
        padding: 50px 20px 20px;
      }

      .works-more a {
        padding: 14px 30px;
        font-size: 13px;
      }
    }
  </style>
</head>

<body hx-boost="true">
  <div class="preloader">
    <div class="layer"></div>
    <div class="inner">
      <figure> <img src="images/preloader.gif" alt="dopagency"> </figure>
      <span>dopagency | Digital Agency</span>
    </div>
  </div>
  <div class="page-transition">
    <div class="layer"></div>
  </div>

  <!-- htmx component: site-navigation -->
  <div hx-get="components/site-navigation.html" hx-trigger="load" hx-swap="outerHTML"></div>

  <!-- htmx component: social-media -->
  <div hx-get="components/social-media.html" hx-trigger="load" hx-swap="outerHTML"></div>

  <!-- htmx component: all-cases -->
  <div hx-get="components/all-cases.html" hx-trigger="load" hx-swap="outerHTML"></div>

  <!-- htmx component: floating-buttons -->
  <div hx-get="components/floating-buttons.html" hx-trigger="load" hx-swap="outerHTML"></div>

  <main>
    <!-- htmx component: sidebar -->
    <div hx-get="components/sidebar.html" hx-trigger="load" hx-swap="outerHTML"></div>
    <header class="slider">
      <div class="video-bg">
        <video src="asset/video/video.mp4" autoplay loop muted="muted" playsinline webkit-playsinline
          preload="auto"></video>
      </div>
      <!-- end video-bg -->
      <div class="swiper-container gallery-top">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
          </div>
          <div class="swiper-slide">
          </div>
          <div class="swiper-slide">
          </div>
        </div>
        <!-- end swiper-wrapper -->
      </div>
      <!-- end gallery-top -->
      <div class="swiper-container gallery-thumbs">
        <div class="swiper-wrapper">
          <div class="swiper-slide"><span>SOLUSI</span> <a href="/portfolio">LIHAT PORTOFOLIO</a> </div>
          <div class="swiper-slide"><span>INOVASI</span> <a href="/services">JELAJAHI LAYANAN</a> </div>
          <div class="swiper-slide"><span>KREATIF</span> <a href="/contact">HUBUNGI KAMI</a> </div>
        </div>
        <!-- end swiper-wrapper -->
      </div>
      <!-- end gallery-thumbs -->
    </header>
    <!-- end slider -->
    <section class="intro">
      <div class="container">
        <div class="row">
          <div class="col-lg-5 wow" data-splitting>
            <h3 class="section-title">SOLUSI TERINTEGRASI,<br>KREATIF &amp; BERDAMPAK</h3>
            <a href="mailto:info@dopagency.com">info@dopagency.com</a>
          </div>
          <!-- end col-5 -->
          <div class="col-lg-7 wow" data-splitting>
            <p>Kami memadukan digital marketing, desain kreatif, dan teknologi untuk menghadirkan solusi yang berdampak.</p>
            <span class="intro-badge">DIGITAL AGENCY FOR DIGITAL AGE</span>
          </div>
          <!-- end col-7 -->
        </div>
        <!-- end row -->
      </div>
      <!-- end container -->
    </section>
    <!-- end intro -->
    <section class="icon-content-block">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 wow" data-splitting>
            <h3 class="section-title">LAYANAN UNGGULAN<br>YANG KAMI TAWARKAN</h3>
          </div>
          <!-- end col-12 -->
          <div class="col-lg-6 col-md-6 wow" data-splitting>
            <div class="content-block content-block-inline">
              <figure> <img src="images/icon01.png" alt="PENGEMBANGAN WEBSITE"> </figure>
              <div class="block-body">
                <h6>PENGEMBANGAN WEBSITE</h6>
                <ul>
                  <li>Profil Perusahaan</li>
                  <li>Content Management System (CMS)</li>
                  <li>Portal E-Learning</li>
                  <li>Pengembangan Website Custom</li>
                </ul>
              </div>
              <!-- end block-body -->
            </div>
            <!-- end content-block -->
          </div>
          <!-- end col -->
          <div class="col-lg-6 col-md-6 wow" data-splitting>
            <div class="content-block content-block-inline selected">
              <figure> <img src="images/icon02.png" alt="APLIKASI &amp; SISTEM"> </figure>
              <div class="block-body">
                <h6>APLIKASI &amp; SISTEM</h6>
                <ul>
                  <li>Mobile Apps Android / iOS</li>
                  <li>Sistem Manajemen</li>
                  <li>Dashboard &amp; Tracking KPI</li>
                  <li>Integrasi SAP / ERP</li>
                </ul>
              </div>
              <!-- end block-body -->
            </div>
            <!-- end content-block -->
          </div>
          <!-- end col -->
        </div>
        <!-- end row -->
      </div>
      <!-- end container -->
    </section>
    <!-- end icon-content-block -->
<?php
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/includes/text.php';
$db = getConnection();

// Kategori untuk daftar "work-cats" diambil langsung dari database
$workCats = [];
try {
    $workCats = $db->query("SELECT id, nama, slug FROM kategori ORDER BY id ASC")->fetchAll();
} catch (Throwable $e) {
    error_log('work-cats gagal load kategori: ' . $e->getMessage());
}
// Fallback bila tabel kosong / query gagal, tampilan tetap ada
if (!$workCats) {
    $workCats = [
        ['nama' => 'Web System'],
        ['nama' => 'Landing Page'],
        ['nama' => 'Company Profile'],
    ];
}
?>
    <div class="work-divider-head">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center wow" data-splitting>
            <h3 class="section-title">KATEGORI</h3>
            <ul class="work-cats">
<?php foreach ($workCats as $cat): ?>
              <li><?= htmlspecialchars($cat['nama'], ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
            </ul>
          </div>
          <!-- end col-12 -->
        </div>
        <!-- end row -->
      </div>
      <!-- end container -->
    </div>
    <!-- end work-divider-head -->
    <section class="works">
      <ul>
<?php
$itemsAll = $db->query("SELECT p.judul, p.slug, p.deskripsi, p.teknologi, p.gambar, k.nama AS kategori FROM portfolio p LEFT JOIN kategori k ON k.id = p.kategori_id WHERE p.status = 'aktif' AND p.is_pin = 1 ORDER BY p.id ASC")->fetchAll();
// CKEditor menyimpan deskripsi dengan tag <p>, bersihkan jadi teks biasa
foreach ($itemsAll as &$_it) { $_it['deskripsi'] = plainText($_it['deskripsi']); }
unset($_it);
$items = array_slice($itemsAll, 0, 6);
foreach ($items as $item):
?>
        <li>
          <figure class="reveal-effect masker wow"> <a hx-boost="false" href="<?= htmlspecialchars($item['gambar'], ENT_QUOTES, 'UTF-8') ?>"><img src="<?= htmlspecialchars($item['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>"></a> </figure>
          <div class="caption wow" data-splitting>
            <h3><?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?></h3>
            <small><?= !empty($item['kategori']) ? htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8') . ' | ' : '' ?><?= htmlspecialchars($item['teknologi'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
          </div>
          <!-- end caption -->
        </li>
<?php endforeach; ?>
      </ul>
      <div class="works-more">
        <a href="portfolio">Lihat Portofolio Lengkap</a>
      </div>
      <!-- end works-more -->
    </section>
    <!-- end works -->
    <section class="clients">
      <div class="container">
        <div class="row">
          <div class="col-lg-5 wow" data-splitting>
            <h3 class="section-title">TEKNOLOGI TERKINI<br>UNTUK SOLUSI TERBAIK</h3>
            <a href="portfolio">Lihat Portofolio Kami</a>
          </div>
          <!-- end col-5 -->
          <div class="col-lg-7">
            <ul>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/laravel.svg" alt="Laravel" loading="lazy"> </figure> <span class="tech-name">Laravel</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/wordpress.svg" alt="WordPress" loading="lazy"> </figure> <span class="tech-name">WordPress</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/flutter.svg" alt="Flutter" loading="lazy"> </figure> <span class="tech-name">Flutter</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/figma.svg" alt="Figma" loading="lazy"> </figure> <span class="tech-name">Figma</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/postgresql.svg" alt="PostgreSQL" loading="lazy"> </figure> <span class="tech-name">PostgreSQL</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/google-analytics.svg" alt="Google Analytics" loading="lazy"> </figure> <span class="tech-name">Google Analytics</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/vuejs.svg" alt="Vue.js" loading="lazy"> </figure> <span class="tech-name">Vue.js</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/roblox.svg" alt="Roblox" loading="lazy"> </figure> <span class="tech-name">Roblox</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/godot.svg" alt="Godot" loading="lazy"> </figure> <span class="tech-name">Godot</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/mysql.svg" alt="MySQL" loading="lazy"> </figure> <span class="tech-name">MySQL</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/nodejs.svg" alt="Node.js" loading="lazy"> </figure> <span class="tech-name">Node.js</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/react.svg" alt="React" loading="lazy"> </figure> <span class="tech-name">React</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/php.svg" alt="PHP" loading="lazy"> </figure> <span class="tech-name">PHP</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/python.svg" alt="Python" loading="lazy"> </figure> <span class="tech-name">Python</span> </li>
              <li class="reveal-effect masker wow"> <figure class="tech-logo"> <img src="asset/tech/golang.svg" alt="Golang" loading="lazy"> </figure> <span class="tech-name">Golang</span> </li>
            </ul>
          </div>
          <!-- end col-7 -->
        </div>
        <!-- end row -->
      </div>
      <!-- end container -->
    </section>
    <!-- end clients -->
  </main>

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
  <script>window.PORTFOLIO_DATA = <?php $__m = []; foreach ($itemsAll as $it) { $__m[$it["slug"]] = ["kategori" => $it["kategori"], "judul" => $it["judul"], "teknologi" => $it["teknologi"], "deskripsi" => $it["deskripsi"], "gambar" => $it["gambar"]]; } echo json_encode($__m, JSON_UNESCAPED_UNICODE); ?>;</script>
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
  <script src='js/splitting.min.js'></script>
  <script src='js/odometer.min.js'></script>
  <script src='js/fancybox.min.js'></script>
  <script src="js/scripts.js"></script>

</body>

</html>