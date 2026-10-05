<?php
require_once __DIR__ . '/backend/includes/env.php';

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = rtrim((string)(getenv('SITE_URL') ?: 'https://dopagency.my.id'), '/');

$pages = [
    ['loc' => '/', 'file' => 'index.php', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/about', 'file' => 'about.html', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => '/services', 'file' => 'services.html', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => '/portfolio', 'file' => 'portfolio.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/produk', 'file' => 'produk.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/showcases', 'file' => 'showcases.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/studio', 'file' => 'studio.html', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/contact', 'file' => 'contact.html', 'priority' => '0.8', 'changefreq' => 'yearly'],
];

$urls = [];

foreach ($pages as $page) {
    $filePath = __DIR__ . '/' . $page['file'];

    if (!is_file($filePath)) {
        continue;
    }

    $urls[] = [
        'loc' => $baseUrl . $page['loc'],
        'lastmod' => date('Y-m-d', filemtime($filePath)),
        'changefreq' => $page['changefreq'],
        'priority' => $page['priority'],
    ];
}

// Halaman daftar blog + setiap artikel yang sudah dipublikasikan
if (is_file(__DIR__ . '/blog.php')) {
    $urls[] = [
        'loc' => $baseUrl . '/blog',
        'lastmod' => date('Y-m-d', filemtime(__DIR__ . '/blog.php')),
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ];

    try {
        $blogPdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: 'localhost',
                getenv('DB_PORT') ?: '3306',
                getenv('DB_NAME') ?: 'dop_db'
            ),
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASSWORD') ?: '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $blogStmt = $blogPdo->query('SELECT slug, created_at FROM blog ORDER BY created_at DESC, id DESC');
        foreach ($blogStmt as $article) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $article['slug'],
                'lastmod' => date('Y-m-d', strtotime($article['created_at'])),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }
    } catch (Throwable $e) {
        // Blog tidak bisa dibaca: tetap kirim sitemap halaman statis
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= $url['lastmod'] ?></lastmod>
        <changefreq><?= $url['changefreq'] ?></changefreq>
        <priority><?= $url['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>