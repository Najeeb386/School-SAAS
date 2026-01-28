<?php
/**
 * School Admin Authentication Check
 * Include this file at the top of all School Admin protected pages
 * Usage: require_once __DIR__ . '/../../../Config/auth_check_school_admin.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/SecurityConfig.php';

// Check if user is logged in as School Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not authenticated
    header('Location: /School-SAAS/App/Modules/Auth/login.php');
    exit;
}

// Verify this is a School Admin session (not SAAS Admin)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'school') {
    // Invalid session type, redirect to login
    session_destroy();
    header('Location: /School-SAAS/App/Modules/Auth/login.php?invalid=1');
    exit;
}

// Check if session has required data
if (!isset($_SESSION['school_id']) || !isset($_SESSION['email'])) {
    // Invalid session, redirect to login
    session_destroy();
    header('Location: /School-SAAS/App/Modules/Auth/login.php?invalid=1');
    exit;
}

// Check session timeout (30 minutes = 1800 seconds)
$SESSION_TIMEOUT = 1800;
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > $SESSION_TIMEOUT) {
        // Session expired
        session_destroy();
        header('Location: /School-SAAS/App/Modules/Auth/login.php?expired=1');
        exit;
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// School Admin session is valid
?>
