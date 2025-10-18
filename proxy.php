<?php
header("Content-Type: text/plain; charset=UTF-8");

// --- Get remote file ---
$url = isset($_GET['file']) ? trim($_GET['file']) : '';
if (!$url) { http_response_code(400); echo "No file specified"; exit; }

// Only allow HTTPS GitHub URLs for security
$host = parse_url($url, PHP_URL_HOST);
if ($host !== 'raw.githubusercontent.com' && $host !== 'github.com') {
    http_response_code(403);
    echo "Host not allowed: " . $host;
    exit;
}

// Use cURL to fetch
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$content = curl_exec($ch);
$err = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($content === false || $httpcode >= 400) {
    http_response_code(502);
    echo "Failed to fetch: HTTP $httpcode, error: $err";
    exit;
}

// Success: output raw content
echo $content;
