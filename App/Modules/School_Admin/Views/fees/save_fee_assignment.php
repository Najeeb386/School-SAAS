<?php
header('Content-Type: application/json');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $action = trim($_POST['action'] ?? '');
    $assignment_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $fee_item_id = isset($_POST['fee_item_id']) ? (int)$_POST['fee_item_id'] : 0;
    $assign_to = $_POST['assign_to'] ?? 'class';
    $target = $_POST['target'] ?? '';
    $session_id = isset($_POST['session_id']) ? (int)$_POST['session_id'] : ($_SESSION['current_session_id'] ?? $_SESSION['active_session_id'] ?? 0);
    $amount = isset($_POST['amount']) && $_POST['amount'] !== '' ? (float)$_POST['amount'] : null;
    $due_day = isset($_POST['due_day']) ? (int)$_POST['due_day'] : 10;

    if (!$fee_item_id) throw new Exception('Fee item required');
    if (!$session_id) throw new Exception('Session required');

    $class_id = null; $section_id = null; $student_id = null;
    if ($assign_to === 'class') {
        $class_id = (int)$target;
    } else if ($assign_to === 'section') {
        $section_id = (int)$target;
    } else if ($assign_to === 'student') {
        $student_id = (int)$target;
    }

    $db = \Database::connect();

    // delete
    if ($action === 'delete' && $assignment_id) {
        // ensure belongs to school
        $check = $db->prepare('SELECT id FROM schoo_fee_assignments WHERE id = :id AND school_id = :sid LIMIT 1');
        $check->execute([':id' => $assignment_id, ':sid' => $school_id]);
        if (!$check->fetch()) throw new Exception('Assignment not found');
        $d = $db->prepare('DELETE FROM schoo_fee_assignments WHERE id = :id AND school_id = :sid');
        $d->execute([':id' => $assignment_id, ':sid' => $school_id]);
        echo json_encode(['success' => true, 'deleted' => $d->rowCount()]);
        exit;
    }

    // update
    if ($assignment_id) {
        // ensure belongs to school
        $check = $db->prepare('SELECT id FROM schoo_fee_assignments WHERE id = :id AND school_id = :sid LIMIT 1');
        $check->execute([':id' => $assignment_id, ':sid' => $school_id]);
        if (!$check->fetch()) throw new Exception('Assignment not found');

        $upd = $db->prepare('UPDATE schoo_fee_assignments SET fee_item_id = :fi, class_id = :cid, section_id = :sec, student_id = :stu, session_id = :sess, amount = :amt, due_day = :due, updated_at = NOW() WHERE id = :id AND school_id = :sid');
        $upd->execute([
            ':fi' => $fee_item_id,
            ':cid' => $class_id,
            ':sec' => $section_id,
            ':stu' => $student_id,
            ':sess' => $session_id,
            ':amt' => $amount,
            ':due' => $due_day,
            ':id' => $assignment_id,
            ':sid' => $school_id
        ]);
        echo json_encode(['success' => true, 'updated' => $upd->rowCount()]);
        exit;
    }

    // create
    $stmt = $db->prepare('INSERT INTO schoo_fee_assignments (school_id, fee_item_id, class_id, section_id, student_id, session_id, amount, due_day, created_at) VALUES (:sid, :fi, :cid, :sec, :stu, :sess, :amt, :due, NOW())');
    $stmt->execute([
        ':sid' => $school_id,
        ':fi' => $fee_item_id,
        ':cid' => $class_id,
        ':sec' => $section_id,
        ':stu' => $student_id,
        ':sess' => $session_id,
        ':amt' => $amount,
        ':due' => $due_day
    ]);

    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    echo json_encode(['success' => false, 'message' => $msg]);
}
