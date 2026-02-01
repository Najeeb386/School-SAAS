<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../Models/FeeCategoryModel.php';
try {
    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');
    $m = new FeeCategoryModel();
    $rows = $m->getAllBySchool($school_id);
    header('Content-Type: application/json');
    echo json_encode(['success'=>true,'data'=>$rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
