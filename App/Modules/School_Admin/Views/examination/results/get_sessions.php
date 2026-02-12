<?php
/**
 * API Endpoint: Get Sessions
 * Returns all sessions for the current school
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

    $db = \Database::connect();
    
    $sessionModel = new \App\Modules\School_Admin\Models\SessionModel($db);
    $sessions = $sessionModel->getAll($school_id);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $sessions
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
