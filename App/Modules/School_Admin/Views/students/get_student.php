<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
    if (!$id) throw new Exception('Missing student id');

    $db = \Database::connect();

    // student
    $stmt = $db->prepare('SELECT * FROM school_students WHERE id = :id AND school_id = :sid AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id'=>$id,':sid'=>$school_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) throw new Exception('Student not found');

    // guardians
    $gstmt = $db->prepare('SELECT id, name, relation, cnic_passport, occupation, mobile, address, is_primary FROM school_student_guardians WHERE student_id = :id AND school_id = :sid AND deleted_at IS NULL ORDER BY is_primary DESC, id ASC');
    $gstmt->execute([':id'=>$id,':sid'=>$school_id]);
    $guardians = $gstmt->fetchAll(PDO::FETCH_ASSOC);

    // latest academic (with IDs only for session) - avoid selecting unknown session columns
    $astmt = $db->prepare('SELECT a.*, s.id AS session_id, c.class_name, sec.section_name
        FROM school_student_academics a
        LEFT JOIN school_sessions s ON s.id = a.session_id
        LEFT JOIN school_classes c ON c.id = a.class_id
        LEFT JOIN school_class_sections sec ON sec.id = a.section_id
        WHERE a.student_id = :id AND a.school_id = :sid AND a.deleted_at IS NULL ORDER BY a.id DESC LIMIT 1');
    $astmt->execute([':id'=>$id,':sid'=>$school_id]);
    $academic = $astmt->fetch(PDO::FETCH_ASSOC);

    // latest enrollment (if any) - avoid selecting unknown session columns
    $enstmt = $db->prepare('SELECT e.*, s.id AS session_id, c.class_name, sec.section_name FROM school_student_enrollments e LEFT JOIN school_sessions s ON s.id = e.session_id LEFT JOIN school_classes c ON c.id = e.class_id LEFT JOIN school_class_sections sec ON sec.id = e.section_id WHERE e.student_id = :id AND e.school_id = :sid ORDER BY e.id DESC LIMIT 1');
    $enstmt->execute([':id'=>$id,':sid'=>$school_id]);
    $enrollment = $enstmt->fetch(PDO::FETCH_ASSOC);

    // fetch session rows and compute friendly labels if possible
    if ($academic && !empty($academic['session_id'])) {
        $ss = $db->prepare('SELECT * FROM school_sessions WHERE id = :id LIMIT 1');
        $ss->execute([':id' => $academic['session_id']]);
        $srow = $ss->fetch(PDO::FETCH_ASSOC);
        if ($srow) {
            if (!empty($srow['name'])) $academic['session_label'] = $srow['name'];
            elseif (!empty($srow['title'])) $academic['session_label'] = $srow['title'];
            elseif (!empty($srow['session'])) $academic['session_label'] = $srow['session'];
            else $academic['session_label'] = 'Session '.$srow['id'];
            $academic['session_row'] = $srow;
        }
    }
    if ($enrollment && !empty($enrollment['session_id'])) {
        $es = $db->prepare('SELECT * FROM school_sessions WHERE id = :id LIMIT 1');
        $es->execute([':id' => $enrollment['session_id']]);
        $srow2 = $es->fetch(PDO::FETCH_ASSOC);
        if ($srow2) {
            if (!empty($srow2['name'])) $enrollment['session_label'] = $srow2['name'];
            elseif (!empty($srow2['title'])) $enrollment['session_label'] = $srow2['title'];
            elseif (!empty($srow2['session'])) $enrollment['session_label'] = $srow2['session'];
            else $enrollment['session_label'] = 'Session '.$srow2['id'];
            $enrollment['session_row'] = $srow2;
        }
    }

    // documents
    $dstmt = $db->prepare('SELECT id, doc_type, file_path, original_name, notes FROM school_student_documents WHERE student_id = :id AND school_id = :sid AND deleted_at IS NULL');
    $dstmt->execute([':id'=>$id,':sid'=>$school_id]);
    $docs = $dstmt->fetchAll(PDO::FETCH_ASSOC);

    // subject assignments for student's current class/session/section
    $subjects = [];
    $class_id = $enrollment['class_id'] ?? $academic['class_id'] ?? null;
    $section_id = $enrollment['section_id'] ?? $academic['section_id'] ?? null;
    $session_for_assign = $enrollment['session_id'] ?? $academic['session_id'] ?? null;
    if ($class_id) {
        $subSql = 'SELECT a.*, s.name AS subject_name, t.name AS teacher_name FROM school_subject_assignments a JOIN school_subjects s ON a.subject_id = s.id LEFT JOIN school_teachers t ON a.teacher_id = t.id WHERE a.school_id = :sid AND a.class_id = :cid';
        $params = [':sid' => $school_id, ':cid' => $class_id];
        if ($session_for_assign) {
            $subSql .= ' AND a.session_id = :sess';
            $params[':sess'] = $session_for_assign;
        }
        if ($section_id) {
            $subSql .= ' AND (a.section_id = :sec OR a.section_id IS NULL)';
            $params[':sec'] = $section_id;
        }
        $subSql .= ' ORDER BY s.name ASC';
        $sstmt = $db->prepare($subSql);
        $sstmt->execute($params);
        $subjects = $sstmt->fetchAll(PDO::FETCH_ASSOC);
        // if no assignments found for the specific session/section, try a broader query by class only
        if (empty($subjects)) {
            $broaderSql = 'SELECT a.*, s.name AS subject_name, t.name AS teacher_name FROM school_subject_assignments a JOIN school_subjects s ON a.subject_id = s.id LEFT JOIN school_teachers t ON a.teacher_id = t.id WHERE a.school_id = :sid AND a.class_id = :cid ORDER BY s.name ASC';
            $bs = $db->prepare($broaderSql);
            $bs->execute([':sid'=>$school_id, ':cid'=>$class_id]);
            $subjects = $bs->fetchAll(PDO::FETCH_ASSOC);
        }
        // if still empty, as a last resort list all subjects defined for the school
        if (empty($subjects)) {
            $allSql = 'SELECT s.id AS subject_id, s.name AS subject_name, t.name AS teacher_name FROM school_subjects s LEFT JOIN school_teachers t ON s.teacher_id = t.id WHERE s.school_id = :sid AND s.deleted_at IS NULL ORDER BY s.name ASC';
            $as = $db->prepare($allSql);
            $as->execute([':sid'=>$school_id]);
            $subjects = $as->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    echo json_encode(['success'=>true,'student'=>$student,'guardians'=>$guardians,'academic'=>$academic,'enrollment'=>$enrollment,'documents'=>$docs,'subjects'=>$subjects]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
