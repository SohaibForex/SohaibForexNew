<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Cache for 5 minutes
$cache = sys_get_temp_dir() . '/mfx_12042691.json';
if (file_exists($cache) && (time() - filemtime($cache)) < 300) {
    echo file_get_contents($cache);
    exit;
}

$url = 'https://www.myfxbook.com/members/Sohaibforex/dgs-gold/12042691';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER => [
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
$err  = curl_error($ch);
curl_close($ch);

if (!$html || $code !== 200) {
    echo json_encode(['ok' => false, 'error' => "HTTP $code $err"]);
    exit;
}

function pick($html, $patterns) {
    foreach ($patterns as $p) {
        if (preg_match($p, $html, $m)) {
            return trim(strip_tags(html_entity_decode($m[1])));
        }
    }
    return null;
}

$data = [
    'ok'          => true,
    'gain'        => pick($html, ['/"gain"\s*:\s*"([^"]+)"/', '/Gain<\/[^>]+>\s*<[^>]+>([\d\.\+\-,%]+)/', '/id="totalGain[^"]*"[^>]*>([^<]+)/i']),
    'absGain'     => pick($html, ['/"absoluteGain"\s*:\s*"([^"]+)"/', '/Absolute Gain<\/[^>]+>\s*<[^>]+>([\d\.\+\-,%]+)/']),
    'monthly'     => pick($html, ['/"monthly"\s*:\s*"([^"]+)"/', '/Monthly<\/[^>]+>\s*<[^>]+>([\d\.\+\-,%]+)/', '/Avg\. Monthly Gain[^%]*?([\d\.\+\-]+)%/i']),
    'drawdown'    => pick($html, ['/"drawdown"\s*:\s*"([^"]+)"/', '/Drawdown[^%]*?([\d\.]+)%/i', '/Max\. Drawdown[^%]*?([\d\.]+)%/i']),
    'winRate'     => pick($html, ['/"won"\s*:\s*"([^"]+)"/', '/Won:\s*([\d\.]+)%/i', '/Win Rate[^%]*?([\d\.]+)%/i']),
    'profitFactor'=> pick($html, ['/"profitFactor"\s*:\s*"([^"]+)"/', '/Profit Factor[^<>]*?([0-9]+\.[0-9]+)/i']),
    'trades'      => pick($html, ['/"totalTrades"\s*:\s*(\d+)/', '/Total Trades[^<>]*?(\d+)/i']),
    'balance'     => pick($html, ['/"balance"\s*:\s*"([^"]+)"/', '/Balance[^$]*?\$([\d,\.]+)/i']),
    'equity'      => pick($html, ['/"equity"\s*:\s*"([^"]+)"/', '/Equity[^$]*?\$([\d,\.]+)/i']),
    'pips'        => pick($html, ['/"pips"\s*:\s*"([^"]+)"/', '/Pips[^<>]*?([\d,\.]+)/i']),
    'updated'     => date('d M Y, H:i T'),
    'url'         => $url,
];

$json = json_encode($data);
file_put_contents($cache, $json);
echo $json;
