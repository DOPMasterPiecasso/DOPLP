<?php
// Helper bersama untuk halaman blog (daftar artikel & detail artikel).
// Dipakai oleh blog.php dan blog-detail.php agar format tampilan konsisten.

if (!function_exists('blogBaseUrl')) {
    function blogBaseUrl() {
        return rtrim((string)(getenv('SITE_URL') ?: 'https://dopagency.my.id'), '/');
    }
}

if (!function_exists('blogArticleUrl')) {
    function blogArticleUrl($slug) {
        return '/blog/' . rawurlencode((string)$slug);
    }
}

if (!function_exists('blogImageUrl')) {
    function blogImageUrl($gambar) {
        return '/uploads/blog/' . rawurlencode((string)$gambar);
    }
}

if (!function_exists('blogTanggal')) {
    function blogTanggal($datetime, $format = 'panjang') {
        $timestamp = strtotime((string)$datetime);
        if (!$timestamp) {
            return '';
        }

        $bulan = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        );

        $tanggal = (int)date('j', $timestamp);
        $namaBulan = $bulan[(int)date('n', $timestamp)];

        if ($format === 'pendek') {
            return $tanggal . ' ' . substr($namaBulan, 0, 3) . ' ' . date('Y', $timestamp);
        }

        return $tanggal . ' ' . $namaBulan . ' ' . date('Y', $timestamp);
    }
}

if (!function_exists('blogWaktuBaca')) {
    function blogWaktuBaca($html) {
        $text = trim(strip_tags((string)$html));
        $jumlahKata = $text === '' ? 0 : count(preg_split('/\s+/u', $text));
        $menit = max(1, (int)ceil($jumlahKata / 200));

        return $menit . ' menit baca';
    }
}

if (!function_exists('blogExcerpt')) {
    function blogExcerpt($html, $limit = 190) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$html)));
        if ($text === '') {
            return '';
        }
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $limit, '...');
        }
        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }
}

if (!function_exists('blogPageUrl')) {
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
}