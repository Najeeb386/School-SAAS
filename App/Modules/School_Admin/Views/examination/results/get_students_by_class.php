<?php
/**
 * API Endpoint: Get Students by Class and Section
 * Returns all students for a specific class and section
 */

// Start session BEFORE sending headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    $appRoot = dirname(__DIR__, 5);
    
    // Check authentication directly (avoid redirect headers after JSON header)
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized: User not logged in');
    }
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'school') {
        throw new Exception('Unauthorized: Not a school admin');
    }
    
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    
    if (!$class_id || !$section_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Class ID and Section ID are required'
        ]);
        exit;
    }

    $db = \Database::connect();
    
    // Get students with correct column mapping
    $stmt = $db->prepare("
        SELECT 
            s.id as student_id,
            CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as student_name,
            COALESCE(sse.roll_no, 0) as roll_no
        FROM school_students s
        INNER JOIN school_student_academics sa ON s.id = sa.student_id
        LEFT JOIN school_student_enrollments sse ON s.id = sse.student_id AND sa.class_id = sse.class_id AND sa.section_id = sse.section_id
        WHERE s.school_id = ? 
        AND sa.class_id = ? 
        AND sa.section_id = ?
        AND sa.status = 1
        ORDER BY COALESCE(sse.roll_no, s.id) ASC
    ");
    $stmt->execute([$school_id, $class_id, $section_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $students
    ]);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
    exit;
}

