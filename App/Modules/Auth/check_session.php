<?php
session_start();

// Return JSON response about session status
header('Content-Type: application/json');

$response = [
    'logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
    'user_id' => $_SESSION['user_id'] ?? null
];

echo json_encode($response);
exit;
?>
