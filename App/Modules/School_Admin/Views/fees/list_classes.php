<?php
header('Content-Type: application/json');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');
    $session_id = $_SESSION['current_session_id'] ?? $_SESSION['active_session_id'] ?? 0;

    $db = \Database::connect();
    $stmt = $db->prepare('SELECT id, class_name, class_code FROM school_classes WHERE school_id = :sid' . ($session_id ? ' AND session_id = :sess' : '') . ' ORDER BY class_name ASC');
    $params = [':sid' => $school_id];
    if ($session_id) $params[':sess'] = $session_id;
    $stmt->execute($params);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch sections for classes
    $classIds = array_column($classes, 'id');
    $sections = [];
    if (!empty($classIds)) {
        $in = implode(',', array_map('intval', $classIds));
        $sstmt = $db->prepare("SELECT id, class_id, section_name FROM school_class_sections WHERE class_id IN ($in) ORDER BY section_name ASC");
        $sstmt->execute();
        $secs = $sstmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($secs as $s) {
            $sections[$s['class_id']][] = $s;
        }
    }

    // attach sections
    foreach ($classes as &$c) {
        $c['sections'] = $sections[$c['id']] ?? [];
    }

    echo json_encode(['success' => true, 'data' => $classes]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    http_response_code(500);
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    echo json_encode(['success' => false, 'message' => $msg]);
}
