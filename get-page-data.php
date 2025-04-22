<?php
session_start();

// Set the Content-Type header to application/json
header('Content-Type: application/json');

// Check if session filename is already generated
if (!isset($_SESSION['page_data_file'])) {
    echo json_encode(['error' => 'No active session']);
    exit;
}

$filePath = __DIR__ . '/sessions/' . $_SESSION['page_data_file'];

if (!file_exists($filePath)) {
    echo json_encode(['error' => 'Session file not found']);
    exit;
}

$jsonData = file_get_contents($filePath);
$pageData = json_decode($jsonData, true);

// Return the page data in JSON format
echo json_encode($pageData);
?>
