<?php
session_start();
include_once("logger.php");

// Check if session file exists
if (!isset($_SESSION['page_data_file'])) {
    http_response_code(400);
    echo json_encode(["error" => "No session file found."]);
    exit;
}

$filePath = __DIR__ . '/sessions/' . $_SESSION['page_data_file'];

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(["error" => "Session data file not found."]);
    exit;
}

// Load existing data
$jsonData = file_get_contents($filePath);
$data = json_decode($jsonData, true);

// Get the incoming block data from the request
$updatedBlock = $_POST;

if (!$updatedBlock || !isset($updatedBlock['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing block ID or data."]);
    exit;
}

// Function to sanitize URLs
function sanitizeURL($url) {
    // Ensure the URL starts with 'http' or 'https' or any valid protocol like mailto
    $urlPattern = '/^(https?|mailto|ftp):\/\/[^\s/$.?#].[^\s]*$/i';
    return preg_match($urlPattern, $url) ? $url : ''; // Return an empty string if the URL is invalid
}

// Sanitize the URLs in the updated block data
if (isset($updatedBlock['logoSrc'])) {
    $updatedBlock['logoSrc'] = sanitizeURL($updatedBlock['logoSrc']);
}

if (isset($updatedBlock['profileUrl'])) {
    $updatedBlock['profileUrl'] = sanitizeURL($updatedBlock['profileUrl']);
}

// Sanitize all center link URLs if they exist
if (isset($updatedBlock['centerLinkUrl']) && is_array($updatedBlock['centerLinkUrl'])) {
    foreach ($updatedBlock['centerLinkUrl'] as $index => $url) {
        $updatedBlock['centerLinkUrl'][$index] = sanitizeURL($url);
    }
}

// Find the block by ID and update it
foreach ($data['page'][0]['block'] as &$block) {
    if ($block['id'] === $updatedBlock['id']) {
        // Update the block's fields with the incoming data
        $block = array_merge($block, $updatedBlock);
        break;
    }
}

// Save back to the session JSON file
file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
