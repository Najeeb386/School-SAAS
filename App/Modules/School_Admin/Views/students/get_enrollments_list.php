<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $student_id = !empty($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
    if (!$student_id) throw new Exception('Missing student id');

    $db = \Database::connect();

    // optional filters
    $filter_session = !empty($_GET['session_id']) ? (int)$_GET['session_id'] : null;
    $filter_class = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $filter_section = !empty($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    $filter_status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null;

    $where = 'e.student_id = :sid AND e.school_id = :sch AND e.deleted_at IS NULL';
    $params = [':sid' => $student_id, ':sch' => $school_id];
    if ($filter_session) { $where .= ' AND e.session_id = :sess'; $params[':sess'] = $filter_session; }
    if ($filter_class) { $where .= ' AND e.class_id = :cid'; $params[':cid'] = $filter_class; }
    if ($filter_section) { $where .= ' AND e.section_id = :sec'; $params[':sec'] = $filter_section; }
    if ($filter_status) { $where .= ' AND e.status = :st'; $params[':st'] = $filter_status; }

    $sql = 'SELECT e.*, s.id AS session_id, s.name AS session_name, c.class_name, sec.section_name FROM school_student_enrollments e LEFT JOIN school_sessions s ON s.id = e.session_id LEFT JOIN school_classes c ON c.id = e.class_id LEFT JOIN school_class_sections sec ON sec.id = e.section_id WHERE ' . $where . ' ORDER BY e.admission_date DESC, e.id DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // counts
    $countSql = 'SELECT COUNT(*) AS total,
        SUM(CASE WHEN LOWER(e.status) LIKE "%pass%" OR LOWER(e.status) = "graduated" THEN 1 ELSE 0 END) AS passedout,
        SUM(CASE WHEN LOWER(e.status) LIKE "%promot%" THEN 1 ELSE 0 END) AS promoted
        FROM school_student_enrollments e WHERE e.student_id = :sid AND e.school_id = :sch AND e.deleted_at IS NULL';
    $cstmt = $db->prepare($countSql);
    $cstmt->execute([':sid'=>$student_id, ':sch'=>$school_id]);
    $counts = $cstmt->fetch(PDO::FETCH_ASSOC);

    // lists for filters (sessions, classes, sections)
    $sessStmt = $db->prepare('SELECT id, COALESCE(name, title, session, CONCAT("Session ", id)) AS label FROM school_sessions WHERE school_id = :sch ORDER BY id DESC');
    $sessStmt->execute([':sch'=>$school_id]);
    $sessions = $sessStmt->fetchAll(PDO::FETCH_ASSOC);

    $classStmt = $db->prepare('SELECT id, class_name FROM school_classes WHERE school_id = :sch ORDER BY class_name ASC');
    $classStmt->execute([':sch'=>$school_id]);
    $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);

    $secStmt = $db->prepare('SELECT id, section_name FROM school_class_sections WHERE school_id = :sch ORDER BY section_name ASC');
    $secStmt->execute([':sch'=>$school_id]);
    $sections = $secStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success'=>true,'enrollments'=>$enrollments,'counts'=>$counts,'sessions'=>$sessions,'classes'=>$classes,'sections'=>$sections]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
