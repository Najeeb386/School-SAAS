<?php
header('Content-Type: application/json');
// capture any unexpected output so we can return it as JSON instead of raw HTML
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $action = trim($_POST['action'] ?? '') ;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    require_once __DIR__ . '/../../Models/FeeCategoryModel.php';
    $m = new FeeCategoryModel();

    if ($action === 'delete' && $id) {
        $deleted = $m->delete($id, $_SESSION['school_id']);
        $out = ob_get_clean();
        echo json_encode(['success' => $deleted>0, 'deleted' => (int)$deleted, 'warning'=> $out?:null]);
        exit;
    }

    if ($id) {
        if (!$name) throw new Exception('Name required');
        $affected = $m->update($id, ['school_id'=>$_SESSION['school_id'],'name'=>$name,'code'=>$code,'description'=>$desc,'status'=>$status]);
        $out = ob_get_clean();
        echo json_encode(['success'=>true,'updated'=>$affected,'warning'=>$out?:null]);
        exit;
    }

    // create
    if (!$name) throw new Exception('Name required');
    $newId = $m->create(['school_id'=>$_SESSION['school_id'],'name'=>$name,'code'=>$code,'description'=>$desc,'status'=>$status]);
    $out = ob_get_clean();
    if ($out) echo json_encode(['success'=>true,'id'=>$newId,'warning'=>substr($out,0,2000)]);
    else echo json_encode(['success'=>true,'id'=>$newId]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $message = $e->getMessage();
    if ($out) $message = $out . "\n" . $message;
    echo json_encode(['success'=>false,'message'=>$message]);
}
