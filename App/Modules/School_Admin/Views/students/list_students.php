<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $db = \Database::connect();

    $class_id = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $section_id = !empty($_GET['section_id']) ? (int)$_GET['section_id'] : null;
    $session_id = !empty($_GET['session_id']) ? (int)$_GET['session_id'] : null;
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT s.id, s.admission_no, s.first_name, s.last_name, s.father_names, s.father_contact, s.status,
        sc.class_name, sec.section_name,
        (SELECT g.name FROM school_student_guardians g WHERE g.student_id = s.id AND g.is_primary = 1 AND g.deleted_at IS NULL LIMIT 1) AS guardian_name,
        (SELECT d.file_path FROM school_student_documents d WHERE d.student_id = s.id AND d.doc_type = 'photo' AND d.deleted_at IS NULL ORDER BY d.id DESC LIMIT 1) AS photo
        FROM school_students s
        LEFT JOIN school_student_academics a ON a.student_id = s.id AND a.deleted_at IS NULL
        LEFT JOIN school_classes sc ON sc.id = a.class_id
        LEFT JOIN school_class_sections sec ON sec.id = a.section_id
        WHERE s.school_id = :sid AND s.deleted_at IS NULL";

    $params = [':sid' => $school_id];
    if ($class_id) { $sql .= ' AND a.class_id = :class_id'; $params[':class_id'] = $class_id; }
    if ($section_id) { $sql .= ' AND a.section_id = :section_id'; $params[':section_id'] = $section_id; }
    if ($session_id) { $sql .= ' AND a.session_id = :session_id'; $params[':session_id'] = $session_id; }
    if ($q) {
        $sql .= ' AND (s.admission_no LIKE :q OR s.first_name LIKE :q OR s.last_name LIKE :q OR s.id = :qid)';
        $params[':q'] = "%$q%";
        if (is_numeric($q)) $params[':qid'] = (int)$q; else $params[':qid'] = 0;
    }

    $sql .= ' ORDER BY s.id DESC LIMIT 500';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // normalize photo path for browser: make it relative to web root used by views
    foreach ($rows as &$r) {
        if (!empty($r['photo'])) {
            $p = $r['photo'];
            if (strpos($p, 'http://') === 0 || strpos($p, 'https://') === 0 || strpos($p, '/') === 0) {
                $r['photo'] = $p; // absolute
            } else {
                // prefix to reach project root from views path (same style used elsewhere)
                $r['photo'] = '../../../../../' . ltrim($p, './');
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    echo json_encode(['success' => false, 'message' => $msg]);
}
