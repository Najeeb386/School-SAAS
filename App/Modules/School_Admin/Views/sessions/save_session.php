<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Controllers/SessionController.php';
require_once __DIR__ . '/../../Models/SessionModel.php';

use App\Modules\School_Admin\Controllers\SessionController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: sessions.php'); exit; }

$ctrl = new SessionController($DB_con);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id > 0) {
    $ok = $ctrl->updateFromRequest();
    // controller sets flash messages
} else {
    $res = $ctrl->createFromRequest();
}

header('Location: sessions.php');
exit;

?>
