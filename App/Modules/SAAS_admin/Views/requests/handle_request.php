<?php
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Models/requests_model.php';

header('Content-Type: application/json');

$requestsModel = new Requests($DB_con);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['action']) || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $requestId = intval($data['id']);
        $action = $data['action'];
        $rejectionReason = isset($data['reason']) ? $data['reason'] : null;

        if ($action === 'approve') {
            // Create school from request
            $schoolCreated = $requestsModel->createSchoolFromRequest($requestId);
            
            if ($schoolCreated) {
                // Update request status to approved
                $result = $requestsModel->updateStatus($requestId, 'approved');
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Request approved and school account created successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'School created but failed to update request status']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create school account']);
            }
        } elseif ($action === 'reject') {
            if (!$rejectionReason) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
                exit;
            }
            $result = $requestsModel->updateStatus($requestId, 'rejected', $rejectionReason);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to reject request']);
            }
        } elseif ($action === 'reconsider') {
            $result = $requestsModel->updateStatus($requestId, 'pending', null);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Request moved back to pending']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to reconsider request']);
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
