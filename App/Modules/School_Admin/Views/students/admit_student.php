<?php
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../Models/Student.php';
    require_once __DIR__ . '/../../Controllers/StudentController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $student_id = null;
    if (!empty($_POST['student_id'])) {
        $student_id = (int)$_POST['student_id'];
    } else {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['student_id'])) $student_id = (int)$data['student_id'];
    }

    if (!$student_id) throw new Exception('Student id is required');

    $ctrl = new \App\Controllers\StudentController((int)$school_id);
    $affected = $ctrl->admitById($student_id);

    if ($affected) echo json_encode(['success' => true, 'message' => 'Student admitted']);
    else echo json_encode(['success' => false, 'message' => 'No matching student found or already active']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
