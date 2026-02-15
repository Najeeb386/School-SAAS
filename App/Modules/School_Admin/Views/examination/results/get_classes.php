<?php
/**
 * API Endpoint: Get Classes
 * Returns all classes for the current school
 */
// Start session BEFORE sending headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Include auth check
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';

    // Include autoloader
    require_once __DIR__ . '/../../../../../../autoloader.php';

    // Include DB helper
    require_once __DIR__ . '/../../../../../Core/database.php';

    // Basic session checks (avoid redirecting from auth_check in JSON API)
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: User not logged in');
    }
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'school') {
        throw new Exception('Unauthorized: Not a school admin');
    }

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    
    $stmt = $db->prepare("
        SELECT DISTINCT c.id, c.class_name 
        FROM school_classes c
        WHERE c.school_id = ? 
        ORDER BY c.class_order ASC
    ");
    $stmt->execute([$school_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $classes
    ]);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
