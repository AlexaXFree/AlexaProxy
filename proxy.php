<?php
header("Content-Type: text/plain; charset=UTF-8");

// === Get and validate 'file' parameter ===
$url = isset($_GET['file']) ? trim($_GET['file']) : '';
if (empty($url)) {
    http_response_code(400);
    echo "Error: No file specified via ?file=";
    exit;
}

// === Parse and validate host ===
$parsed = parse_url($url);
$host = $parsed['host'] ?? '';

$allowedHosts = ['raw.githubusercontent.com', 'github.com'];
if (!in_array($host, $allowedHosts)) {
    http_response_code(403);
    echo "Error: Host not allowed - $host";
    exit;
}

// === Append 'cb' cache-buster if present ===
if (isset($_GET['cb'])) {
    $cb = preg_replace('/[^0-9]/', '', $_GET['cb']); // Only digits
    $separator = isset($parsed['query']) ? '&' : '?';
    $url .= $separator . 'cb=' . $cb;
}

// === Fetch using cURL ===
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => "Mozilla/5.0 (Proxy)",
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'Accept: */*'
    ]
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// === Handle fetch failure ===
if ($response === false || $httpCode >= 400) {
    http_response_code(502);
    echo "Failed to fetch file.\n";
    echo "HTTP Code: $httpCode\n";
    echo "cURL Error: $error\n";
    exit;
}

// === Output raw content ===
echo $response;
