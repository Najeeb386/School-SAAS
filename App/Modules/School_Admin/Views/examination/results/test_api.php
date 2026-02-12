<?php
/**
 * Test API Endpoint for Debugging
 */
header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    $appRoot = dirname(__DIR__, 5);
    $projectRoot = dirname($appRoot);
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
    require_once $projectRoot . DIRECTORY_SEPARATOR . 'autoloader.php';
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    
    if (!$school_id) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $db = \Database::connect();
    
    // Test basic query
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM school_exams WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC);

    // Test current exams
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM school_exams WHERE school_id = ? AND DATE(start_date) >= CURDATE()");
    $stmt->execute([$school_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    // Test today's date
    $stmt = $db->prepare("SELECT CURDATE() as today");
    $today = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sample exam data
    $stmt = $db->prepare("
        SELECT id, exam_name, exam_type, start_date, end_date, status 
        FROM school_exams 
        WHERE school_id = ? 
        ORDER BY start_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$school_id]);
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'school_id' => $school_id,
        'total_exams' => $total['count'],
        'current_exams' => $current['count'],
        'today' => $today['today'],
        'sample_exams' => $samples
    ]);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
