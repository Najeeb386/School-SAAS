<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Controllers/ConcessionController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\ConcessionController($db);

    $filters = [];
    if (!empty($_GET['session_id'])) $filters['session_id'] = (int)$_GET['session_id'];
    if (isset($_GET['status']) && $_GET['status'] !== '') $filters['status'] = (int)$_GET['status'];
    if (!empty($_GET['admission_no'])) $filters['admission_no'] = trim($_GET['admission_no']);

    $list = $controller->listConcessions($school_id, $filters);
    echo json_encode(['success'=>true,'concessions'=>$list]);
} catch (Throwable $e) {
    $out = ob_get_clean(); http_response_code(500);
    $msg = $e->getMessage(); if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
