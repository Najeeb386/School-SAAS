<?php
/**
 * API Endpoint to save attendance for students
 */
ob_start();
session_start();
ob_clean();

// Set proper error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (empty($_SESSION['school_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception('Session expired or unauthorized access');
    }

    $school_id = $_SESSION['school_id'];
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    $class_id = $data['class_id'] ?? null;
    $section_id = $data['section_id'] ?? null;
    $attendance_date = $data['attendance_date'] ?? null;
    $attendance_records = $data['attendance'] ?? [];

    if (!$class_id || !$section_id || !$attendance_date || empty($attendance_records)) {
        throw new Exception('Missing required fields: class_id, section_id, attendance_date, or attendance records');
    }

    // Include database and model
    $dbPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, __DIR__ . '/../../../../../Core/database.php');
    if (!file_exists($dbPath)) {
        throw new Exception('Database file not found at: ' . $dbPath);
    }
    require_once $dbPath;
    
    // Model path: from Views/attendence/student_attendence/ go up 3 levels to School_Admin, then into Models
    $modelPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, __DIR__ . '/../../../Models/StudentAttendanceModel.php');
    if (!file_exists($modelPath)) {
        throw new Exception('Model file not found at: ' . $modelPath);
    }
    require_once $modelPath;
    
    $db = \Database::connect();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    
    $model = new \App\Modules\School_Admin\Models\StudentAttendanceModel($db);

    // Get current session
    $session = $model->getCurrentSession((int)$school_id);
    if (!$session) {
        throw new Exception('No active session found for this school');
    }

    $marked_by = $_SESSION['user_id'] ?? null;
    $saved_count = 0;
    $errors = [];

    // Save each attendance record
    foreach ($attendance_records as $student_id => $status) {
        if (in_array($status, ['P', 'A', 'L', 'HD'])) {
            try {
                $result = $model->saveAttendance(
                    (int)$school_id,
                    (int)$session['id'],
                    (int)$student_id,
                    (int)$class_id,
                    (int)$section_id,
                    $attendance_date,
                    $status,
                    null,
                    $marked_by
                );
                if ($result) {
                    $saved_count++;
                }
            } catch (Exception $e) {
                $errors[] = "Student ID {$student_id}: " . $e->getMessage();
            }
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Attendance saved for {$saved_count} student(s)",
        'saved_count' => $saved_count,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
