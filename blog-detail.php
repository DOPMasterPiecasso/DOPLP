<?php
require_once __DIR__ . '/backend/config/database.php';

$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
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

$relatedStmt = $db->prepare('SELECT judul, slug, created_at FROM blog WHERE id != :id ORDER BY created_at DESC, id DESC LIMIT 3');
$relatedStmt->execute(['id' => $article['id']]);
$related = $relatedStmt->fetchAll();

$pageTitle = $article['judul'] . ' | dopagency';
$baseUrl = rtrim((string)(getenv('SITE_URL') ?: 'https://dopagency.my.id'), '/');
$articleUrl = $baseUrl . '/blog/' . $article['slug'];
$description = trim(preg_replace('/\s+/', ' ', strip_tags($article['konten'])));
$description = function_exists('mb_strimwidth')
    ? mb_strimwidth($description, 0, 155, '...')
    : substr($description, 0, 155);
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="format-detection" content="telephone=no">
  <meta name="theme-color" content="#00ff39" />
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="author" content="dopagency">
  <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="canonical" href="/blog/<?= htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8') ?>">

  <meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:site_name" content="dopagency">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="article:published_time" content="<?= date('c', strtotime($article['created_at'])) ?>">
  <?php if ($article['gambar']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($baseUrl . '/uploads/blog/' . $article['gambar'], ENT_QUOTES, 'UTF-8') ?>">
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
    <section class="blog">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="post single">
              <?php if ($article['gambar']): ?>
                <div class="post-image">
                  <img src="/uploads/blog/<?= htmlspecialchars($article['gambar'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
              <?php endif; ?>

              <div class="post-content">
                <div class="post-date"><?= date('d F Y', strtotime($article['created_at'])) ?></div>

                <div class="post-title">
                  <h2><?= htmlspecialchars($article['judul'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>

                <div class="post-author">
                  <span>Oleh <a href="#"><?= htmlspecialchars($article['penulis'] ?: 'dopagency', ENT_QUOTES, 'UTF-8') ?></a></span>
                </div>

                <?php if (trim($article['konten']) !== ''): ?>
                  <?= $article['konten'] ?>
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

                <a href="/blog" class="link">&larr; Kembali ke Blog</a>
              </div>
              <!-- end post-content -->
            </div>
            <!-- end post single -->

            <?php if (!empty($related)): ?>
              <div class="post">
                <div class="post-content">
                  <div class="post-title">
                    <h5>Artikel Lainnya</h5>
                  </div>
                  <ul class="post-categories">
                    <?php foreach ($related as $item): ?>
                      <li>
                        <a href="/blog/<?= htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8') ?>">
                          <?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endif; ?>

          </div>
          <!-- end col-lg-12 -->
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