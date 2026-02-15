<?php
/**
 * API Endpoint: Get Exams with Filters
 * Returns exams (current or all) based on filters applied
 */
// Start session BEFORE sending headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // Include auth check
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    
    // Include autoloader
    require_once __DIR__ . '/../../../../../../autoloader.php';

    // Include DB helper
    require_once __DIR__ . '/../../../../../Core/database.php';

    // Basic session checks (avoid redirecting from auth_check in JSON API)
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: User not logged in', 'data' => []]);
        exit;
    }
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'school') {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden: Not a school admin', 'data' => []]);
        exit;
    }

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: missing school context', 'data' => []]);
        exit;
    }

    $db = \Database::connect();
    
    $examController = new \App\Modules\School_Admin\Controllers\ExamController($db, $school_id);
    
    // Get filter parameters
    $filters = [
        'session_id' => isset($_GET['session_id']) && $_GET['session_id'] ? (int)$_GET['session_id'] : null,
        'exam_type' => isset($_GET['exam_type']) && $_GET['exam_type'] ? trim($_GET['exam_type']) : null,
        'class_id' => isset($_GET['class_id']) && $_GET['class_id'] ? (int)$_GET['class_id'] : null,
        'section_id' => isset($_GET['section_id']) && $_GET['section_id'] ? (int)$_GET['section_id'] : null,
        'date_from' => isset($_GET['date_from']) && $_GET['date_from'] ? trim($_GET['date_from']) : null,
        'date_to' => isset($_GET['date_to']) && $_GET['date_to'] ? trim($_GET['date_to']) : null,
    ];

    $isCurrent = isset($_GET['current']) && $_GET['current'] ? true : false;

    // Get exams
    $exams = [];
    try {
        if ($isCurrent) {
            $exams = $examController->getCurrentExamsWithDetails($filters);
        } else {
            $exams = $examController->getAllExamsWithDetails($filters);
        }
    } catch (Exception $methodError) {
        // If method fails, try simple fallback query
        $query = "
            SELECT 
                e.id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.status,
                e.session_id,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.school_id = ?
        ";
        
        $params = [$school_id];
        
        if (!$isCurrent) {
            // For all exams, no date filter
        } else {
            // For current exams, add date filter
            $query .= " AND DATE(e.start_date) >= CURDATE()";
        }
        
        if (isset($filters['session_id']) && $filters['session_id']) {
            $query .= " AND e.session_id = ?";
            $params[] = $filters['session_id'];
        }
        
        if (isset($filters['exam_type']) && $filters['exam_type']) {
            $query .= " AND e.exam_type = ?";
            $params[] = $filters['exam_type'];
        }
        
        $query .= " ORDER BY e.start_date DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add class_section placeholder
        foreach ($exams as &$exam) {
            $exam['class_section'] = 'All Classes';
            $exam['marks_uploaded_percent'] = 0;
        }
    }
    
    // Ensure marks_uploaded_percent is set
    if (is_array($exams)) {
        foreach ($exams as &$exam) {
            if (!isset($exam['marks_uploaded_percent'])) {
                $exam['marks_uploaded_percent'] = 0;
            }
            if (!isset($exam['class_section'])) {
                $exam['class_section'] = 'All Classes';
            }
        }
    } else {
        $exams = [];
    }

    // Get statistics if current exams
    $stats = null;
    if ($isCurrent) {
        try {
            $statsRaw = $examController->getMarksUploadStats();
            $stats = [
                'total' => (int)($statsRaw['total_exams'] ?? 0),
                'uploaded' => (int)($statsRaw['exams_with_marks'] ?? 0),
                'pending' => (int)($statsRaw['exams_without_marks'] ?? 0),
                'completion_rate' => (int)($statsRaw['completion_rate'] ?? 0)
            ];
        } catch (Exception $statsError) {
            $stats = [
                'total' => 0,
                'uploaded' => 0,
                'pending' => 0,
                'completion_rate' => 0
            ];
        }
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $exams,
        'stats' => $stats
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
