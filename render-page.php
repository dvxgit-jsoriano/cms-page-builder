<?php
session_start();
// IFRAME TEST FAILED!!!

if (!isset($_SESSION['page_data_file'])) {
    echo "No active session.";
    exit;
}

$filePath = __DIR__ . '/sessions/' . $_SESSION['page_data_file'];

if (!file_exists($filePath)) {
    echo "Session file not found.";
    exit;
}

$jsonData = file_get_contents($filePath);
$pageData = json_decode($jsonData, true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Render</title>
    <!-- Add your own or template CSS -->
    <!-- <link rel="stylesheet" href="your-template.css">  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
<div id="page-layout">
<?php
if (isset($pageData['page'][0]['block']) && is_array($pageData['page'][0]['block'])) {
    foreach ($pageData['page'][0]['block'] as $block) {
        echo "<div class='block mb-4 p-3 border rounded'>";
        switch ($block['type']) {

            case 'hero':
                $id = $block['id'] ?? 0;
                $title = htmlspecialchars($block['title'] ?? 'No Title');
                $description = htmlspecialchars($block['description'] ?? 'No Description');

                echo 
                <<<TEXT
                    <section data-id="$id" class="group relative">
                        <button class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-3 py-1 rounded hover:bg-opacity-70 transition hidden group-hover:block edit-btn">
                            Edit
                        </button>
                        <div class="p-6 bg-blue-100 rounded shadow">
                            <h1 class="text-2xl font-bold mb-2">$title</h1>
                            <p class="text-gray-700">$description</p>
                        </div>
                    </section>
                TEXT;
                break;

            case 'banner':
                echo "
                    <div class='banner'>
                        <p>" . htmlspecialchars($block['description'] ?? 'No description available') . "</p>
                    </div>
                ";
                break;

            case 'left-image':
                echo "
                    <h2>" . htmlspecialchars($block['title'] ?? 'No Title') . "</h2>
                    <p>" . htmlspecialchars($block['description'] ?? 'No description available') . "</p>
                    <img src='" . htmlspecialchars($block['imageUrl'] ?? '#') . "' alt='Image' class='img-fluid'>
                ";
                break;

            default:
                echo "<p>Unknown block type: " . htmlspecialchars($block['type']) . "</p>";
                break;
        }
        echo "</div>";
    }
} else {
    echo "<p>No blocks found.</p>";
}
?>
</div>
</body>
</html>
