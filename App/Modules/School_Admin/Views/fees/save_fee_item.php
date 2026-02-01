<?php
header('Content-Type: application/json');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';
    require_once __DIR__ . '/../../Models/FeeItemModel.php';

    $action = $_POST['action'] ?? '';
    $itemId = !empty($_POST['id']) ? (int)$_POST['id'] : null;

    // delete action
    if ($action === 'delete' && $itemId) {
        $m = new FeeItemModel();
        $deleted = $m->delete($itemId, $_SESSION['school_id']);
        $out = ob_get_clean();
        echo json_encode(['success' => (bool)$deleted, 'deleted' => $deleted, 'warning' => ($out?substr($out,0,2000):null)]);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    if (!$name) throw new Exception('Name required');
    $category = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.00;
    $billing_cycle = trim($_POST['billing_cycle'] ?? 'one_time');
    $code = trim($_POST['code'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    $m = new FeeItemModel();

    // update if id provided
    if ($itemId) {
        $updated = $m->update($itemId, [
            'school_id' => $_SESSION['school_id'],
            'category_id' => $category,
            'name' => $name,
            'code' => $code,
            'amount' => $amount,
            'billing_cycle' => $billing_cycle,
            'status' => $status
        ]);
        $out = ob_get_clean();
        echo json_encode(['success' => true, 'updated' => $updated, 'warning' => ($out?substr($out,0,2000):null)]);
        exit;
    }

    $id = $m->create([
        'school_id' => $_SESSION['school_id'],
        'category_id' => $category,
        'name' => $name,
        'code' => $code,
        'amount' => $amount,
        'billing_cycle' => $billing_cycle,
        'status' => $status
    ]);

    $out = ob_get_clean();
    if ($out) echo json_encode(['success'=>true,'id'=>$id,'warning'=>substr($out,0,2000)]);
    else echo json_encode(['success'=>true,'id'=>$id]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    echo json_encode(['success'=>false,'message'=>$msg]);
}
