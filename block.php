<?php
session_start();
include_once("logger.php");

header('Content-Type: application/json');  // <-- Tell the browser it's JSON

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing block ID']);
    exit;
}

$blockId = intval($_GET['id']);  // Sanitize input

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

$foundBlock = null;

if (isset($pageData['page'][0]['block']) && is_array($pageData['page'][0]['block'])) {
    foreach ($pageData['page'][0]['block'] as $block) {
        if (isset($block['id']) && intval($block['id']) === $blockId) {  // <-- force both to int
            $foundBlock = $block;
            break;
        }
    }
}

if ($foundBlock) {
    echo json_encode($foundBlock);  // Success
} else {
    echo json_encode(['error' => 'Block not found']);
}