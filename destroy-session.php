<?php
session_start();
include_once("logger.php");

// Destroy the session and clear any session variables
session_unset();
session_destroy();

// Return a success response
echo json_encode(["message" => "Session destroyed successfully."]);
?>
