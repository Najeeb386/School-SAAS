<?php
/**
 * Grading Criteria Management API
 * Handles CRUD operations for grading criteria
 */

// Set error handlers FIRST to catch any errors as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr
    ]);
    exit;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $exception->getMessage()
    ]);
    exit;
});

// Output buffering to prevent header issues
ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');

try {
    // Check session
    if (empty($_SESSION['school_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: School ID not found'
        ]);
        exit;
    }

    // Requires AFTER session check
    require_once __DIR__ . '/../../../../Core/database.php';
    require_once __DIR__ . '/../../Controllers/GradingCriteriaController.php';
    require_once __DIR__ . '/../../Models/GradingCriteriaModel.php';

    $school_id = (int)$_SESSION['school_id'];

    // Get database connection
    $db = \Database::connect();

    // Initialize controller
    $controller = new \App\Modules\School_Admin\Controllers\GradingCriteriaController($db, $school_id);

    // Get action from request
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    switch ($action) {
        case 'get':
            // Get all grading criteria
            $criteria = $controller->getGradingCriteria();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $criteria
            ]);
            break;

        case 'add':
            // Add new grading criteria
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->addGradingCriteria($data);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'update':
            // Update grading criteria
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for update'
                ]);
            } else {
                $result = $controller->updateGradingCriteria($id, $data);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            }
            break;

        case 'delete':
            // Delete grading criteria
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for deletion'
                ]);
            } else {
                $result = $controller->deleteGradingCriteria($id);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action: ' . ($action ?? 'none')
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}


