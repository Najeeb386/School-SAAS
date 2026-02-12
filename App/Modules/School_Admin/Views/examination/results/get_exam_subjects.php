<?php
/**
 * API Endpoint: Get Exam Subjects
 * Returns all subjects for a specific exam
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

    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    
    if (!$exam_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Exam ID is required'
        ]);
        exit;
    }

    $db = \Database::connect();
    
    // Query to get subjects for specific exam and class
    if ($class_id) {
        $stmt = $db->prepare("
            SELECT 
                eses.id,
                s.subject_name,
                s.subject_code,
                eses.total_marks
            FROM school_exam_subjects eses
            JOIN school_subjects s ON eses.subject_id = s.id
            JOIN school_exam_classes ec ON eses.exam_class_id = ec.id
            WHERE ec.exam_id = ? AND ec.class_id = ? AND ec.school_id = ?
            ORDER BY s.subject_name ASC
        ");
        $stmt->execute([$exam_id, $class_id, $school_id]);
    } else {
        // If no class specified, get all subjects for the exam
        $stmt = $db->prepare("
            SELECT 
                eses.id,
                s.subject_name,
                s.subject_code,
                eses.total_marks
            FROM school_exam_subjects eses
            JOIN school_subjects s ON eses.subject_id = s.id
            JOIN school_exam_classes ec ON eses.exam_class_id = ec.id
            WHERE ec.exam_id = ? AND ec.school_id = ?
            ORDER BY s.subject_name ASC
        ");
        $stmt->execute([$exam_id, $school_id]);
    }
    
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $subjects
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
