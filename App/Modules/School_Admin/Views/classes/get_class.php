<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function json_error($msg, $code = 400) {
    while (ob_get_level()) ob_end_clean();
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit(0);
}

try {
    if (!isset($_SESSION['school_id'])) json_error('Unauthorized', 401);
    $school_id = $_SESSION['school_id'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) json_error('Invalid class id');

    $autoloader = __DIR__ . '/../../../../../autoloader.php';
    if (!file_exists($autoloader)) json_error('Autoloader missing');
    require_once $autoloader;

    $db_file = __DIR__ . '/../../../../Core/database.php';
    if (!file_exists($db_file)) json_error('Database file not found');
    require_once $db_file;

    $db = \Database::connect();
    if (!$db) json_error('DB connect failed');

    if (!class_exists('\\App\\Modules\\School_Admin\\Models\\ClassModel')) json_error('ClassModel not found');
    if (!class_exists('\\App\\Modules\\School_Admin\\Models\\ClassSectionModel')) json_error('ClassSectionModel not found');

    $classModel = new \App\Modules\School_Admin\Models\ClassModel($db);
    $sectionModel = new \App\Modules\School_Admin\Models\ClassSectionModel($db);

    $class = $classModel->getById($id);
    if (!$class || (int)$class['school_id'] !== (int)$school_id) json_error('Class not found', 404);

    $sections = $sectionModel->getByClassId($id);

    echo json_encode(['success' => true, 'class' => $class, 'sections' => $sections]);
    exit(0);
} catch (Throwable $e) {
    json_error('Unexpected: ' . $e->getMessage(), 500);
}
