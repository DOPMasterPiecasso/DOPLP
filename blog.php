<?php
require_once __DIR__ . '/backend/config/database.php';

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$keyword = trim((string)($_GET['q'] ?? ''));

$db = getConnection();

$where = '';
$params = [];
if ($keyword !== '') {
    $where = ' WHERE judul LIKE :kw OR konten LIKE :kw OR penulis LIKE :kw';
    $params['kw'] = '%' . $keyword . '%';
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM blog$where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listStmt = $db->prepare("SELECT id, judul, slug, gambar, penulis, created_at, konten
                          FROM blog$where
                          ORDER BY created_at DESC, id DESC
                          LIMIT $perPage OFFSET $offset");
$listStmt->execute($params);
$articles = $listStmt->fetchAll();

$recentStmt = $db->prepare('SELECT id, judul, slug, created_at FROM blog ORDER BY created_at DESC, id DESC LIMIT 4');
$recentStmt->execute();
$recent = $recentStmt->fetchAll();

function blogExcerpt($html, $limit = 190) {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...');
    }
    return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

function blogPageUrl($page, $keyword) {
    $query = [];
    if ($page > 1) {
        $query['page'] = $page;
    }
    if ($keyword !== '') {
        $query['q'] = $keyword;
    }
    return $query ? '/blog?' . http_build_query($query) : '/blog';
}

$pageTitle = 'Blog & Insights | dopagency';
$description = 'Baca artikel, insight, dan pembaruan terbaru dari dopagency tentang digital marketing, web development, dan solusi digital.';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="format-detection" content="telephone=no">
  <meta name="theme-color" content="#75dab4" />
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="author" content="dopagency">
  <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="keywords" content="dopagency, blog, insight, digital marketing, web development, branding, tangerang selatan">
  <link rel="canonical" href="<?= htmlspecialchars(blogPageUrl($page, $keyword), ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name" content="dopagency">
  <meta property="og:title" content="Blog &amp; Insights | dopagency">
  <meta property="og:type" content="website">

  <link href="ico/apple-touch-icon-144-precomposed.png" rel="apple-touch-icon" sizes="144x144">
  <link href="ico/apple-touch-icon-114-precomposed.png" rel="apple-touch-icon" sizes="114x114">
  <link href="ico/apple-touch-icon-72-precomposed.png" rel="apple-touch-icon" sizes="72x72">
  <link href="ico/apple-touch-icon-57-precomposed.png" rel="apple-touch-icon">
  <link href="ico/favicon.png" rel="shortcut icon">

  <link rel="stylesheet" href="css/fontawesome.min.css">
  <link rel="stylesheet" href="css/fancybox.min.css">
  <link rel="stylesheet" href="css/hamburger.min.css">
  <link rel="stylesheet" href="css/odometer.min.css">
  <link rel="stylesheet" href="css/swiper.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . "/css/style.css") ?>">

  <script src="js/htmx.min.js"></script>
  <script>
    if (window.htmx) { htmx.config.allowScriptTags = false; }
  </script>
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
  <main>
    <!-- htmx component: sidebar -->
    <div hx-get="components/sidebar.html" hx-trigger="load" hx-swap="outerHTML"></div>
    <header class="page-header">
      <div class="video-bg">
        <video src="asset/video/video.mp4" muted loop autoplay></video>
      </div>
      <div class="inner">
        <div class="container">
          <h1>BLOG &amp; INSIGHTS</h1>
          <p>Wawasan, Tren, dan Cerita dibalik Proyek Kami</p>
        </div>
      </div>
    </header>
    <!-- end page-header -->
    <section class="blog">
      <div class="container">
        <div class="row">
          <div class="col-lg-9">

            <?php if ($keyword !== ''): ?>
              <p class="text-center" style="margin-top:30px;">Hasil pencarian untuk
                &ldquo;<strong><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></strong>&rdquo;
                &mdash; <?= $total ?> artikel ditemukan. <a href="/blog">Reset</a>
              </p>
            <?php endif; ?>

            <?php if (empty($articles)): ?>
              <div class="post">
                <div class="post-content">
                  <div class="post-title">
                    <h5>Belum ada artikel</h5>
                  </div>
                  <p>Artikel terbaru sedang dipersiapkan. Silakan kembali beberapa saat lagi.</p>
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($articles as $article): ?>
                <div class="post">
                  <div class="post-image">
                    <?php if ($article['gambar']): ?>
                      <a href="/blog/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="/uploads/blog/<?= htmlspecialchars($article['gambar'], ENT_QUOTES, 'UTF-8') ?>"
                          alt="<?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                      </a>
                    <?php endif; ?>
                  </div>
                  <div class="post-content">
                    <div class="post-date"><?= date('d F Y', strtotime($article['created_at'])) ?></div>
                    <div class="post-title">
                      <h5><a href="/blog/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>">
                          <?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?>
                        </a></h5>
                    </div>
                    <div class="post-author">
                      <span>Oleh <a href="#"><?= htmlspecialchars($article['penulis'] ?: 'dopagency', ENT_QUOTES, 'UTF-8') ?></a></span>
                    </div>
                    <p><?= htmlspecialchars(blogExcerpt($article['konten']), ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="/blog/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>" class="link">Baca Selengkapnya</a>
                  </div>
                </div>
                <!-- end post -->
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
              <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars(blogPageUrl($i, $keyword), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
              </ul>
            <?php endif; ?>

          </div>
          <!-- end col-lg-9 -->
          <div class="sidebar">
            <div class="widget">
              <div class="title">Cari Artikel</div>
              <form action="/blog" method="GET">
                <input type="text" name="q" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                  placeholder="Kata kunci...">
              </form>
            </div>
            <!-- end widget -->
            <div class="widget">
              <div class="title">Artikel Terbaru</div>
              <ul class="categories">
                <?php foreach ($recent as $item): ?>
                  <li>
                    <a href="/blog/<?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <span><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <!-- end widget -->
          </div>
          <!-- end sidebar -->
        </div>
        <!-- end row -->
      </div>
      <!-- end container -->
    </section>
    <!-- end blog -->
  </main>
  <!-- end main -->
  <!-- htmx component: footer -->
  <div hx-get="components/footer.html" hx-trigger="load" hx-swap="outerHTML"></div>

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