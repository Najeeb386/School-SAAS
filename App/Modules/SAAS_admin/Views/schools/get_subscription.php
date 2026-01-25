<?php
header('Content-Type: application/json');
session_start();

require_once '../../../../Config/connection.php';

if (!isset($_GET['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'School ID not provided']);
    exit;
}

$school_id = intval($_GET['school_id']);

try {
    $stmt = $DB_con->prepare("SELECT * FROM saas_school_subscriptions WHERE school_id = ? LIMIT 1");
    $stmt->execute([$school_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subscription) {
        echo json_encode(['success' => true, 'subscription' => $subscription]);
    } else {
        echo json_encode(['success' => true, 'subscription' => null, 'message' => 'No subscription found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
