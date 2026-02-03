<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
 try {
     require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
     require_once __DIR__ . '/../../../../../Core/database.php';
     require_once __DIR__ . '/../../../Models/ConcessionModel.php';
     require_once __DIR__ . '/../../../Controllers/ConcessionController.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) throw new Exception('Unauthorized');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid method');
    // small debug logger to capture request/response for troubleshooting duplicate alerts
    $logFile = __DIR__ . '/../../../../../Storage/logs/concession_debug.log';
    $log = function($label, $payload) use ($logFile) {
        $entry = date('Y-m-d H:i:s') . " | " . ($label ?? '') . " | ";
        $entry .= json_encode([ 'remote' => $_SERVER['REMOTE_ADDR'] ?? 'cli', 'payload' => $payload ], JSON_UNESCAPED_UNICODE);
        $entry .= PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    };

    $db = \Database::connect();
    $controller = new \App\Modules\School_Admin\Controllers\ConcessionController($db);

    // Helper: convert month input (YYYY-MM) to DATE format (YYYY-MM-01), validate format
    $normMonth = function($val) {
        if (empty($val)) return null;
        $s = trim($val);
        // HTML month input returns YYYY-MM format (7 chars)
        if (strlen($s) === 7 && preg_match('/^\d{4}-\d{2}$/', $s)) {
            // append -01 to make it a valid DATE: YYYY-MM-01
            return $s . '-01';
        }
        return null; // invalid format
    };

    $start_month = $normMonth($_POST['start_month'] ?? '');
    $end_month = $normMonth($_POST['end_month'] ?? '');

    $data = [
        'school_id' => $school_id,
        'admission_no' => trim($_POST['admission_no'] ?? ''),
        'session_id' => (int)($_POST['session_id'] ?? 0),
        'type' => $_POST['type'] ?? 'discount',
        'value_type' => $_POST['value_type'] ?? 'fixed',
        'value' => $_POST['value'] ?? 0,
        'applies_to' => $_POST['applies_to'] ?? 'tuition_only',
        'start_month' => $start_month,
        'end_month' => $end_month,
        'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1
    ];

    // log incoming request
    $log('incoming_save', $data);

    $id = $controller->saveConcession($data);

    $resp = ['success' => true, 'id' => $id];
    // log response
    $log('saved', $resp);
    echo json_encode($resp);
} catch (Throwable $e) {
    $out = ob_get_clean(); http_response_code(500);
    $msg = $e->getMessage(); if ($out) $msg = $out."\n".$msg;
    // log error
    if (isset($log)) $log('error', ['message' => $msg]);
    echo json_encode(['success'=>false,'message'=>$msg]);
}
