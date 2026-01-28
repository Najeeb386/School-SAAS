<?php
/**
 * SAAS Admin Authentication Check
 * Include this file at the top of all SAAS Admin protected pages
 * Usage: require_once __DIR__ . '/../../../Config/auth_check_saas_admin.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/SecurityConfig.php';

// Check SAAS-specific session keys to isolate from school admin sessions
if (!isset($_SESSION['saas_logged_in']) || $_SESSION['saas_logged_in'] !== true) {
    header('Location: /School-SAAS/App/Modules/Auth/saas-login.php');
    exit;
}

// Verify this is a SAAS Admin session (not School Admin)
if (!isset($_SESSION['saas_user_type']) || $_SESSION['saas_user_type'] !== 'saas_admin') {
    session_destroy();
    header('Location: /School-SAAS/App/Modules/Auth/saas-login.php?invalid=1');
    exit;
}

// Check if session has required data
if (!isset($_SESSION['saas_admin_id']) || !isset($_SESSION['saas_email'])) {
    session_destroy();
    header('Location: /School-SAAS/App/Modules/Auth/saas-login.php?invalid=1');
    exit;
}

// Check SAAS session timeout (separate key to avoid interfering with school sessions)
$SAAS_SESSION_TIMEOUT = 1800;
if (isset($_SESSION['saas_last_activity'])) {
    $elapsed = time() - $_SESSION['saas_last_activity'];
    if ($elapsed > $SAAS_SESSION_TIMEOUT) {
        session_destroy();
        header('Location: /School-SAAS/App/Modules/Auth/saas-login.php?expired=1');
        exit;
    }
}

// Update SAAS last activity timestamp
$_SESSION['saas_last_activity'] = time();

// SAAS Admin session is valid (isolated keys)
?>
