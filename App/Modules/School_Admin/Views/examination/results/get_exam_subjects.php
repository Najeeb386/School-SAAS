<?php
/**
 * API Endpoint: Get Exam Subjects
 * Returns all subjects for a specific exam
 */

// Start session BEFORE sending headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../../autoloader.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    
    if (!$exam_id) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Exam ID is required'
        ]);
        exit;
    }

    $db = \Database::connect();
    
    // Query to get subjects for specific exam and class
    // Note: some DBs use `name` for subject title; use s.name AS subject_name to be compatible
    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;

    if ($class_id) {
        // If section provided, filter subjects assigned to that exam/class/section
        if ($section_id) {
            // Add uploaded count and total students so UI can decide which subjects are complete
            $hasMarksSchoolId = false;
            $colChk = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
            $colChk->execute(['school_exam_marks','school_id']);
            $hasMarksSchoolId = (bool)$colChk->fetchColumn();

            $sql = "SELECT eses.id, s.id AS subject_id, s.name AS subject_name, eses.total_marks,
                    (SELECT COUNT(DISTINCT m.student_id) FROM school_exam_marks m WHERE m.exam_subject_id = eses.id";
            if ($hasMarksSchoolId) $sql .= " AND m.school_id = ?";
            $sql .= ") AS uploaded_count,
                    (SELECT COUNT(*) FROM school_student_enrollments e WHERE e.class_id = ? AND e.section_id = ? AND e.school_id = ? AND e.deleted_at IS NULL) AS total_students
                FROM school_exam_subjects eses
                JOIN school_subjects s ON eses.subject_id = s.id
                JOIN school_exam_classes ec ON eses.exam_class_id = ec.id
                WHERE ec.exam_id = ? AND ec.class_id = ? AND ec.section_id = ? AND s.school_id = ?
                ORDER BY s.name ASC";
            $stmt = $db->prepare($sql);
            if ($hasMarksSchoolId) {
                $stmt->execute([$school_id, $class_id, $section_id, $school_id, $exam_id, $class_id, $section_id, $school_id]);
            } else {
                $stmt->execute([$class_id, $section_id, $school_id, $exam_id, $class_id, $section_id, $school_id]);
            }
        } else {
            // No section specified — provide uploaded_count but total_students will be NULL
            $hasMarksSchoolId = false;
            $colChk = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
            $colChk->execute(['school_exam_marks','school_id']);
            $hasMarksSchoolId = (bool)$colChk->fetchColumn();

            $sql = "SELECT eses.id, s.id AS subject_id, s.name AS subject_name, eses.total_marks,
                    (SELECT COUNT(DISTINCT m.student_id) FROM school_exam_marks m WHERE m.exam_subject_id = eses.id";
            if ($hasMarksSchoolId) $sql .= " AND m.school_id = ?";
            $sql .= ") AS uploaded_count,
                    NULL AS total_students
                FROM school_exam_subjects eses
                JOIN school_subjects s ON eses.subject_id = s.id
                JOIN school_exam_classes ec ON eses.exam_class_id = ec.id
                WHERE ec.exam_id = ? AND ec.class_id = ? AND s.school_id = ?
                ORDER BY s.name ASC";
            $stmt = $db->prepare($sql);
            if ($hasMarksSchoolId) {
                $stmt->execute([$school_id, $exam_id, $class_id, $school_id]);
            } else {
                $stmt->execute([$exam_id, $class_id, $school_id]);
            }
        }
    } else {
        // If no class specified, get all subjects for the exam
        $hasMarksSchoolId = false;
        $colChk = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $colChk->execute(['school_exam_marks','school_id']);
        $hasMarksSchoolId = (bool)$colChk->fetchColumn();

        $sql = "SELECT eses.id, s.id AS subject_id, s.name AS subject_name, eses.total_marks,
                (SELECT COUNT(DISTINCT m.student_id) FROM school_exam_marks m WHERE m.exam_subject_id = eses.id";
        if ($hasMarksSchoolId) $sql .= " AND m.school_id = ?";
        $sql .= ") AS uploaded_count,
                NULL AS total_students
            FROM school_exam_subjects eses
            JOIN school_subjects s ON eses.subject_id = s.id
            JOIN school_exam_classes ec ON eses.exam_class_id = ec.id
            WHERE ec.exam_id = ? AND s.school_id = ?
            ORDER BY s.name ASC";
        $stmt = $db->prepare($sql);
        if ($hasMarksSchoolId) {
            $stmt->execute([$school_id, $exam_id, $school_id]);
        } else {
            $stmt->execute([$exam_id, $school_id]);
        }
    }
    
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // compute uploaded_percent for each subject
    foreach ($subjects as &$sub) {
        $uploaded = isset($sub['uploaded_count']) ? (int)$sub['uploaded_count'] : 0;
        $total = isset($sub['total_students']) && $sub['total_students'] !== null ? (int)$sub['total_students'] : null;
        if ($total === null || $total === 0) {
            $sub['uploaded_percent'] = $total === 0 ? 100 : 0; // if total=0 treat as 100% to hide optionally
        } else {
            $sub['uploaded_percent'] = round(($uploaded / $total) * 100, 2);
        }
    }
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $subjects
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
