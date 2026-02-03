<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Models/ConcessionModel.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized: No school_id in session');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid method: ' . $_SERVER['REQUEST_METHOD']);

    $db = \Database::connect();
    $model = new \App\Modules\School_Admin\Models\ConcessionModel($db);

    $action = trim($_POST['action'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    error_log("Concession action: action=$action, id=$id, school_id=$school_id");

    if ($id <= 0) throw new Exception('Invalid concession ID: ' . $id);
    if (empty($action)) throw new Exception('Missing action parameter');

    // Verify concession belongs to this school
    $stmt = $db->prepare('SELECT * FROM school_student_fees_concessions WHERE id = :id AND school_id = :sid LIMIT 1');
    $stmt->execute([':id' => $id, ':sid' => $school_id]);
    $concession = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$concession) throw new Exception('Concession not found for id=' . $id . ' and school_id=' . $school_id);

    if ($action === 'extend') {
        $newEndMonth = trim($_POST['end_month'] ?? '');
        if (empty($newEndMonth)) throw new Exception('New end month is required');
        
        // Validate DATE format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newEndMonth)) {
            throw new Exception('Invalid date format: ' . $newEndMonth . ' (expected YYYY-MM-DD)');
        }
        
        // Update end_month
        $upd = $db->prepare('UPDATE school_student_fees_concessions SET end_month = :end_month, updated_at = NOW() WHERE id = :id');
        $upd->execute([':end_month' => $newEndMonth, ':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Concession extended']);
    } elseif ($action === 'cancel') {
        // Update status to 0 (inactive)
        $upd = $db->prepare('UPDATE school_student_fees_concessions SET status = 0, updated_at = NOW() WHERE id = :id');
        $upd->execute([':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Concession cancelled']);
    } else {
        throw new Exception('Invalid action: ' . $action);
    }

} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(400);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    error_log("Concession action error: " . $msg);
    echo json_encode(['success' => false, 'message' => $msg]);
}

