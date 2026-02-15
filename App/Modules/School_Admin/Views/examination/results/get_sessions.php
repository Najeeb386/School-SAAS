<?php
/**
 * API Endpoint: Get Sessions
 * Returns all sessions for the current school
 */
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
