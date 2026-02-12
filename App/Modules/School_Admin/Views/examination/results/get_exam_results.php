<?php
/**
 * API Endpoint: Get Exam Results
 * Returns results for a specific exam
 */
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    $appRoot = dirname(__DIR__, 5); // Navigate to App folder
    $projectRoot = dirname($appRoot); // Navigate to School-SAAS root
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
    require_once $projectRoot . DIRECTORY_SEPARATOR . 'autoloader.php';
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    
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
    $results = $examController->getExamResults($exam_id);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $results
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
