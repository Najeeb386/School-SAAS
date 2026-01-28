<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Config/connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header('Location: employee.php'); exit; }

try {
    $stmt = $DB_con->prepare('DELETE FROM employees WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $_SESSION['flash_success'] = 'Employee deleted.';
} catch (PDOException $e) {
    $_SESSION['flash_error'] = 'DB error: ' . $e->getMessage();
}

header('Location: employee.php');
exit;

?>
