<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Controllers/ConcessionController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid method');

    $class_id = (int)($_POST['class_id'] ?? 0);
    $session_id = (int)($_POST['session_id'] ?? 0);
    if (!$class_id || !$session_id) throw new Exception('Missing class or session');

    $data = [
        'session_id' => $session_id,
        'type' => $_POST['type'] ?? 'discount',
        'value_type' => $_POST['value_type'] ?? 'fixed',
        'value' => $_POST['value'] ?? 0,
        'applies_to' => $_POST['applies_to'] ?? 'tuition_only',
        'start_month' => $_POST['start_month'] ?? null,
        'end_month' => $_POST['end_month'] ?? null,
        'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1
    ];

    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\ConcessionController($db);
    $count = $controller->applyBulk($school_id, $session_id, $class_id, $data);
    echo json_encode(['success'=>true,'applied'=>$count]);
} catch (Throwable $e) {
    $out = ob_get_clean(); http_response_code(500);
    $msg = $e->getMessage(); if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
