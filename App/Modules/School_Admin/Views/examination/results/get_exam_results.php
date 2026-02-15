<?php
/**
 * API Endpoint: Get Exam Results
 * Returns results for a specific exam
 */
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Manual session check (don't use auth_check which redirects)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized', 'data' => []]);
        exit;
    }
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'school') {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden', 'data' => []]);
        exit;
    }
    
    require_once __DIR__ . '/../../../../../../autoloader.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized - No school_id in session');
    }

    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    
    if (!$exam_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Exam ID is required',
            'data' => []
        ]);
        exit;
    }

    $db = \Database::connect();
    
    $examController = new \App\Modules\School_Admin\Controllers\ExamController($db, $school_id);
    $results = $examController->getExamResults($exam_id, $class_id, $section_id);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    exit;

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
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
