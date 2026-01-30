<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Controllers/TeacherController.php';
require_once __DIR__ . '/../../Models/TeacherModel.php';

use App\Modules\School_Admin\Controllers\TeacherController;

$ctrl = new TeacherController($DB_con);
$ctrl->updateFromRequest();
// redirect back to staff list
header('Location: staff.php');
exit;
