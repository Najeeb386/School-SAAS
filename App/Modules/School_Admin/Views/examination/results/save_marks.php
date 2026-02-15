<?php
/**
 * API: save_marks.php
 * Accepts JSON POST: { exam_id, exam_subject_id, marks: [{student_id, obtained_marks, is_absent, remarks}, ...] }
 */
header('Content-Type: application/json; charset=utf-8');

// Require auth and database
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../../autoloader.php';
require_once __DIR__ . '/../../../../../Core/database.php';
require_once __DIR__ . '/../../../Controllers/ExamMarksController.php';

// Ensure POST JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$exam_id = isset($input['exam_id']) ? (int)$input['exam_id'] : 0;
$exam_subject_id = isset($input['exam_subject_id']) ? (int)$input['exam_subject_id'] : 0;
$marks = isset($input['marks']) && is_array($input['marks']) ? $input['marks'] : [];

if (!$exam_id || !$exam_subject_id || empty($marks)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'exam_id, exam_subject_id and marks are required']);
    exit;
}

// Instantiate controller and save
$ctrl = new ExamMarksController();
$result = $ctrl->saveMarks($exam_id, $exam_subject_id, $marks);

if ($result['success']) {
    echo json_encode(['success' => true, 'message' => $result['message']]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}

?>
