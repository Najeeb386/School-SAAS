<?php
/**
 * API Endpoint: Get Sections by Class
 * Returns all sections for a specific class
 */

// Start session BEFORE sending headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../../autoloader.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    
    if (!$class_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Class ID is required'
        ]);
        exit;
    }

    $db = \Database::connect();
    
    $stmt = $db->prepare("
        SELECT cs.id, cs.section_name 
        FROM school_class_sections cs
        WHERE cs.class_id = ? AND cs.school_id = ?
        ORDER BY cs.section_name ASC
    ");
    $stmt->execute([$class_id, $school_id]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $sections
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
