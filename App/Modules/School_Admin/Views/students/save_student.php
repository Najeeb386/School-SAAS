<?php
header('Content-Type: application/json');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $db = \Database::connect();
    // collect basic fields
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    if (!$first_name) throw new Exception('First name is required');

    $admission_no = trim($_POST['admission_no'] ?? null);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = trim($_POST['gender'] ?? null);
    $admission_date = !empty($_POST['admission_date']) ? $_POST['admission_date'] : null;
    $religion = trim($_POST['religion'] ?? null);

    // guardians
    $g1_name = trim($_POST['guardian_name'] ?? '');
    $g1_relation = trim($_POST['guardian_relation'] ?? '');
    $g1_cnic = trim($_POST['guardian_cnic'] ?? '');
    $g1_occupation = trim($_POST['guardian_occupation'] ?? '');
    $g1_mobile = trim($_POST['guardian_mobile'] ?? '');
    $g1_address = trim($_POST['guardian_address'] ?? '');

    $g2_name = trim($_POST['guardian2_name'] ?? '');
    $g2_relation = trim($_POST['guardian2_relation'] ?? '');
    $g2_cnic = trim($_POST['guardian2_cnic'] ?? '');
    $g2_occupation = trim($_POST['guardian2_occupation'] ?? '');

    // academic
    $enroll_class = !empty($_POST['enroll_class']) ? (int)$_POST['enroll_class'] : null;
    $enroll_section = !empty($_POST['enroll_section']) ? (int)$_POST['enroll_section'] : null;
    $enroll_session = !empty($_POST['enroll_session']) ? (int)$_POST['enroll_session'] : null;
    $is_transferred = (isset($_POST['transferred']) && $_POST['transferred'] === 'yes') ? 1 : 0;
    $prev_school = trim($_POST['prev_school'] ?? null);
    $prev_class = trim($_POST['prev_class'] ?? null);
    $prev_adm_no = trim($_POST['prev_adm_no'] ?? null);
    $prev_result = trim($_POST['prev_result'] ?? null);

    // start transaction
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO school_students (school_id, admission_no, first_name, last_name, dob, gender, admission_date, religion, created_at) VALUES (:school_id, :admission_no, :first_name, :last_name, :dob, :gender, :admission_date, :religion, NOW())");
    $stmt->execute([
        'school_id' => $school_id,
        'admission_no' => $admission_no,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'dob' => $dob,
        'gender' => $gender,
        'admission_date' => $admission_date,
        'religion' => $religion
    ]);
    $student_id = (int)$db->lastInsertId();

    // insert guardians
    $gstmt = $db->prepare("INSERT INTO school_student_guardians (student_id, school_id, name, relation, cnic_passport, occupation, mobile, address, is_primary, created_at) VALUES (:student_id, :school_id, :name, :relation, :cnic, :occupation, :mobile, :address, :is_primary, NOW())");
    if ($g1_name) {
        $gstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'name'=>$g1_name,'relation'=>$g1_relation,'cnic'=>$g1_cnic,'occupation'=>$g1_occupation,'mobile'=>$g1_mobile,'address'=>$g1_address,'is_primary'=>1]);
    }
    if ($g2_name) {
        $gstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'name'=>$g2_name,'relation'=>$g2_relation,'cnic'=>$g2_cnic,'occupation'=>$g2_occupation,'mobile'=>null,'address'=>null,'is_primary'=>0]);
    }

    // insert academic record
    $astmt = $db->prepare("INSERT INTO school_student_academics (student_id, school_id, session_id, class_id, section_id, is_transferred, previous_school, previous_class, previous_admission_no, previous_result, enrolled_at, created_at) VALUES (:student_id, :school_id, :session_id, :class_id, :section_id, :is_transferred, :previous_school, :previous_class, :previous_admission_no, :previous_result, NOW(), NOW())");
    $astmt->execute([
        'student_id' => $student_id,
        'school_id' => $school_id,
        'session_id' => $enroll_session,
        'class_id' => $enroll_class,
        'section_id' => $enroll_section,
        'is_transferred' => $is_transferred,
        'previous_school' => $prev_school,
        'previous_class' => $prev_class,
        'previous_admission_no' => $prev_adm_no,
        'previous_result' => $prev_result
    ]);

    // update section enrollment count if section provided
    if (!empty($enroll_section)) {
        try {
            $u = $db->prepare("UPDATE school_class_sections SET current_enrollment = COALESCE(current_enrollment,0) + 1 WHERE id = :sid AND school_id = :sch");
            $u->execute(['sid' => $enroll_section, 'sch' => $school_id]);
        } catch (Exception $ee) {
            // non-fatal - record warning in output
            // continue
        }
    }

    // handle file uploads
    $base_upload_dir = __DIR__ . '/../../../../../Storage/uploads/schools/';
    if (!is_dir($base_upload_dir)) mkdir($base_upload_dir, 0755, true);
    $school_dir = $base_upload_dir . 'school_' . $school_id . '/';
    if (!is_dir($school_dir)) mkdir($school_dir, 0755, true);
    $student_dir = $school_dir . 'students/' . $student_id . '/';
    if (!is_dir($student_dir)) mkdir($student_dir, 0755, true);

    $dstmt = $db->prepare("INSERT INTO school_student_documents (student_id, school_id, doc_type, file_path, original_name, notes, uploaded_at, created_at) VALUES (:student_id, :school_id, :doc_type, :file_path, :original_name, :notes, NOW(), NOW())");

    $savedFiles = [];

    // helper to save one file
    $saveOne = function($fileField, $docType) use (&$savedFiles, $student_dir, $student_id, $school_id, $dstmt) {
        if (empty($_FILES[$fileField]) || !isset($_FILES[$fileField]['tmp_name'])) return null;
        $file = $_FILES[$fileField];
        if ($file['error']) return null;
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeDoc = preg_replace('/[^a-z0-9_\-]/i', '_', $docType);
        $newName = $school_id . '_' . $safeDoc . '_' . $student_id . '.' . $ext;
        $target = $student_dir . $newName;
        if (!move_uploaded_file($file['tmp_name'], $target)) return null;
        $webPath = 'Storage/uploads/schools/school_' . $school_id . '/students/' . $student_id . '/' . $newName;
        $dstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'doc_type'=>$docType,'file_path'=>$webPath,'original_name'=>$file['name'],'notes'=>null]);
        $savedFiles[] = $webPath;
        return $webPath;
    };

    // photo
    $saveOne('doc_photo','photo');
    $saveOne('doc_guardian_cnic','guardian_cnic');
    $saveOne('doc_birth_cert','birth_certificate');

    // other multiple
    if (!empty($_FILES['doc_other'])) {
        $files = $_FILES['doc_other'];
        for ($i=0;$i < count($files['name']); $i++) {
            if ($files['error'][$i]) continue;
            $tmpName = $files['tmp_name'][$i];
            $orig = $files['name'][$i];
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $safeDoc = 'other' . ($i+1);
            $newName = $school_id . '_' . $safeDoc . '_' . $student_id . '.' . $ext;
            $target = $student_dir . $newName;
            if (!move_uploaded_file($tmpName, $target)) continue;
            $webPath = 'Storage/uploads/schools/school_' . $school_id . '/students/' . $student_id . '/' . $newName;
            $dstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'doc_type'=>'other','file_path'=>$webPath,'original_name'=>$orig,'notes'=>null]);
            $savedFiles[] = $webPath;
        }
    }

    $db->commit();

    $out = ob_get_clean();
    echo json_encode(['success'=>true,'student_id'=>$student_id,'files'=>$savedFiles,'warning'=>($out?substr($out,0,2000):null)]);
    exit;

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
