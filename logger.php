<?php
function app_log($message) {
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/app.log';

    // Create the logs folder if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Format: [2025-04-22 15:30:12] Your log message
    $date = date('[Y-m-d H:i:s]');
    $formattedMessage = $date . ' ' . $message . PHP_EOL;

    // Append to log file
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}
?>