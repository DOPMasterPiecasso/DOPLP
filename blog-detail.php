<?php
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/includes/blog-helpers.php';

$slug = trim((string)($_GET['slug'] ?? ''));

// Slug harus mengikuti pola RewriteRule di .htaccess: ^blog/([A-Za-z0-9\-_]+)/?$
if ($slug === '' || !preg_match('/^[A-Za-z0-9\-_]+$/', $slug)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit();
}

$db = getConnection();

$stmt = $db->prepare('SELECT id, judul, slug, konten, gambar, penulis, created_at FROM blog WHERE slug = :slug LIMIT 1');
$stmt->execute(['slug' => $slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit();
}

// Artikel terkait untuk blok "Artikel Lainnya" di sidebar
$relatedStmt = $db->prepare('SELECT id, judul, slug, konten, gambar, penulis, created_at
                             FROM blog WHERE id != :id
                             ORDER BY created_at DESC, id DESC
                             LIMIT 3');
$relatedStmt->execute(['id' => $article['id']]);
$related = $relatedStmt->fetchAll();

$pageTitle = $article['judul'] . ' | dopagency';
$baseUrl = blogBaseUrl();
$articleUrl = $baseUrl . blogArticleUrl($article['slug']);
$description = blogExcerpt($article['konten'], 155);
$coverImage = $article['gambar'] ? $baseUrl . blogImageUrl($article['gambar']) : '';
$publishedIso = date('c', strtotime($article['created_at']));
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
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="author" content="dopagency">
  <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="canonical" href="<?= htmlspecialchars(blogArticleUrl($article['slug']), ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name" content="dopagency">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="article:published_time" content="<?= $publishedIso ?>">
  <?php if ($coverImage): ?>
    <meta property="og:image" content="<?= htmlspecialchars($coverImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= htmlspecialchars($coverImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php else: ?>
    <meta name="twitter:card" content="summary">
  <?php endif; ?>

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
    <!-- end inner -->
  </div>
  <!-- end preloader -->
  <div class="page-transition">
    <div class="layer"></div>
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
          <h1>BLOG &amp; INSIGHTS</h1>
          <p class="page-header-title"><?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <!-- end container -->
      </div>
      <!-- end inner -->
    </header>
    <!-- end page-header -->
    <section class="blog">
      <div class="container">
        <div class="row">
          <div class="col-lg-9">
            <article class="post single">
              <?php if ($article['gambar']): ?>
                <figure class="post-image">
                  <img src="<?= htmlspecialchars(blogImageUrl($article['gambar']), ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?>">
                </figure>
              <?php endif; ?>

              <div class="post-content">
                <div class="post-date"><?= blogTanggal($article['created_at']) ?></div>

                <div class="post-title">
                  <h2><?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>

                <div class="post-author">
                  <span>Oleh <a href="/blog"><?= htmlspecialchars($article['penulis'] ?: 'dopagency', ENT_QUOTES, 'UTF-8') ?></a></span>
                  <span class="post-meta"><?= htmlspecialchars(blogWaktuBaca($article['konten']), ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <?php if (trim($article['konten']) !== ''): ?>
                  <div class="post-body">
                    <?= $article['konten'] ?>
                  </div>
                  <!-- end post-body -->
                <?php endif; ?>

                <ul class="social-share">
                  <li class="facebook">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($articleUrl) ?>"
                      target="_blank" rel="noopener" aria-label="Bagikan ke Facebook"><i class="fab fa-facebook-f"></i></a>
                  </li>
                  <li class="twitter">
                    <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($articleUrl) ?>&text=<?= rawurlencode($article['judul']) ?>"
                      target="_blank" rel="noopener" aria-label="Bagikan ke Twitter"><i class="fab fa-twitter"></i></a>
                  </li>
                  <li class="linkedin">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($articleUrl) ?>"
                      target="_blank" rel="noopener" aria-label="Bagikan ke LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                  </li>
                  <li class="whatsapp">
                    <a href="https://wa.me/?text=<?= rawurlencode($article['judul'] . ' ' . $articleUrl) ?>"
                      target="_blank" rel="noopener" aria-label="Bagikan ke WhatsApp"><i class="fab fa-whatsapp"></i></a>
                  </li>
                </ul>
                <!-- end social-share -->

                <a href="/blog" class="link post-back">&larr; Kembali ke Blog</a>
              </div>
              <!-- end post-content -->
            </article>
            <!-- end post single -->

          </div>
          <!-- end col-lg-9 -->
          <aside class="sidebar">
            <div class="widget">
              <div class="title">Cari Artikel</div>
              <form action="/blog" method="GET">
                <input type="text" name="q" placeholder="Kata kunci..." aria-label="Kata kunci pencarian">
              </form>
            </div>
            <!-- end widget -->
            <?php if (!empty($related)): ?>
              <div class="widget post-related">
                <div class="title">Artikel Lainnya</div>
                <ul>
                  <?php foreach ($related as $item): ?>
                    <li>
                      <?php if ($item['gambar']): ?>
                        <a class="post-image" href="<?= htmlspecialchars(blogArticleUrl($item['slug']), ENT_QUOTES, 'UTF-8') ?>">
                          <img src="<?= htmlspecialchars(blogImageUrl($item['gambar']), ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                        </a>
                      <?php endif; ?>
                      <div class="post-content<?= $item['gambar'] ? '' : ' full' ?>">
                        <div class="post-date"><?= blogTanggal($item['created_at'], 'pendek') ?></div>
                        <div class="post-title">
                          <h5><a href="<?= htmlspecialchars(blogArticleUrl($item['slug']), ENT_QUOTES, 'UTF-8') ?>">
                              <?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>
                            </a></h5>
                        </div>
                        <p><?= htmlspecialchars(blogExcerpt($item['konten'], 90), ENT_QUOTES, 'UTF-8') ?></p>
                      </div>
                      <!-- end post-content -->
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <!-- end post-related -->
            <?php endif; ?>
          </aside>
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