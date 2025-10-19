<?php
header("Content-Type: text/plain; charset=UTF-8");

// === Get and validate URL ===
$url = isset($_GET['file']) ? trim($_GET['file']) : '';
if (!$url) {
    http_response_code(400);
    echo "No file specified";
    exit;
}

// === Append cb param if present ===
if (isset($_GET['cb'])) {
    $cb = preg_replace('/[^0-9]/', '', $_GET['cb']); // sanitize
    $url .= (strpos($url, '?') === false ? '?' : '&') . 'cb=' . $cb;
}

// === Restrict to GitHub hosts ===
$host = parse_url($url, PHP_URL_HOST);
if ($host !== 'raw.githubusercontent.com' && $host !== 'github.com') {
    http_response_code(403);
    echo "Host not allowed: " . $host;
    exit;
}

// === Fetch using cURL ===
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => "Mozilla/5.0",
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'Accept: */*'
    ]
]);

$content = curl_exec($ch);
$err = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// === Handle failure ===
if ($content === false || $httpcode >= 400) {
    http_response_code(502);
    echo "Failed to fetch: HTTP $httpcode, error: $err";
    exit;
}

// === Output ===
echo $content;
