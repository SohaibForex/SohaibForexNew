<?php
// Server-side proxy: fetches Myfxbook performance page and serves it from OUR domain.
// Because the HTML is served from our domain, the browser has no X-Frame-Options issue.
// The <base href> tag makes all relative URLs (CSS, JS, images) resolve to myfxbook.com.

$cache = sys_get_temp_dir() . '/mfx_frame_12042691.html';
if (file_exists($cache) && (time() - filemtime($cache)) < 180) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($cache);
    exit;
}

$base = 'https://www.myfxbook.com';
$url  = $base . '/members/Sohaibforex/dgs-gold/12042691';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER     => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Accept-Encoding: gzip, deflate, br',
        'Referer: https://www.google.com/',
        'Cache-Control: no-cache',
    ],
    CURLOPT_ENCODING => '',
]);
$html = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$html || $code !== 200) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><body style="background:#0d0d0d;color:#888;'
       . 'font-family:Inter,sans-serif;text-align:center;padding:80px 20px;font-size:1rem">'
       . '<p>Performance data is loading&hellip; Please refresh in a moment.</p></body></html>';
    exit;
}

// Inject <base href> so ALL relative paths (CSS/JS/images) resolve to myfxbook.com
$baseTag = '<base href="' . $base . '/">';
if (preg_match('/<head[^>]*>/i', $html)) {
    $html = preg_replace('/(<head[^>]*>)/i', '$1' . $baseTag, $html, 1);
} else {
    $html = $baseTag . $html;
}

file_put_contents($cache, $html);
header('Content-Type: text/html; charset=utf-8');
echo $html;
