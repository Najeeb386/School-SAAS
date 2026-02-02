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
    $father_names = trim($_POST['father_names'] ?? '');
    $father_contact = trim($_POST['father_contact'] ?? '');
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

    $student_id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    if ($student_id) {
        // update existing student
        $ust = $db->prepare("UPDATE school_students SET admission_no = :admission_no, first_name = :first_name, last_name = :last_name, father_names = :father_names, father_contact = :father_contact, dob = :dob, gender = :gender, admission_date = :admission_date, religion = :religion, updated_at = NOW() WHERE id = :id AND school_id = :school_id");
        $ust->execute([
            'admission_no' => $admission_no,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'father_names' => $father_names,
            'father_contact' => $father_contact,
            'dob' => $dob,
            'gender' => $gender,
            'admission_date' => $admission_date,
            'religion' => $religion,
            'id' => $student_id,
            'school_id' => $school_id
        ]);
    } else {
        // Generate admission number atomically using school_admission_counters
        try {
            $startedTx = false;
            if (!$db->inTransaction()) {
                $db->beginTransaction();
                $startedTx = true;
            }

            // lock counter row for this school+session
            $sel = $db->prepare("SELECT last_number FROM school_admission_counters WHERE school_id = :sch AND session_id = :sess FOR UPDATE");
            $sel->execute(['sch' => $school_id, 'sess' => $enroll_session]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $newnum = (int)$row['last_number'] + 1;
                $upd = $db->prepare("UPDATE school_admission_counters SET last_number = :new WHERE school_id = :sch AND session_id = :sess");
                $upd->execute(['new' => $newnum, 'sch' => $school_id, 'sess' => $enroll_session]);
            } else {
                $newnum = 1;
                $ins = $db->prepare("INSERT INTO school_admission_counters (school_id, session_id, last_number) VALUES (:sch, :sess, :new)");
                $ins->execute(['sch' => $school_id, 'sess' => $enroll_session, 'new' => $newnum]);
            }

            // fetch school_code
            $scode = 'SCH';
            try {
                $sc = $db->prepare("SELECT school_code FROM schools WHERE id = :id LIMIT 1");
                $sc->execute(['id' => $school_id]);
                $srow = $sc->fetch(PDO::FETCH_ASSOC);
                if ($srow && !empty($srow['school_code'])) {
                    $scode = $srow['school_code'];
                }
            } catch (Exception $ex) {
                // ignore and use default
            }

            // derive session label (try common column names)
            $session_label = date('Y');
            if (!empty($enroll_session)) {
                try {
                    $ss = $db->prepare("SELECT name, session, title, id FROM school_sessions WHERE id = :id LIMIT 1");
                    $ss->execute(['id' => $enroll_session]);
                    $sessRow = $ss->fetch(PDO::FETCH_ASSOC);
                    if ($sessRow) {
                        if (!empty($sessRow['session'])) $session_label = $sessRow['session'];
                        elseif (!empty($sessRow['name'])) $session_label = $sessRow['name'];
                        elseif (!empty($sessRow['title'])) $session_label = $sessRow['title'];
                        else $session_label = (string)$sessRow['id'];
                    }
                } catch (Exception $ex) {
                    // ignore and fallback to year
                }
            }

            $admission_no = sprintf('%s-%s-%s', $scode, $session_label, str_pad((string)$newnum, 6, '0', STR_PAD_LEFT));

            // insert student with generated admission_no
            $stmt = $db->prepare("INSERT INTO school_students (school_id, admission_no, first_name, last_name, father_names, father_contact, dob, gender, admission_date, religion, created_at) VALUES (:school_id, :admission_no, :first_name, :last_name, :father_names, :father_contact, :dob, :gender, :admission_date, :religion, NOW())");
            $stmt->execute([
                'school_id' => $school_id,
                'admission_no' => $admission_no,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'father_names' => $father_names,
                'father_contact' => $father_contact,
                'dob' => $dob,
                'gender' => $gender,
                'admission_date' => $admission_date,
                'religion' => $religion
            ]);
            $student_id = (int)$db->lastInsertId();

            // commit counter + student insert; only commit if we started the transaction
            if ($startedTx && $db->inTransaction()) {
                $db->commit();
            }
        } catch (Exception $e) {
            if (isset($startedTx) && $startedTx && $db->inTransaction()) $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to create student (admission number generation): '.$e->getMessage()]);
            exit;
        }
    }

    // insert / update guardians: mark existing as deleted and re-insert (simple approach)
    $delg = $db->prepare("UPDATE school_student_guardians SET deleted_at = NOW() WHERE student_id = :student_id AND school_id = :school_id AND deleted_at IS NULL");
    $delg->execute(['student_id'=>$student_id,'school_id'=>$school_id]);
    $gstmt = $db->prepare("INSERT INTO school_student_guardians (student_id, school_id, name, relation, cnic_passport, occupation, mobile, address, is_primary, created_at) VALUES (:student_id, :school_id, :name, :relation, :cnic, :occupation, :mobile, :address, :is_primary, NOW())");
    if ($g1_name) {
        $gstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'name'=>$g1_name,'relation'=>$g1_relation,'cnic'=>$g1_cnic,'occupation'=>$g1_occupation,'mobile'=>$g1_mobile,'address'=>$g1_address,'is_primary'=>1]);
    }
    if ($g2_name) {
        $gstmt->execute(['student_id'=>$student_id,'school_id'=>$school_id,'name'=>$g2_name,'relation'=>$g2_relation,'cnic'=>$g2_cnic,'occupation'=>$g2_occupation,'mobile'=>null,'address'=>null,'is_primary'=>0]);
    }

    // insert academic record
    // handle academic: if updating, adjust previous enrollment counts
    if (!empty($_POST['id'])) {
        // find existing academic (most recent)
        $oldAstmt = $db->prepare("SELECT id, section_id FROM school_student_academics WHERE student_id = :sid AND school_id = :sch AND deleted_at IS NULL ORDER BY id DESC LIMIT 1");
        $oldAstmt->execute([':sid'=>$student_id,':sch'=>$school_id]);
        $oldA = $oldAstmt->fetch(PDO::FETCH_ASSOC);
        if ($oldA) {
            // mark old as deleted (simple history)
            $mark = $db->prepare("UPDATE school_student_academics SET deleted_at = NOW() WHERE id = :id");
            $mark->execute([':id'=>$oldA['id']]);
        }
    }

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

    // update section enrollment counts: decrement old, increment new
    if (!empty($_POST['id'])) {
        if (!empty($oldA['section_id']) && $oldA['section_id'] != $enroll_section) {
            $dec = $db->prepare("UPDATE school_class_sections SET current_enrollment = GREATEST(COALESCE(current_enrollment,0) - 1, 0) WHERE id = :sid AND school_id = :sch");
            $dec->execute(['sid'=>$oldA['section_id'],'sch'=>$school_id]);
        }
    }
    if (!empty($enroll_section)) {
        try {
            $u = $db->prepare("UPDATE school_class_sections SET current_enrollment = COALESCE(current_enrollment,0) + 1 WHERE id = :sid AND school_id = :sch");
            $u->execute(['sid' => $enroll_section, 'sch' => $school_id]);
        } catch (Exception $ee) {
            // non-fatal
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
