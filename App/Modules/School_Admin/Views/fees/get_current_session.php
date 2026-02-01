<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $db = \Database::connect();
    // try to get active session
    $stmt = $db->prepare('SELECT id, name FROM school_sessions WHERE school_id = :sid AND is_active = 1 AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':sid' => $school_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode(['success' => true, 'id' => (int)$row['id'], 'name' => $row['name']]);
        exit;
    }

    // fallback: most recent session
    $stmt = $db->prepare('SELECT id, name FROM school_sessions WHERE school_id = :sid AND deleted_at IS NULL ORDER BY id DESC LIMIT 1');
    $stmt->execute([':sid' => $school_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) echo json_encode(['success' => true, 'id' => (int)$row['id'], 'name' => $row['name']]);
    else echo json_encode(['success' => false, 'message' => 'No session found']);
} catch (Throwable $e) {
    $out = ob_get_clean();
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $msg]);
}
