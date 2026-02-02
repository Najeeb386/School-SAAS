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

    // fetch latest enrollment (not deleted)
        $sql = 'SELECT e.*, s.id AS session_id, c.class_name, sec.section_name
            FROM school_student_enrollments e
            LEFT JOIN school_sessions s ON s.id = e.session_id
            LEFT JOIN school_classes c ON c.id = e.class_id
            LEFT JOIN school_class_sections sec ON sec.id = e.section_id
            WHERE e.student_id = :sid AND e.school_id = :sch
            ORDER BY e.id DESC LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([':sid' => $student_id, ':sch' => $school_id]);
    $en = $stmt->fetch(PDO::FETCH_ASSOC);

    // if enrollment has a session_id, fetch session row and compute a friendly label
    if ($en && !empty($en['session_id'])) {
        $ss = $db->prepare('SELECT * FROM school_sessions WHERE id = :id LIMIT 1');
        $ss->execute([':id' => $en['session_id']]);
        $srow = $ss->fetch(PDO::FETCH_ASSOC);
        if ($srow) {
            if (!empty($srow['name'])) $en['session_label'] = $srow['name'];
            elseif (!empty($srow['title'])) $en['session_label'] = $srow['title'];
            elseif (!empty($srow['session'])) $en['session_label'] = $srow['session'];
            else $en['session_label'] = 'Session '.$srow['id'];
            $en['session_row'] = $srow;
        }
    }

    echo json_encode(['success' => true, 'enrollment' => $en]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success' => false, 'message' => $msg]);
}
