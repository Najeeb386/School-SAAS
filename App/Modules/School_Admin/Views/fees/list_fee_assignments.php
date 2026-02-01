<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
try {
    require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    $db = \Database::connect();
    $sql = "SELECT a.id, a.fee_item_id, a.class_id, a.section_id, a.student_id, a.session_id, a.amount, a.due_day,
        fi.name AS fee_item_name, fi.amount AS fee_item_default_amount, c.class_name, s.section_name, se.name AS session_name
        FROM schoo_fee_assignments a
        LEFT JOIN schoo_fee_items fi ON fi.id = a.fee_item_id
        LEFT JOIN school_classes c ON c.id = a.class_id
        LEFT JOIN school_class_sections s ON s.id = a.section_id
        LEFT JOIN school_sessions se ON se.id = a.session_id
        WHERE a.school_id = :sid
        ORDER BY a.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':sid' => $school_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Throwable $e) {
    $out = ob_get_clean();
    $msg = $e->getMessage();
    if ($out) $msg = $out . "\n" . $msg;
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $msg]);
}
