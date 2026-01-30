<?php
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Controllers/StaffSalaryController.php';
require_once __DIR__ . '/../../../Models/StaffSalaryModel.php';

use App\Modules\School_Admin\Controllers\StaffSalaryController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: salaries.php'); exit; }

$ctrl = new StaffSalaryController($DB_con);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id > 0) {
    $ok = $ctrl->updateFromRequest();
} else {
    $res = $ctrl->createFromRequest();
}

header('Location: salaries.php');
exit;
?>
