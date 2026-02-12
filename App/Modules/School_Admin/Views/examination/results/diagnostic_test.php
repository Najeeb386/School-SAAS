<?php
/**
 * Diagnostic test for classes API
 * This version bypasses auth to help debug the issue
 */
header('Content-Type: application/json; charset=utf-8');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Path resolution
$test1 = [
    'name' => 'Path Resolution',
    'passed' => false,
    'details' => []
];

$appRoot = dirname(__DIR__, 5);
$test1['details']['calculated_appRoot'] = $appRoot;
$test1['details']['appRoot_exists'] = is_dir($appRoot);
$test1['passed'] = $test1['details']['appRoot_exists'];

// Test 2: Check auth file
$test2 = [
    'name' => 'Auth Check File',
    'passed' => false,
    'details' => []
];

$authPath = $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
$test2['details']['auth_file_path'] = $authPath;
$test2['details']['auth_file_exists'] = file_exists($authPath);
$test2['passed'] = $test2['details']['auth_file_exists'];

// Test 3: Check database file
$test3 = [
    'name' => 'Database Class File',
    'passed' => false,
    'details' => []
];

$dbPath = $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
$test3['details']['db_file_path'] = $dbPath;
$test3['details']['db_file_exists'] = file_exists($dbPath);
$test3['passed'] = $test3['details']['db_file_exists'];

// Test 4: Check autoloader
$test4 = [
    'name' => 'Autoloader',
    'passed' => false,
    'details' => []
];

$projectRoot = dirname($appRoot);
$autoloaderPath = $projectRoot . DIRECTORY_SEPARATOR . 'autoloader.php';
$test4['details']['autoloader_path'] = $autoloaderPath;
$test4['details']['autoloader_exists'] = file_exists($autoloaderPath);
$test4['passed'] = $test4['details']['autoloader_exists'];

// Test 5: Check logs directory
$test5 = [
    'name' => 'Logs Directory',
    'passed' => false,
    'details' => []
];

$logsDir = $appRoot . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'logs';
$test5['details']['logs_dir_path'] = $logsDir;
$test5['details']['logs_dir_exists'] = is_dir($logsDir);
$test5['details']['logs_dir_writable'] = is_writable($logsDir);
$test5['passed'] = $test5['details']['logs_dir_exists'] && $test5['details']['logs_dir_writable'];

// Test 6: Try loading classes (without auth)
$test6 = [
    'name' => 'Database Query Test',
    'passed' => false,
    'details' => []
];

try {
    // Try to load the database class
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
    
    // Try to connect (this will use the config)
    $db = \Database::connect();
    $test6['details']['db_connection'] = 'Success';
    
    // Try to run a simple query
    $stmt = $db->prepare("SELECT 1");
    $stmt->execute();
    $test6['details']['simple_query'] = 'Success';
    
    // Check if classes table exists
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM school_exam_classes");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $test6['details']['exam_classes_table'] = 'Exists, count: ' . $result['count'];
    
    $test6['passed'] = true;
} catch (Exception $e) {
    $test6['details']['error'] = $e->getMessage();
    $test6['passed'] = false;
}

$diagnostics['tests'][] = $test1;
$diagnostics['tests'][] = $test2;
$diagnostics['tests'][] = $test3;
$diagnostics['tests'][] = $test4;
$diagnostics['tests'][] = $test5;
$diagnostics['tests'][] = $test6;
$diagnostics['all_passed'] = $test1['passed'] && $test2['passed'] && $test3['passed'] && $test4['passed'] && $test5['passed'];

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
