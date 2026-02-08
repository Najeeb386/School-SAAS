<?php
/**
 * Exam Assignment Management API
 * Handles all requests for exam class assignments and subjects
 */

// Set proper headers and error handling
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Set up error handler to capture all errors as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error: ' . $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    error_log("API Error: $errstr in $errfile:$errline");
    exit;
});

// Set up exception handler
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    error_log("API Exception: " . $e->getMessage());
    exit;
});

try {
    // Load required files with error checking
    $auth_file = __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    $db_file = __DIR__ . '/../../../../../Core/database.php';
    // Model is in App/Modules/School_Admin/Models (3 levels up from generate_exam)
    $model_file = __DIR__ . '/../../../Models/ExamAssignmentModel.php';
    // Controller is in App/Controllers (5 levels up from generate_exam)
    $controller_file = __DIR__ . '/../../../../../Controllers/ExamAssignmentController.php';
    
    // Check if files exist
    if (!file_exists($auth_file)) {
        throw new Exception('Auth file not found: ' . $auth_file);
    }
    if (!file_exists($db_file)) {
        throw new Exception('Database file not found: ' . $db_file);
    }
    if (!file_exists($model_file)) {
        throw new Exception('Model file not found: ' . $model_file);
    }
    if (!file_exists($controller_file)) {
        throw new Exception('Controller file not found: ' . $controller_file);
    }
    
    // Load files
    require_once $auth_file;
    require_once $db_file;
    require_once $model_file;
    require_once $controller_file;

    // Get school ID from session
    $school_id = $_SESSION['school_id'] ?? null;

    if (!$school_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: No school_id in session']);
        exit;
    }

    // Initialize database connection using static method
    $pdo = Database::connect();
    if (!$pdo) {
        throw new Exception('Database connection returned null');
    }

    // Initialize controller
    $controller = new ExamAssignmentController($pdo, $school_id);

    // Get action from request
    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    if (!$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action parameter is required']);
        exit;
    }

    // Add a health check endpoint
    if ($action === 'health') {
        echo json_encode([
            'success' => true,
            'message' => 'API is healthy',
            'school_id' => $school_id
        ]);
        exit;
    }

    switch ($action) {
        
        case 'get_exam':
            // Get exam details by ID
            $exam_id = $_GET['exam_id'] ?? null;
            if (!$exam_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
                exit;
            }
            
            $exam = $controller->getExam($exam_id);
            echo json_encode([
                'success' => $exam ? true : false,
                'data' => $exam,
                'message' => $exam ? 'Exam found' : 'Exam not found'
            ]);
            break;
            
        case 'get_classes':
            // Get all classes
            $classes = $controller->getClasses();
            echo json_encode([
                'success' => true,
                'data' => $classes
            ]);
            break;
            
        case 'get_sections':
            // Get sections by class ID
            $class_id = $_GET['class_id'] ?? null;
            if (!$class_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Class ID is required']);
                exit;
            }
            
            $sections = $controller->getSectionsByClass($class_id);
            echo json_encode([
                'success' => true,
                'data' => $sections
            ]);
            break;
            
        case 'get_subjects':
            // Get all subjects
            $subjects = $controller->getSubjects();
            echo json_encode([
                'success' => true,
                'data' => $subjects
            ]);
            break;
            
        case 'get_assignments':
            // Get all assignments or by exam ID
            $exam_id = $_GET['exam_id'] ?? null;
            
            if ($exam_id) {
                $assignments = $controller->getAssignmentsByExamId($exam_id);
            } else {
                $assignments = $controller->getAssignments();
            }
            
            echo json_encode([
                'success' => true,
                'data' => $assignments
            ]);
            break;
            
        case 'save_assignment':
            // Save exam assignment
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'POST method required']);
                exit;
            }
            
            $raw_input = file_get_contents('php://input');
            $input_data = json_decode($raw_input, true);
            
            if (!$input_data || !is_array($input_data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data', 'raw' => substr($raw_input, 0, 100)]);
                exit;
            }
            
            $result = $controller->saveAssignment($input_data);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        case 'delete_assignment':
            // Delete assignment
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'POST method required']);
                exit;
            }
            
            $input_data = json_decode(file_get_contents('php://input'), true);
            $exam_class_id = $input_data['exam_class_id'] ?? null;
            
            if (!$exam_class_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Exam class ID is required']);
                exit;
            }
            
            $result = $controller->deleteAssignment($exam_class_id);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        case 'update_assignment':
            // Update exam assignment
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'POST method required']);
                exit;
            }
            
            $input_data = json_decode(file_get_contents('php://input'), true);
            
            if (!$input_data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                exit;
            }
            
            $result = $controller->updateAssignment($input_data);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        case 'get_subjects_by_class_section':
            // Get subjects assigned to a specific class and section
            $class_id = $_GET['class_id'] ?? null;
            $section_id = $_GET['section_id'] ?? null;
            
            if (!$class_id || !$section_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Class ID and Section ID are required']);
                exit;
            }
            
            $subjects = $controller->getSubjectsByClassAndSection($class_id, $section_id);
            echo json_encode([
                'success' => true,
                'data' => $subjects
            ]);
            break;

        case 'get_subjects_by_class':
            // Get all subjects assigned to a specific class (all sections)
            $class_id = $_GET['class_id'] ?? null;
            
            if (!$class_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Class ID is required']);
                exit;
            }
            
            $subjects = $controller->getSubjectsByClass($class_id);
            echo json_encode([
                'success' => true,
                'data' => $subjects
            ]);
            break;

        case 'update_subject':
            // Update single subject details only
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'POST method required']);
                exit;
            }
            
            $input_data = json_decode(file_get_contents('php://input'), true);
            
            if (!$input_data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                exit;
            }
            
            $result = $controller->updateSubject($input_data);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;

        case 'delete_subject':
            // Delete single subject
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'POST method required']);
                exit;
            }
            
            $input_data = json_decode(file_get_contents('php://input'), true);
            $subject_id = $input_data['subject_id'] ?? null;
            
            if (!$subject_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
                exit;
            }
            
            $result = $controller->deleteSubject($subject_id);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;

        case 'get_datesheet_data':
            // Get complete datesheet data including school info
            $exam_id = $_GET['exam_id'] ?? null;
            
            if (!$exam_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
                exit;
            }
            
            // Get school info from session
            $school_id = $_SESSION['school_id'] ?? null;
            
            // Fetch school details from schools table
            $stmt = $pdo->prepare("
                SELECT id, name as school_name, logo_path, address, city
                FROM schools 
                WHERE id = ?
            ");
            $stmt->execute([$school_id]);
            $school_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get exam details
            $stmt = $pdo->prepare("
                SELECT id, exam_name, start_date, end_date, status 
                FROM school_exams 
                WHERE id = ? AND school_id = ?
            ");
            $stmt->execute([$exam_id, $school_id]);
            $exam_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$exam_data) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Exam not found']);
                exit;
            }
            
            // Get complete schedule data for this exam
            $assignments = $controller->getAssignmentsByExamId($exam_id);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'school' => $school_data,
                    'exam' => $exam_data,
                    'schedule' => $assignments
                ]
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("API Error in manage_exam_assignments.php: " . $e->getMessage());
}
?>


