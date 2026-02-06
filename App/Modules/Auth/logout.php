<?php
/**
 * School SAAS - Logout Handler
 * Destroys user session and redirects to login page
 */

require_once __DIR__ . '/../../Config/SecurityConfig.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If SAAS admin logout (saas session present), clear only SAAS-specific keys
if (isset($_SESSION['saas_logged_in']) && $_SESSION['saas_logged_in'] === true) {
    SecurityConfig::logSecurityEvent('saas_logout', [
        'saas_admin_id' => $_SESSION['saas_admin_id'] ?? null,
        'email' => $_SESSION['saas_email'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);

    // Unset SAAS-specific session keys
    unset($_SESSION['saas_logged_in'], $_SESSION['saas_admin_id'], $_SESSION['saas_email'], $_SESSION['saas_user_type'], $_SESSION['saas_last_activity']);

    // Redirect to SAAS login
    header('Location: /School-SAAS/App/Modules/Auth/login.php');
    exit;
}

// Log logout event for school session if present
if (isset($_SESSION['school_id'])) {
    SecurityConfig::logSecurityEvent('school_logout', [
        'school_id' => $_SESSION['school_id'],
        'email' => $_SESSION['email'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Clear entire session (school logout)
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie
setcookie('school_email', '', time() - 3600, '/');

// Redirect to login
header('Location: /School-SAAS/App/Modules/Auth/login.php');
exit;
