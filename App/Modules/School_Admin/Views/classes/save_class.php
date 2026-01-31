<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Simple console-friendly error output
function json_error($msg) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit(0);
}

function json_success($class_id) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'id' => $class_id, 'message' => 'Class saved successfully']);
    exit(0);
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Log but don't throw - let try/catch handle real errors
    return true;
});

try {
    // Check auth
    if (!isset($_SESSION['school_id'])) {
        json_error('Unauthorized: No school_id in session');
    }
    
    $school_id = $_SESSION['school_id'];
    $raw = file_get_contents('php://input');
    
    if (empty($raw)) {
        json_error('Empty request body');
    }
    
    $data = json_decode($raw, true);
    if (!$data) {
        json_error('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    if (empty($data['name'])) {
        json_error('Class name is required');
    }
    if (empty($data['session'])) {
        json_error('Session is required');
    }
    if (empty($data['sections']) || !is_array($data['sections'])) {
        json_error('At least one section is required');
    }
    
    // Load dependencies - use correct relative paths to project root
    $autoloader = __DIR__ . '/../../../../../autoloader.php';
    if (!file_exists($autoloader)) {
        json_error('Autoloader not found at: ' . $autoloader);
    }
    
    require_once $autoloader;
    
    $db_file = __DIR__ . '/../../../../Core/database.php';
    if (!file_exists($db_file)) {
        json_error('Database file not found at: ' . $db_file);
    }
    
    require_once $db_file;
    
    // Connect to database
    try {
        $db = \Database::connect();
    } catch (Exception $e) {
        json_error('Database connection failed: ' . $e->getMessage());
    }
    
    if (!$db) {
        json_error('Failed to connect to database');
    }
    
    $session_id = (int)$data['session'];
    
    // Verify ClassController exists
    if (!class_exists('\App\Modules\School_Admin\Controllers\ClassController')) {
        json_error('ClassController not found in autoloader');
    }
    
    // Build payload
    $payload = [
        'class_name' => $data['name'] ?? '',
        'class_code' => $data['code'] ?? null,
        'grade_level' => $data['grade'] ?? '',
        'class_order' => 0,
        'description' => $data['description'] ?? null,
        'status' => 'active',
        'sections' => []
    ];
    
    // helper to create URL/code-safe slugs
    $slugify = function($str) {
        $s = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($str)));
        $s = trim($s, '-');
        return $s ?: 'item';
    };

    // Ensure base class code exists for section codes
    $baseClassCode = $payload['class_code'] ?? '';
    if (empty($baseClassCode)) {
        $baseClassCode = $slugify($payload['class_name']);
        $payload['class_code'] = $baseClassCode;
    }

    if (!empty($data['sections']) && is_array($data['sections'])) {
        foreach ($data['sections'] as $s) {
            $sectionName = $s['name'] ?? '';
            $sectionSlug = $slugify($sectionName ?: 'sec');
            $sectionCode = $baseClassCode . '-' . $sectionSlug;
            $payload['sections'][] = [
                'section_name' => $sectionName ?: null,
                'section_code' => $sectionCode,
                'room_number' => $s['room'] ?? null,
                'capacity' => (int)($s['capacity'] ?? 0),
                'class_teacher_id' => null
            ];
        }
    }
    
    try {
        $controller = new \App\Modules\School_Admin\Controllers\ClassController($db);
        $class_id = $controller->createFromArray($school_id, $session_id, $payload);
        json_success($class_id);
    } catch (Exception $e) {
        json_error('Save error: ' . $e->getMessage());
    }
    
} catch (Throwable $e) {
    json_error('Unexpected error: ' . $e->getMessage());
}
