<?php
/**
 * Payrun Actions Handler (Process, Approve, Pay)
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$action = isset($_POST['action']) ? sanitize($_POST['action']) : '';
$payrun_id = isset($_POST['payrun_id']) ? intval($_POST['payrun_id']) : 0;

$ctrl = new PayrunController($DB_con);
$result = ['success' => false, 'message' => 'Unknown action'];

if (!$payrun_id) {
    $_SESSION['flash_error'] = 'Invalid payrun ID';
    header('Location: payrun.php');
    exit;
}

switch ($action) {
    case 'process':
        $result = $ctrl->processPayrun($payrun_id);
        break;
    case 'approve':
        $result = $ctrl->approvePayrun($payrun_id);
        break;
    case 'pay':
        $result = $ctrl->payPayrun($payrun_id);
        break;
    default:
        $result = ['success' => false, 'message' => 'Invalid action'];
}

if ($result['success']) {
    $_SESSION['flash_success'] = $result['message'];
} else {
    $_SESSION['flash_error'] = $result['message'];
}

header('Location: payrun.php');
exit;

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
