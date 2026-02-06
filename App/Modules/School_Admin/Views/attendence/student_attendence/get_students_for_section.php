<?php
/**
 * API Endpoint to fetch students for a given class and section
 */
// Suppress any output before setting JSON header
ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (empty($_SESSION['school_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception('Unauthorized');
    }

    $school_id = $_SESSION['school_id'];
    $class_id = intval($_GET['class_id'] ?? 0);
    $section_id = intval($_GET['section_id'] ?? 0);

    if (!$class_id || !$section_id) {
        throw new Exception('Missing required parameters');
    }

    require_once __DIR__ . '/../../../../../Core/database.php';
    $db = \Database::connect();

    // Get students for this class and section
    $stmt = $db->prepare("
        SELECT 
            se.student_id,
            se.admission_no,
            se.roll_no,
            ss.first_name,
            ss.last_name
        FROM school_student_enrollments se
        JOIN school_students ss ON ss.id = se.student_id
        WHERE se.school_id = ? 
            AND se.class_id = ? 
            AND se.section_id = ?
            AND se.status = 'active'
            AND se.deleted_at IS NULL
        ORDER BY se.roll_no ASC, ss.first_name ASC
    ");
    $stmt->execute([$school_id, $class_id, $section_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $students,
        'count' => count($students)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
