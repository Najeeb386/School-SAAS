<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
    require_once __DIR__ . '/../../../../../Core/database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $db = \Database::connect();
    $action = trim($_POST['action'] ?? '');
    $invoice_id = intval($_POST['id'] ?? 0);

    if (!$action || !$invoice_id) {
        throw new Exception('Invalid request');
    }

    // Verify invoice belongs to school
    $stmtCheck = $db->prepare('SELECT id FROM schoo_fee_invoices WHERE id = :id AND school_id = :sid');
    $stmtCheck->execute([':id' => $invoice_id, ':sid' => $school_id]);
    
    if ($stmtCheck->rowCount() === 0) {
        throw new Exception('Invoice not found or unauthorized');
    }

    if ($action === 'mark_paid') {
        $stmt = $db->prepare('
            UPDATE schoo_fee_invoices SET status = :status, updated_at = NOW()
            WHERE id = :id AND school_id = :sid
        ');
        
        $stmt->execute([
            ':status' => 'paid',
            ':id' => $invoice_id,
            ':sid' => $school_id
        ]);

        echo json_encode(['success' => true, 'message' => 'Invoice marked as paid']);
    }
    elseif ($action === 'delete') {
        // Delete line items
        $stmtItems = $db->prepare('DELETE FROM schoo_fee_invoice_items WHERE invoice_id = :id');
        $stmtItems->execute([':id' => $invoice_id]);

        // Delete invoice
        $stmt = $db->prepare('DELETE FROM schoo_fee_invoices WHERE id = :id AND school_id = :sid');
        $stmt->execute([':id' => $invoice_id, ':sid' => $school_id]);

        echo json_encode(['success' => true, 'message' => 'Invoice deleted']);
    }
    else {
        throw new Exception('Unknown action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
