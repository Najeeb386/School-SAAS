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

    // latest academic
    $astmt = $db->prepare('SELECT * FROM school_student_academics WHERE student_id = :id AND school_id = :sid AND deleted_at IS NULL ORDER BY id DESC LIMIT 1');
    $astmt->execute([':id'=>$id,':sid'=>$school_id]);
    $academic = $astmt->fetch(PDO::FETCH_ASSOC);

    // documents
    $dstmt = $db->prepare('SELECT id, doc_type, file_path, original_name, notes FROM school_student_documents WHERE student_id = :id AND school_id = :sid AND deleted_at IS NULL');
    $dstmt->execute([':id'=>$id,':sid'=>$school_id]);
    $docs = $dstmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success'=>true,'student'=>$student,'guardians'=>$guardians,'academic'=>$academic,'documents'=>$docs]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
