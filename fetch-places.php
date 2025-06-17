<?php
header('Content-Type: application/json');

// Get input
$input = $_GET['input'] ?? '';
$lat = $_GET['lat'] ?? '';
$lng = $_GET['lng'] ?? '';
if ($input == '') {
    echo json_encode(['error' => 'input missing']);
    exit;
}

$apiURL = "https://www.swiggy.com/dapi/misc/place-autocomplete?input=" . urlencode($input) . "&lat=" . $lat . "&lng=" . $lng;

// Initialize cURL
$ch = curl_init($apiURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)', // pretend we are a real browser
]);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json'); // make sure we respond in proper format
echo $response;
?>