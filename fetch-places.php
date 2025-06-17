<?php
header('Content-Type: application/json');

// Include server URLs
include('servers-config.php'); // This should define $server_urls array

$input = $_GET['input'] ?? '';
$lat = $_GET['lat'] ?? '';
$lng = $_GET['lng'] ?? '';

// Validate
if (empty($input) || empty($lat) || empty($lng)) {
    echo json_encode(['error' => 'Missing parameters']);
    http_response_code(400);
    exit;
}

$indexFile = __DIR__ . '/rotate-index.txt';
$lastIndex = file_exists($indexFile) ? (int)file_get_contents($indexFile) : -1;

// Calculate the next server in a round-robin manner
$nextIndex = ($lastIndex + 1) % count($server_urls);
file_put_contents($indexFile, $nextIndex);

$server = $server_urls[$nextIndex];

// Forward request through the server's proxy script
$url = $server . "/server.php";

$data = [
    "url"   => "https://www.swiggy.com/dapi/misc/place-autocomplete",
    "input" => $input,
    "lat"   => $lat,
    "lng"   => $lng,
];

// Perform a GET request through server.php
$query = http_build_query([
    "url"   => $data['url'], 
    "input" => $input, 
    "lat"   => $lat, 
    "lng"   => $lng,
]);

$fullURL = $url . "?" . $query;

// Fetch response
$response = file_get_contents($fullURL);

if ($response === false) {
    echo json_encode(['error' => 'Unable to fetch from Swiggy API through server']);
    http_response_code(502);
    exit;
}

$counter_file = __DIR__ . "/servers_count.json";

$counts = [];

if (file_exists($counter_file)) {
    $counts = json_decode(file_get_contents($counter_file), true);
    if (!is_array($counts)) {
        $counts = []; // fallback if invalid
    }
}

$counts[$server] = ($counts[$server] ?? 0) + 1;

file_put_contents($counter_file, json_encode($counts, JSON_PRETTY_PRINT));

header("Access-Control-Allow-Origin: *");

echo $response;

?>