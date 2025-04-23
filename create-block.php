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

// Get block type from request (using POST instead of GET)
$blockType = isset($_POST['type']) ? $_POST['type'] : null;
if (!$blockType) {
    http_response_code(400);
    echo json_encode(["error" => "Missing block type."]);
    exit;
}

$newBlock = null;

switch ($blockType) {
    case 'navigation':
        $newBlock = [
            "id" => "block-" . rand(1000000, 9999999),
            "type" => "navigation",
            "logo" => [
                "src" => "https://cdn4.iconfinder.com/data/icons/logos-and-brands/512/232_Nintendo_Switch_logo-256.png",
                "label" => "LOGO"
            ],
            "centerLinks" => [
                ["title" => "Home", "url" => "#"],
                ["title" => "Test", "url" => "#"],
                ["title" => "About", "url" => "#"]
            ],
            "profileLink" => [
                "title" => "Profile",
                "url" => "#"
            ]
        ];
        break;

    case 'hero':
        $newBlock = [
            "id" => "block-" . rand(1000000, 9999999),
            "type" => "hero",
            "name" => "Hero Block",
            "title" => "Welcome to Our Site!",
            "sub_title" => "A place to showcase your products.",
            "description" => "This is a hero section with catchy text and an attractive image."
        ];
        break;

    // You can add more cases here for other types:
    // case 'banner': ...
    case 'banner':
        $newBlock = [
            "id" => "block-" . rand(1000000, 9999999),
            "type" => "banner",
            "name" => "Banner Block",
            "description" => "This is a hero section with catchy text and an attractive image."
        ];
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Unknown block type."]);
        exit;
}

// Append the new block to the first page (or customize as needed)
$data['page'][0]['block'][] = $newBlock;

// Save back to the session JSON file
file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

// Return the new block as JSON
header('Content-Type: application/json');
echo json_encode($newBlock);
?>
