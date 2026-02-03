<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    require_once __DIR__ . '/../../../Models/ConcessionModel.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');
    $ad = trim($_GET['admission_no'] ?? '');
    if ($ad === '') throw new Exception('Missing admission_no');

    $db = \Database::connect();
    $model = new \App\Modules\School_Admin\Models\ConcessionModel($db);
    $student = $model->findStudentByAdmission($school_id, $ad);
    if (!$student) echo json_encode(['success'=>false,'message'=>'Student not found']);
    else echo json_encode(['success'=>true,'student'=>$student]);
} catch (Throwable $e) {
    $out = ob_get_clean(); http_response_code(500);
    $msg = $e->getMessage(); if ($out) $msg = $out."\n".$msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
