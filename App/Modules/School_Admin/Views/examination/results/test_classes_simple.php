<?php
/**
 * API Endpoint: Get Classes by Exam - TEST VERSION (No Auth)
 * Debugging version to identify the issue
 */
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Start session to capture school_id from session if available
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $appRoot = dirname(__DIR__, 5);
    
    // Include database
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
    
    // Get school_id from session if available
    $school_id = $_SESSION['school_id'] ?? $_POST['school_id'] ?? $_GET['school_id'] ?? null;
    
    // For testing, if no school_id, use 10 (the known school from logs)
    if (!$school_id) {
        $school_id = 10;
    }
    
    // Get exam_id
    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    
    if (!$exam_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Exam ID is required',
            'debug' => [
                'school_id_used' => $school_id,
                'session_school_id' => $_SESSION['school_id'] ?? null
            ]
        ]);
        exit;
    }
    
    $db = \Database::connect();
    
    // Execute the query
    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.class_name
        FROM school_classes c
        INNER JOIN school_exam_classes ec ON c.id = ec.class_id
        WHERE ec.exam_id = ? 
        AND ec.school_id = ? 
        AND c.school_id = ?
        GROUP BY c.id, c.class_name
        ORDER BY c.class_order ASC
    ");
    
    $stmt->execute([$exam_id, $school_id, $school_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $classes,
        'debug' => [
            'exam_id' => $exam_id,
            'school_id' => $school_id,
            'count' => count($classes)
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'data' => []
    ]);
    exit;
}
?>
