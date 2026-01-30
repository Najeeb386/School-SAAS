<?php
/**
 * Payrun Save Handler
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$ctrl = new PayrunController($DB_con);

$result = $ctrl->createFromRequest();

$_SESSION['flash_success'] = $result['message'];
if (!$result['success']) {
    $_SESSION['flash_error'] = $result['message'];
}

header('Location: payrun.php');
exit;
