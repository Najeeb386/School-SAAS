<?php
header('Content-Type: application/json');
session_start();

require_once '../../../../Config/connection.php';
require_once '../../Models/school_subscription_model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $schoolId = intval($_POST['school_id'] ?? 0);
    $billingStudents = intval($_POST['billing_students'] ?? 0);
    $pricePerStudent = floatval($_POST['price_per_student'] ?? 0);
    $billingCycle = $_POST['billing_cycle'] ?? 'yearly';
    $discountType = $_POST['discount_type'] ?? 'none';
    $discountValue = floatval($_POST['discount_value'] ?? 0);
    $paidAmount = floatval($_POST['paid_amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? '';
    $referenceNo = $_POST['reference_no'] ?? '';
    $receivedBy = $_POST['received_by'] ?? '';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $notes = $_POST['notes'] ?? '';

    if (!$schoolId || !$billingStudents || !$pricePerStudent || !$paymentMethod) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Start transaction
    $DB_con->beginTransaction();

    // Get school details
    $schoolStmt = $DB_con->prepare("SELECT * FROM schools WHERE id = ?");
    $schoolStmt->execute([$schoolId]);
    $school = $schoolStmt->fetch(PDO::FETCH_ASSOC);

    if (!$school) {
        $DB_con->rollBack();
        echo json_encode(['success' => false, 'message' => 'School not found']);
        exit;
    }

    // Get current subscription
    $subStmt = $DB_con->prepare("SELECT * FROM saas_school_subscriptions WHERE school_id = ? LIMIT 1");
    $subStmt->execute([$schoolId]);
    $subscription = $subStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate amounts
    $totalAmount = $billingStudents * $pricePerStudent;
    $discountAmount = 0;
    
    if ($discountType === 'percentage' && $discountValue > 0) {
        $discountAmount = ($totalAmount * $discountValue / 100);
    } elseif ($discountType === 'fixed' && $discountValue > 0) {
        $discountAmount = $discountValue;
    }
    
    $finalAmount = $totalAmount - $discountAmount;
    $dueAmount = $finalAmount - $paidAmount;

    // Determine payment status
    $paymentStatus = 'due';
    if ($paidAmount >= $finalAmount) {
        $paymentStatus = 'paid';
    } elseif ($paidAmount > 0 && $paidAmount < $finalAmount) {
        $paymentStatus = 'partial';
    }

    // Calculate new end date based on billing cycle
    $today = new DateTime();
    $newEndDate = new DateTime();
    
    switch ($billingCycle) {
        case 'monthly':
            $newEndDate->modify('+1 month');
            break;
        case 'quarterly':
            $newEndDate->modify('+3 months');
            break;
        case 'semi-annual':
            $newEndDate->modify('+6 months');
            break;
        case 'yearly':
            $newEndDate->modify('+1 year');
            break;
        default:
            $newEndDate->modify('+1 year');
    }

    // Update school expires_at
    $updateSchoolStmt = $DB_con->prepare("
        UPDATE schools SET expires_at = ?, updated_at = ? WHERE id = ?
    ");
    $updateSchoolStmt->execute([$newEndDate->format('Y-m-d'), date('Y-m-d H:i:s'), $schoolId]);

    // Update or create subscription
    if ($subscription) {
        // Update existing subscription
        $subscriptionModel = new SchoolSubscription($DB_con);
        $subData = [
            'plan_name' => $school['plan'],
            'price_per_student' => $pricePerStudent,
            'students_count' => $billingStudents,
            'billing_cycle' => $billingCycle,
            'start_date' => date('Y-m-d'),
            'end_date' => $newEndDate->format('Y-m-d'),
            'status' => 'active'
        ];
        $subscriptionModel->update($schoolId, $subData);
        $subscriptionId = $subscription['subscription_id'];
    } else {
        // Create new subscription if doesn't exist
        $subscriptionModel = new SchoolSubscription($DB_con);
        $subData = [
            'school_id' => $schoolId,
            'plan_name' => $school['plan'],
            'price_per_student' => $pricePerStudent,
            'students_count' => $billingStudents,
            'billing_cycle' => $billingCycle,
            'start_date' => date('Y-m-d'),
            'end_date' => $newEndDate->format('Y-m-d'),
            'status' => 'active'
        ];
        $subscriptionModel->create($subData);
        $subscriptionId = $DB_con->lastInsertId();
    }

    // Calculate due date
    $dueDate = (clone $today)->modify('+30 days');

    // Insert billing cycle record
    $billingStmt = $DB_con->prepare("
        INSERT INTO saas_billing_cycles 
        (school_id, subscription_id, period_start, period_end, due_date, total_amount, discounted_amount, paid_amount, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $billingStmt->execute([
        $schoolId,
        $subscriptionId,
        $today->format('Y-m-d'),
        $newEndDate->format('Y-m-d'),
        $dueDate->format('Y-m-d'),
        $totalAmount,
        $discountAmount,
        $paidAmount,
        $paymentStatus,
        date('Y-m-d H:i:s')
    ]);

    $billingId = $DB_con->lastInsertId();

    // Insert payment record if there's a paid amount
    if ($paidAmount > 0) {
        $paymentStmt = $DB_con->prepare("
            INSERT INTO saas_payments
            (billing_id, school_id, total_amount, paid_amount, payment_date, payment_method, reference_no, received_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $paymentStmt->execute([
            $billingId,
            $schoolId,
            $finalAmount,
            $paidAmount,
            $paymentDate,
            $paymentMethod,
            $referenceNo,
            $receivedBy,
            date('Y-m-d H:i:s')
        ]);
    }

    // Commit transaction
    $DB_con->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Subscription renewed successfully!',
        'total_amount' => $totalAmount,
        'discount_amount' => $discountAmount,
        'final_amount' => $finalAmount,
        'paid_amount' => $paidAmount,
        'due_amount' => $dueAmount,
        'payment_status' => $paymentStatus,
        'new_end_date' => $newEndDate->format('Y-m-d')
    ]);

} catch (Exception $e) {
    $DB_con->rollBack();
    error_log('Renewal error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
