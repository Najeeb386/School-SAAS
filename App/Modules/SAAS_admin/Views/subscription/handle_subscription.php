<?php
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Models/subscription_model.php';

header('Content-Type: application/json');

$subscriptionModel = new Subscription($DB_con);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['action']) || !isset($data['schoolId'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $schoolId = intval($data['schoolId']);
        $action = $data['action'];

        if ($action === 'renew') {
            $result = $subscriptionModel->renewSubscription($schoolId, $_POST['plan'] ?? 'annually');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Subscription renewed successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to renew subscription']);
            }
        } elseif ($action === 'extend') {
            if (!isset($data['days'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Number of days is required']);
                exit;
            }
            
            $days = intval($data['days']);
            if ($days < 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Days must be greater than 0']);
                exit;
            }

            $result = $subscriptionModel->extendSubscription($schoolId, $days);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Subscription extended successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to extend subscription']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
