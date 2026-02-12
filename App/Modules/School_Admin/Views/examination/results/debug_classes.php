<?php
/**
 * Debug Classes API - Test Version
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json; charset=utf-8');

try {
    echo json_encode([
        'step1' => 'Script started',
        'cwd' => getcwd(),
        'file' => __FILE__,
        'dir' => __DIR__
    ]);
    die;

    $appRoot = dirname(__DIR__, 5);
    echo json_encode([
        'step2' => 'AppRoot calculated',
        'appRoot' => $appRoot
    ]);
    die;
    
    // Check if auth file exists
    $authFile = $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
    echo json_encode([
        'step3' => 'Auth file path',
        'authFile' => $authFile,
        'exists' => file_exists($authFile)
    ]);
    die;
    
    require_once $authFile;
    
    echo json_encode([
        'step4' => 'Auth check passed',
        'school_id' => $_SESSION['school_id'] ?? 'NOT SET'
    ]);
    die;
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    die;
}
?>
