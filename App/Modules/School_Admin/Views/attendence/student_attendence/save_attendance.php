<?php
/**
 * API Endpoint to save attendance for students
 */
session_start();

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (empty($_SESSION['school_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception('Unauthorized');
    }

    $school_id = $_SESSION['school_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Invalid request');
    }

    $class_id = $data['class_id'] ?? null;
    $section_id = $data['section_id'] ?? null;
    $attendance_date = $data['attendance_date'] ?? null;
    $attendance_records = $data['attendance'] ?? [];

    if (!$class_id || !$section_id || !$attendance_date || empty($attendance_records)) {
        throw new Exception('Missing required fields');
    }

    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../Models/StudentAttendanceModel.php';
    
    $db = \Database::connect();
    $model = new \App\Modules\School_Admin\Models\StudentAttendanceModel($db);

    // Get current session
    $session = $model->getCurrentSession((int)$school_id);
    if (!$session) {
        throw new Exception('No active session found');
    }

    $marked_by = $_SESSION['user_id'] ?? null;
    $saved_count = 0;

    // Save each attendance record
    foreach ($attendance_records as $student_id => $status) {
        if (in_array($status, ['P', 'A', 'L', 'HD'])) {
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
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Attendance saved for {$saved_count} students",
        'saved_count' => $saved_count
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
