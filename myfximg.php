<?php
// Uses thum.io (free, no API key) to take a live screenshot of the Myfxbook
// performance page and serve it as an image. Cached 15 minutes.

$cacheImg  = sys_get_temp_dir() . '/mfx_screenshot_12042691.bin';
$cacheMime = sys_get_temp_dir() . '/mfx_screenshot_mime_12042691.txt';

if (file_exists($cacheImg) && file_exists($cacheMime) && (time() - filemtime($cacheImg)) < 900) {
    header('Content-Type: ' . trim(file_get_contents($cacheMime)));
    header('Cache-Control: max-age=900, public');
    readfile($cacheImg);
    exit;
}

function svgMsg($msg) {
    header('Content-Type: image/svg+xml');
    header('Cache-Control: no-cache');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="200">'
       . '<rect width="100%" height="100%" fill="#111"/>'
       . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" '
       . 'fill="#555" font-family="Inter,sans-serif" font-size="14">'
       . htmlspecialchars($msg) . '</text></svg>';
    exit;
}

// thum.io renders the full page in a headless browser and returns a PNG screenshot.
// width/1280  = screenshot at 1280px viewport width
// crop/820    = crop to top 820px of the rendered page (shows the main stats section)
$mfxUrl = 'https://www.myfxbook.com/members/Sohaibforex/dgs-gold/12042691';
$url    = 'https://image.thum.io/get/width/1280/crop/820/' . $mfxUrl;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
]);
$imgData = curl_exec($ch);
$info    = curl_getinfo($ch);
curl_close($ch);

$mime = strtok($info['content_type'] ?? 'image/jpeg', ';');

if (!$imgData || $info['http_code'] !== 200 || strpos($mime, 'image/') !== 0) {
    svgMsg('Performance preview loading… check back shortly');
}

file_put_contents($cacheImg, $imgData);
file_put_contents($cacheMime, $mime);

header('Content-Type: ' . $mime);
header('Cache-Control: max-age=900, public');
echo $imgData;
