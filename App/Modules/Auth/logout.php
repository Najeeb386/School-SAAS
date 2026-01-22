<?php
session_start();

// Include auth controller and database
require_once __DIR__ . '/controller/authcontroller.php';
require_once __DIR__ . '/../../Config/database.php';
require_once __DIR__ . '/../../Core/database.php';

// Get database connection
$db = Database::connect();
$authController = new AuthController($db);

// Logout
$result = $authController->logout();

// Redirect to login
header('Location: /School-SAAS/App/Modules/Auth/login.php');
exit;
?>
