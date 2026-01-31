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

function json_success($id) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'id' => $id]);
    exit(0);
}

try {
    if (!isset($_SESSION['school_id'])) json_error('Unauthorized', 401);
    $school_id = $_SESSION['school_id'];

    $raw = file_get_contents('php://input');
    if (empty($raw)) json_error('Empty request');
    $data = json_decode($raw, true);
    if (!$data) json_error('Invalid JSON');

    $class_id = isset($data['class_id']) ? (int)$data['class_id'] : 0;
    $name = trim($data['name'] ?? '');
    $room = trim($data['room'] ?? '');
    $capacity = isset($data['capacity']) && $data['capacity'] !== '' ? (int)$data['capacity'] : null;
    $session_id = isset($data['session_id']) ? (int)$data['session_id'] : 0;

    if ($class_id <= 0) json_error('Invalid class id');
    if ($name === '') json_error('Section name is required');

    // load dependencies
    $autoloader = __DIR__ . '/../../../../../autoloader.php';
    if (!file_exists($autoloader)) json_error('Autoloader missing');
    require_once $autoloader;

    $db_file = __DIR__ . '/../../../../Core/database.php';
    if (!file_exists($db_file)) json_error('Database file missing');
    require_once $db_file;

    $db = \Database::connect();
    if (!$db) json_error('DB connect failed');

    // verify class belongs to school
    $st = $db->prepare('SELECT id FROM school_classes WHERE id = :id AND school_id = :sid LIMIT 1');
    $st->execute([':id' => $class_id, ':sid' => $school_id]);
    $found = $st->fetch(PDO::FETCH_ASSOC);
    if (!$found) json_error('Class not found or access denied', 403);

    // default session if not passed
    if (!$session_id) {
        // try to find active session
        $ss = $db->prepare('SELECT id FROM school_sessions WHERE school_id = :sid AND is_active = 1 LIMIT 1');
        $ss->execute([':sid' => $school_id]);
        $sres = $ss->fetch(PDO::FETCH_ASSOC);
        if ($sres) $session_id = (int)$sres['id'];
    }
    if (!$session_id) json_error('Session not specified');

    // create section using model
    if (!class_exists('\\App\\Modules\\School_Admin\\Models\\ClassSectionModel')) json_error('Model not found');
    $sectionModel = new \App\Modules\School_Admin\Models\ClassSectionModel($db);

    // generate section_code based on class_code + section name (ensure uniqueness)
    $clsStmt = $db->prepare('SELECT class_code, class_name FROM school_classes WHERE id = :id LIMIT 1');
    $clsStmt->execute([':id' => $class_id]);
    $cls = $clsStmt->fetch(PDO::FETCH_ASSOC);

    // helper slug
    $slugify = function($str) {
        $s = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($str)));
        $s = trim($s, '-');
        return $s ?: null;
    };

    $baseClassCode = null;
    if ($cls && !empty($cls['class_code'])) {
        $baseClassCode = $cls['class_code'];
    } elseif ($cls && !empty($cls['class_name'])) {
        $baseClassCode = $slugify($cls['class_name']);
    }
    if (!$baseClassCode) {
        $baseClassCode = 'class-' . $class_id;
    }

    $sectionSlug = $slugify($name) ?: 'sec';
    $candidate = $baseClassCode . '-' . $sectionSlug;

    // ensure uniqueness within school
    $i = 0;
    while (true) {
        $check = $db->prepare('SELECT id FROM school_class_sections WHERE section_code = :code AND school_id = :sid LIMIT 1');
        $check->execute([':code' => $candidate, ':sid' => $school_id]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);
        if (!$exists) break;
        $i++;
        $candidate = $baseClassCode . '-' . $sectionSlug . '-' . $i;
    }

    $section_code = $candidate;

    $id = $sectionModel->create($school_id, $session_id, $class_id, $name, $section_code, $room ?: null, $capacity, null, 'active');

    json_success($id);

} catch (Throwable $e) {
    json_error('Unexpected: ' . $e->getMessage(), 500);
}
