<?php
/**
 * API Endpoint: Get Exam Types
 * Returns all distinct exam types for the current school
 */
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    $appRoot = dirname(__DIR__, 5);
    $projectRoot = dirname($appRoot);
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
    require_once $projectRoot . DIRECTORY_SEPARATOR . 'autoloader.php';
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    
    $stmt = $db->prepare("
        SELECT DISTINCT exam_type 
        FROM school_exams 
        WHERE school_id = ? AND exam_type IS NOT NULL
        ORDER BY exam_type ASC
    ");
    $stmt->execute([$school_id]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to array of values
    $examTypes = array_map(function($row) {
        return $row['exam_type'];
    }, $types);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $examTypes
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
