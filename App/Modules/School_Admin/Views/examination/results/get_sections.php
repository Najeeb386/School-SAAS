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

    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    
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
    
    // If exam_id is provided, get only sections that are assigned to this exam
    if ($exam_id) {
        $stmt = $db->prepare("
            SELECT DISTINCT cs.id, cs.section_name 
            FROM school_class_sections cs
            INNER JOIN school_exam_classes ec ON cs.id = ec.section_id AND ec.class_id = cs.class_id
            WHERE cs.class_id = ? AND cs.school_id = ? AND ec.exam_id = ?
            ORDER BY cs.section_name ASC
        ");
        $stmt->execute([$class_id, $school_id, $exam_id]);
    } else {
        // Otherwise get all sections for the class
        $stmt = $db->prepare("
            SELECT cs.id, cs.section_name 
            FROM school_class_sections cs
            WHERE cs.class_id = ? AND cs.school_id = ?
            ORDER BY cs.section_name ASC
        ");
        $stmt->execute([$class_id, $school_id]);
    }
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $sections
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'data' => [],
        'message' => $e->getMessage()
    ]);
}
