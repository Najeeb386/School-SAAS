<?php
/**
 * Simple test endpoint for manage_exams.php access
 */
session_start();

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Session is working',
    'school_id' => $_SESSION['school_id'] ?? 'NOT SET',
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'session_vars' => $_SESSION
]);
?>
