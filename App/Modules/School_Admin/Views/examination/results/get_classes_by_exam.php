<?php
/**
 * API Endpoint: Get Classes by Exam
 * Returns only classes assigned to a specific exam
 */

// Start session BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Send JSON header
header('Content-Type: application/json; charset=utf-8');
header('X-Powered-By: PHP/' . phpversion());

// Enable error reporting for better debugging
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    // Get the exam_id from query string first
    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    
    if (!$exam_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Exam ID is required'
        ]);
        exit;
    }
    
    // Check session
    $school_id = $_SESSION['school_id'] ?? null;
    $logged_in = $_SESSION['logged_in'] ?? false;
    $user_type = $_SESSION['user_type'] ?? null;
    
    // Validation
    if (!$logged_in || $logged_in !== true) {
        // Allow a secure localhost fallback for testing/deployment convenience
        $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
        if ($isLocal && isset($_GET['school_id'])) {
            $school_id = (int) $_GET['school_id'];
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Not logged in',
                'debug' => ['logged_in' => $logged_in]
            ]);
            exit;
        }
    }
    
    if ($user_type !== 'school' && !isset($school_id)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized user type',
            'debug' => ['user_type' => $user_type]
        ]);
        exit;
    }
    
    if (!$school_id) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'No school ID in session'
        ]);
        exit;
    }
    
    // Require database class
    $appRoot = dirname(__DIR__, 5);
    $dbPath = $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
    
    if (!file_exists($dbPath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database class not found',
            'debug' => ['db_path' => $dbPath]
        ]);
        exit;
    }
    
    require_once $dbPath;
    
    // Get database connection
    $db = \Database::connect();
    
    // Execute query
    // Note: some installations may not have `school_id` on the `school_exam_classes` table.
    // Use `ec.exam_id` and filter by `c.school_id` to ensure compatibility.
    $sql = "SELECT c.id, c.class_name
        FROM school_classes c
        INNER JOIN school_exam_classes ec ON c.id = ec.class_id
        WHERE ec.exam_id = ?
        AND c.school_id = ?
        GROUP BY c.id, c.class_name
        ORDER BY c.class_order ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([$exam_id, $school_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $classes
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'error_type' => 'PDOException',
            'error' => $e->getMessage()
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
    exit;
}
?>
