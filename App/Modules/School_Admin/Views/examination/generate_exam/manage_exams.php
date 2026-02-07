<?php
/**
 * Exam Management API
 * Handles CRUD operations for exams
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/exam_api_errors.log');

// Set error handlers FIRST to catch any errors as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("ERROR [$errno]: $errstr in $errfile on line $errline");
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline
    ]);
    exit;
});

set_exception_handler(function($exception) {
    error_log("EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
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
            'message' => 'Unauthorized'
        ]);
        exit;
    }

    // Requires AFTER session check
    require_once __DIR__ . '/../../../../../App/Core/database.php';
    require_once __DIR__ . '/../../Controllers/ExamController.php';
    require_once __DIR__ . '/../../Models/ExamModel.php';
    require_once __DIR__ . '/../../Models/SessionModel.php';

    $school_id = (int)$_SESSION['school_id'];

    // Get database connection
    $db = \Database::connect();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Initialize controller
    $controller = new \App\Modules\School_Admin\Controllers\ExamController($db, $school_id);

    // Get action from request
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    switch ($action) {
        case 'get':
            // Get all exams
            $exams = $controller->getExams();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $exams ?? []
            ]);
            break;

        case 'filter':
            // Get filtered exams
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->getFilteredExams($data);
            http_response_code(200);
            echo json_encode($result);
            break;

        case 'sessions':
            // Get all sessions for dropdown
            $sessions = $controller->getSessions();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $sessions ?? []
            ]);
            break;

        case 'add':
            // Add new exam
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->addExam($data);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'update':
            // Update exam
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for update'
                ]);
            } else {
                $result = $controller->updateExam($id, $data);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            }
            break;

        case 'delete':
            // Delete exam
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for deletion'
                ]);
            } else {
                $result = $controller->deleteExam($id);
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
    error_log("Caught Exception: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

exit;

    // Get action from request
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    switch ($action) {
        case 'get':
            // Get all exams
            $exams = $controller->getExams();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $exams
            ]);
            break;

        case 'filter':
            // Get filtered exams
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $result = $controller->getFilteredExams($data);
            http_response_code(200);
            echo json_encode($result);
            break;

        case 'sessions':
            // Get all sessions for dropdown
            $sessions = $controller->getSessions();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $sessions
            ]);
            break;

        case 'add':
            // Add new exam
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $controller->addExam($data);
            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'update':
            // Update exam
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for update'
                ]);
            } else {
                $result = $controller->updateExam($id, $data);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            }
            break;

        case 'delete':
            // Delete exam
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID is required for deletion'
                ]);
            } else {
                $result = $controller->deleteExam($id);
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
    error_log("Caught Exception: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

exit;
