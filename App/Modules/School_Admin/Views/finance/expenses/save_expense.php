<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../../../../autoloader.php';
require_once __DIR__ . '/../../../../../Config/connection.php';

use App\Modules\School_Admin\Controllers\ExpenseController;

// Database connection already initialized in connection.php
$db = $DB_con;

// Initialize controller
$controller = new ExpenseController($db);

$school_id = $_SESSION['school_id'] ?? null;
$session_id = $_SESSION['current_session_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$school_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid school context']);
    exit;
}

// If session_id is not in session, fetch the current active session from database
if (!$session_id) {
    $stmt = $db->prepare("SELECT id FROM school_sessions WHERE school_id = ? ORDER BY start_date DESC LIMIT 1");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $session_id = $result['id'] ?? null;
}

// Get request method
$request_method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

try {
    if ($request_method === 'POST') {
        $action = $_POST['action'] ?? 'create';
        
        switch ($action) {
            case 'create_category':
                $name = $_POST['name'] ?? '';
                if (empty($name)) {
                    echo json_encode(['success' => false, 'message' => 'Category name is required']);
                    exit;
                }

                $result = $controller->createCategory($school_id, $name);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Category created successfully', 'id' => $result]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create category']);
                }
                break;

            case 'create':
                $expense_data = [
                    'school_id' => $school_id,
                    'session_id' => $session_id,
                    'expense_category_id' => $_POST['expense_category_id'] ?? null,
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'vendor_name' => $_POST['vendor_name'] ?? '',
                    'invoice_no' => $_POST['invoice_no'] ?? '',
                    'amount' => $_POST['amount'] ?? 0,
                    'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                    'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'reference_no' => $_POST['reference_no'] ?? '',
                    'status' => $_POST['status'] ?? 'pending',
                    'created_by' => $user_id
                ];

                // Validate required fields
                if (empty($expense_data['title']) || empty($expense_data['amount']) || empty($expense_data['expense_category_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Title, Amount, and Category are required']);
                    exit;
                }

                $result = $controller->createFromRequest($expense_data);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Expense created successfully', 'id' => $result]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create expense']);
                }
                break;

            case 'update':
                $expense_id = $_POST['id'] ?? null;
                if (!$expense_id) {
                    echo json_encode(['success' => false, 'message' => 'Expense ID is required']);
                    exit;
                }

                $expense_data = [
                    'school_id' => $school_id,
                    'session_id' => $session_id,
                    'expense_category_id' => $_POST['expense_category_id'] ?? null,
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'vendor_name' => $_POST['vendor_name'] ?? '',
                    'invoice_no' => $_POST['invoice_no'] ?? '',
                    'amount' => $_POST['amount'] ?? 0,
                    'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                    'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'reference_no' => $_POST['reference_no'] ?? '',
                    'status' => $_POST['status'] ?? 'pending'
                ];

                // Validate required fields
                if (empty($expense_data['title']) || empty($expense_data['amount']) || empty($expense_data['expense_category_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Title, Amount, and Category are required']);
                    exit;
                }

                $result = $controller->updateFromRequest($expense_id, $school_id, $expense_data);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
                }
                break;

            case 'delete':
                $expense_id = $_POST['id'] ?? null;
                if (!$expense_id) {
                    echo json_encode(['success' => false, 'message' => 'Expense ID is required']);
                    exit;
                }

                $result = $controller->deleteFromRequest($expense_id, $school_id);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete expense']);
                }
                break;

            case 'approve':
                $expense_id = $_POST['id'] ?? null;
                $approval_notes = $_POST['approval_notes'] ?? '';

                if (!$expense_id) {
                    echo json_encode(['success' => false, 'message' => 'Expense ID is required']);
                    exit;
                }

                $result = $controller->updateStatus($expense_id, $school_id, 'approved', $user_id, $approval_notes);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Expense approved']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to approve expense']);
                }
                break;

            case 'reject':
                $expense_id = $_POST['id'] ?? null;
                $approval_notes = $_POST['approval_notes'] ?? '';

                if (!$expense_id) {
                    echo json_encode(['success' => false, 'message' => 'Expense ID is required']);
                    exit;
                }

                $result = $controller->updateStatus($expense_id, $school_id, 'rejected', $user_id, $approval_notes);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Expense rejected']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to reject expense']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
