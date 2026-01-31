<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    require_once __DIR__ . '/../../../../Core/database.php';
    $db = \Database::connect();

    require_once __DIR__ . '/Models/ClassModel.php';
    require_once __DIR__ . '/Models/ClassSectionModel.php';
    require_once __DIR__ . '/Controllers/ClassController.php';

    $session_id = isset($data['session']) ? (int)$data['session'] : 0;
    if (!$session_id) {
        echo json_encode(['success' => false, 'message' => 'Session is required']);
        exit;
    }

    $controller = new \App\Modules\School_Admin\Controllers\ClassController($db);

    // Map input to expected structure
    $payload = [
        'class_name' => $data['name'] ?? '',
        'class_code' => $data['code'] ?? null,
        'class_order' => 0,
        'description' => $data['description'] ?? null,
        'sections' => []
    ];

    if (!empty($data['sections']) && is_array($data['sections'])) {
        foreach ($data['sections'] as $s) {
            $payload['sections'][] = [
                'name' => $s['name'] ?? null,
                'room' => $s['room'] ?? null,
                'capacity' => $s['capacity'] ?? null
            ];
        }
    }

    $class_id = $controller->createFromArray((int)$school_id, (int)$session_id, $payload);

    // fetch class to get class_code
    $classModel = new \App\Modules\School_Admin\Models\ClassModel($db);
    $record = $classModel->getById((int)$class_id);

    echo json_encode(['success' => true, 'id' => $class_id, 'class' => $record]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    error_log('ClassController::save_class error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit;
}
