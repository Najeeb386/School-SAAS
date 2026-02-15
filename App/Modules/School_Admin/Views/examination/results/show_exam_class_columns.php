<?php
header('Content-Type: application/json; charset=utf-8');

$appRoot = dirname(__DIR__, 4);
try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../../autoloader.php';
    require_once __DIR__ . '/../../../../../Core/database.php';
    $db = \Database::connect();

    $stmt = $db->prepare("SHOW COLUMNS FROM school_exam_classes");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'columns' => $cols], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>