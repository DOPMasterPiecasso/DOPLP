<?php
/**
 * Helper teks biasa untuk konten yang dihasilkan editor rich-text (CKEditor).
 *
 * CKEditor menyimpan paragraf sebagai <p>...</p>, sedangkan field seperti
 * deskripsi portofolio dirender sebagai teks biasa (kartu portfolio dan
 * modal detail memakai htmlspecialchars / textContent). Tanpa dibersihkan,
 * tag <p> akan tampil sebagai teks literal di halaman.
 */
if (!function_exists('plainText')) {
    function plainText($value)
    {
        // Decode entity dulu supaya &lt;p&gt; ikut terbersihkan
        $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // &nbsp; (U+00A0) jadi spasi biasa supaya bisa dirapikan/di-trim
        $value = str_replace("\xC2\xA0", ' ', $value);

        // Tag blok -> spasi, supaya kata di dua paragraf tidak menempel
        $value = preg_replace('#</?(p|div|blockquote|pre|li|ul|ol|h[1-6])\b[^>]*>#i', ' ', $value);
        $value = preg_replace('#<br\s*/?>#i', ' ', $value);

        // Sisa tag apa pun dibuang
        $value = strip_tags((string)$value);

        // Rapikan spasi (unicode-aware, dengan fallback bila byte tidak valid UTF-8)
        $collapsed = preg_replace('/\s+/u', ' ', $value);
        $value = $collapsed !== null ? $collapsed : preg_replace('/\s+/', ' ', $value);

        return trim((string)$value);
    }
}
