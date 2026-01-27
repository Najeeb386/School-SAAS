<?php
header('Content-Type: application/json');
session_start();

require_once '../../../../Config/connection.php';

if (!isset($_GET['plan_name'])) {
    echo json_encode(['success' => false, 'message' => 'Plan name not provided']);
    exit;
}

$planName = $_GET['plan_name'];

try {
    $stmt = $DB_con->prepare("SELECT * FROM plans WHERE name = ? LIMIT 1");
    $stmt->execute([$planName]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($plan) {
        echo json_encode([
            'success' => true,
            'plan' => $plan,
            'price' => $plan['price_per_student_year'] ?? $plan['price'] ?? 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
