<?php
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    // controller and model
    require_once __DIR__ . '/../../Models/Student.php';
    require_once __DIR__ . '/../../Controllers/StudentController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $admission_no = '';
    // Accept both form-encoded and JSON payloads
    if (!empty($_POST['admission_no'])) {
        $admission_no = trim($_POST['admission_no']);
    } else {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['admission_no'])) $admission_no = trim($data['admission_no']);
    }
    if (!$admission_no) throw new Exception('Admission number is required');

    $ctrl = new \App\Controllers\StudentController((int)$school_id);
    $affected = $ctrl->dropByAdmissionNo($admission_no);

    if ($affected) {
        echo json_encode(['success' => true, 'message' => 'Student dropped']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching student found or already dropped']);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
