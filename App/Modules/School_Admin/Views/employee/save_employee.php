<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Config/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: employee.php'); exit; }

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role_value = isset($_POST['role_id']) ? trim($_POST['role_id']) : '';
$permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? array_values($_POST['permissions']) : [];

// Ensure we have the current school id from session
$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
if ($school_id <= 0) { $_SESSION['flash_error'] = 'Invalid school context.'; header('Location: employee.php'); exit; }

if ($name === '') { $_SESSION['flash_error'] = 'Name is required.'; header('Location: employee.php'); exit; }

$perms = array_values(array_unique(array_filter(array_map('strval', $permissions))));
$permsJson = json_encode($perms);

try {
    if ($id > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $DB_con->prepare('UPDATE employees SET name=:name,email=:email,password=:pass,role_id=:role,permissions=:perms,updated_at=NOW() WHERE id=:id AND school_id=:school_id');
            $stmt->execute([':name'=>$name,':email'=>$email,':pass'=>$hash,':role'=>$role_value,':perms'=>$permsJson,':id'=>$id, ':school_id'=>$school_id]);
        } else {
            $stmt = $DB_con->prepare('UPDATE employees SET name=:name,email=:email,role_id=:role,permissions=:perms,updated_at=NOW() WHERE id=:id AND school_id=:school_id');
            $stmt->execute([':name'=>$name,':email'=>$email,':role'=>$role_value,':perms'=>$permsJson,':id'=>$id, ':school_id'=>$school_id]);
        }
        $_SESSION['flash_success'] = 'Employee updated.';
    } else {
        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(5)), PASSWORD_DEFAULT);
        $stmt = $DB_con->prepare('INSERT INTO employees (school_id,name,email,password,role_id,permissions,created_at,updated_at) VALUES (:school_id,:name,:email,:pass,:role,:perms,NOW(),NOW())');
        $stmt->execute([':school_id'=>$school_id, ':name'=>$name,':email'=>$email,':pass'=>$hash,':role'=>$role_value,':perms'=>$permsJson]);
        $_SESSION['flash_success'] = 'Employee created.';
    }
} catch (PDOException $e) {
    $_SESSION['flash_error'] = 'DB error: ' . $e->getMessage();
}

header('Location: employee.php');
exit;

?>
