<?php
/**
 * API Endpoint to manage holidays
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'PHP Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline
    ]);
    exit;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Exception: ' . $exception->getMessage()
    ]);
    exit;
});

ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');

try {
    if (empty($_SESSION['school_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception('Unauthorized');
    }

    $school_id = $_SESSION['school_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Controllers/HolidayController.php';
    require_once __DIR__ . '/../../../Models/HolidayModel.php';

    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\HolidayController($db, (int)$school_id);

    if ($action === 'get') {
        $holidays = $controller->getHolidays();
        echo json_encode([
            'success' => true,
            'data' => $holidays
        ]);
        exit;
    } 
    elseif ($action === 'add') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['title']) || !isset($data['event_type'])) {
            throw new Exception('Missing required fields');
        }

        $data['created_by'] = $user_id;
        
        if ($controller->addHoliday($data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Holiday added successfully'
            ]);
            exit;
        } else {
            throw new Exception('Failed to add holiday');
        }
    } 
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id']) || !isset($data['title'])) {
            throw new Exception('Missing required fields');
        }

        if ($controller->updateHoliday((int)$data['id'], $data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Holiday updated successfully'
            ]);
            exit;
        } else {
            throw new Exception('Failed to update holiday');
        }
    } 
    elseif ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Holiday ID is required');
        }

        if ($controller->deleteHoliday((int)$id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Holiday deleted successfully'
            ]);
            exit;
        } else {
            throw new Exception('Failed to delete holiday');
        }
    } 
    else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
