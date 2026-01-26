<?php
// Initialize database connection
require_once __DIR__ . '/../../../../Core/database.php';
require_once __DIR__ . '/../../Controllers/billing_cycles_controller.php';

$db = Database::connect();
$billingController = new BillingCyclesController($db);

// Determine which action we're handling
$action = $_POST['action'] ?? null;

// Handle block school action FIRST (before any other processing)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'block_school') {
    try {
        $schoolId = intval($_POST['school_id'] ?? 0);
        
        if (!$schoolId) {
            throw new Exception('School ID is required');
        }
        
        error_log("===== BLOCK SCHOOL ACTION =====");
        error_log("School ID: " . $schoolId);
        error_log("POST action: " . $_POST['action']);
        
        // Verify school exists
        $verifyQuery = "SELECT id, name, status FROM schools WHERE id = ?";
        $verifyStmt = $db->prepare($verifyQuery);
        $verifyStmt->execute([$schoolId]);
        $schoolData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schoolData) {
            throw new Exception('School not found with ID: ' . $schoolId);
        }
        
        error_log("School found: " . $schoolData['name'] . " | Current status: " . $schoolData['status']);
        
        // Execute direct SQL update without prepared statement to ensure it executes
        $updateSQL = "UPDATE schools SET status = 'blocked' WHERE id = " . $schoolId;
        error_log("Executing SQL: " . $updateSQL);
        
        $rowsAffected = $db->exec($updateSQL);
        error_log("Rows affected: " . $rowsAffected);
        
        if ($rowsAffected === false) {
            throw new Exception('Database update failed');
        }
        
        // Verify update
        $verifyStmt2 = $db->prepare("SELECT id, name, status FROM schools WHERE id = ?");
        $verifyStmt2->execute([$schoolId]);
        $updatedSchool = $verifyStmt2->fetch(PDO::FETCH_ASSOC);
        
        error_log("After update - Status: " . $updatedSchool['status']);
        
        if ($updatedSchool['status'] !== 'blocked') {
            throw new Exception('Status update verification failed. Status is: ' . $updatedSchool['status']);
        }
        
        error_log("✓ School successfully blocked!");
        
        // Return success response
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'School "' . $schoolData['name'] . '" has been successfully blocked!'
        ]);
        exit;
    } catch (Exception $e) {
        error_log("ERROR blocking school: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle clear balance action (clear all billing cycles for a school)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_balance') {
    try {
        $schoolId = intval($_POST['school_id'] ?? 0);
        
        if (!$schoolId) {
            throw new Exception('School ID is required');
        }
        
        error_log("===== CLEAR BALANCE ACTION =====");
        error_log("School ID: " . $schoolId);
        
        // Set all billing cycles to paid for this school
        $clearSQL = "UPDATE saas_billing_cycles SET paid_amount = total_amount, status = 'paid' WHERE school_id = " . $schoolId;
        error_log("Executing SQL: " . $clearSQL);
        
        $rowsAffected = $db->exec($clearSQL);
        error_log("Rows affected: " . $rowsAffected);
        
        // Return success response
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Balance cleared successfully for this school!'
        ]);
        exit;
    } catch (Exception $e) {
        error_log("ERROR clearing balance: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle unblock school action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unblock_school') {
    try {
        $schoolId = intval($_POST['school_id'] ?? 0);
        
        if (!$schoolId) {
            throw new Exception('School ID is required');
        }
        
        error_log("===== UNBLOCK SCHOOL ACTION =====");
        error_log("School ID: " . $schoolId);
        
        // Get school info first
        $verifyStmt = $db->prepare("SELECT id, name, status FROM schools WHERE id = ?");
        $verifyStmt->execute([$schoolId]);
        $schoolData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schoolData) {
            throw new Exception('School not found');
        }
        
        // Update status to active
        $unblockSQL = "UPDATE schools SET status = 'active' WHERE id = " . $schoolId;
        error_log("Executing SQL: " . $unblockSQL);
        
        $rowsAffected = $db->exec($unblockSQL);
        error_log("Rows affected: " . $rowsAffected);
        
        if ($rowsAffected === false) {
            throw new Exception('Database update failed');
        }
        
        error_log("✓ School successfully unblocked!");
        
        // Return success response
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'School "' . $schoolData['name'] . '" has been successfully unblocked!'
        ]);
        exit;
    } catch (Exception $e) {
        error_log("ERROR unblocking school: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_payment') {
    try {
        $billingId = intval($_POST['billing_id'] ?? 0);
        $schoolId = intval($_POST['school_id'] ?? 0);
        $paidAmount = floatval($_POST['paid_amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? null;
        $referenceNo = $_POST['reference_no'] ?? '';
        $receivedBy = $_POST['received_by'] ?? '';

        if (!$billingId || !$schoolId || !$paidAmount) {
            throw new Exception('Missing required payment information');
        }

        // Get the remaining amount from the billing cycle
        $getBillingQuery = "SELECT total_amount, paid_amount FROM saas_billing_cycles WHERE billing_id = ?";
        $stmt = $db->prepare($getBillingQuery);
        $stmt->execute([$billingId]);
        $billingData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$billingData) {
            throw new Exception('Billing cycle not found');
        }

        // Calculate remaining amount (this becomes total_amount in saas_payments)
        $remainingAmount = $billingData['total_amount'] - $billingData['paid_amount'];

        // Insert payment record into saas_payments
        $insertPaymentQuery = "INSERT INTO saas_payments 
            (billing_id, school_id, total_amount, paid_amount, payment_date, payment_method, reference_no, received_by, created_at) 
            VALUES (:billing_id, :school_id, :total_amount, :paid_amount, :payment_date, :payment_method, :reference_no, :received_by, NOW())";
        
        $stmt = $db->prepare($insertPaymentQuery);
        $executeResult = $stmt->execute([
            ':billing_id' => $billingId,
            ':school_id' => $schoolId,
            ':total_amount' => $remainingAmount,
            ':paid_amount' => $paidAmount,
            ':payment_date' => date('Y-m-d'),
            ':payment_method' => $paymentMethod,
            ':reference_no' => $referenceNo,
            ':received_by' => $receivedBy
        ]);

        if (!$executeResult) {
            throw new Exception('Failed to insert payment: ' . json_encode($stmt->errorInfo()));
        }

        // Update the paid_amount in saas_billing_cycles
        $updateBillingQuery = "UPDATE saas_billing_cycles 
            SET paid_amount = paid_amount + ?, 
                status = CASE 
                    WHEN (paid_amount + ?) >= total_amount THEN 'paid'
                    ELSE 'partial'
                END
            WHERE billing_id = ?";
        
        $stmt = $db->prepare($updateBillingQuery);
        $updateResult = $stmt->execute([
            $paidAmount,
            $paidAmount,
            $billingId
        ]);

        if (!$updateResult) {
            throw new Exception('Failed to update billing: ' . json_encode($stmt->errorInfo()));
        }

        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle block school action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'block_school') {
    try {
        $schoolId = intval($_POST['school_id'] ?? 0);
        
        if (!$schoolId) {
            throw new Exception('School ID is required');
        }
        
        error_log("Blocking school ID: " . $schoolId);
        
        // Update school status to inactive
        $blockQuery = "UPDATE schools SET status = 'inactive' WHERE id = ?";
        $stmt = $db->prepare($blockQuery);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . print_r($db->errorInfo(), true));
        }
        
        $blockResult = $stmt->execute([$schoolId]);
        
        error_log("Query result: " . var_export($blockResult, true));
        error_log("Error info: " . print_r($stmt->errorInfo(), true));
        error_log("Rows affected: " . $stmt->rowCount());
        
        if (!$blockResult) {
            throw new Exception('Failed to block school: ' . print_r($stmt->errorInfo(), true));
        }
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('School not found or already inactive');
        }
        
        // Return success response
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'School has been blocked successfully']);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch unpaid billing cycles
$unpaidBillings = $billingController->getUnpaid();
$totalUnpaid = $billingController->getTotalUnpaidAmount();

// Fetch all payments
$paymentsQuery = "SELECT sp.*, s.name as school_name FROM saas_payments sp LEFT JOIN schools s ON sp.school_id = s.id ORDER BY sp.payment_date DESC";
$paymentsStmt = $db->prepare($paymentsQuery);
$paymentsStmt->execute();
$payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all billing cycles (not just unpaid)
$allBillingsQuery = "SELECT bc.*, s.name as school_name FROM saas_billing_cycles bc LEFT JOIN schools s ON bc.school_id = s.id ORDER BY bc.due_date DESC";
$allBillingsStmt = $db->prepare($allBillingsQuery);
$allBillingsStmt->execute();
$allBillings = $allBillingsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch defaulting billings (due_date < today and outstanding amount exists)
$defaultersQuery = "SELECT bc.*, s.name as school_name, s.contact_no, s.email 
                   FROM saas_billing_cycles bc 
                   LEFT JOIN schools s ON bc.school_id = s.id 
                   WHERE bc.due_date < CURDATE() 
                   AND (bc.total_amount - bc.paid_amount - bc.discounted_amount) > 0
                   ORDER BY bc.due_date ASC";
$defaultersStmt = $db->prepare($defaultersQuery);
$defaultersStmt->execute();
$defaulters = $defaultersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch blocked schools with their outstanding amounts
$blockedQuery = "SELECT s.id, s.name, s.email, s.contact_no, s.status, s.created_at,
                 COALESCE(SUM(bc.total_amount - bc.paid_amount - bc.discounted_amount), 0) as outstanding_amount
                 FROM schools s
                 LEFT JOIN saas_billing_cycles bc ON s.id = bc.school_id
                 WHERE s.status = 'blocked'
                 GROUP BY s.id
                 ORDER BY s.created_at DESC";
$blockedStmt = $db->prepare($blockedQuery);
$blockedStmt->execute();
$blockedAccounts = $blockedStmt->fetchAll(PDO::FETCH_ASSOC);

// Temporary debug
file_put_contents('/tmp/debug.log', "Count: " . count($unpaidBillings) . " | Total: " . $totalUnpaid . " | Data: " . json_encode($unpaidBillings) . "\n", FILE_APPEND);
error_log("Billings Count: " . count($unpaidBillings));
error_log("Total Unpaid: " . $totalUnpaid);

// Function to calculate days overdue
function calculateDaysOverdue($dueDate) {
    $today = new DateTime();
    $due = new DateTime($dueDate);
    $interval = $today->diff($due);
    
    if ($today > $due) {
        return $interval->days;
    }
    return 0;
}

// Function to format currency
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>School managment system</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Admin template that can be used to build dashboards for CRM, CMS, etc." />
    <meta name="author" content="Potenza Global Solutions" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        .nav-tabs {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .nav-tabs .nav-link.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background-color: transparent;
        }

        .nav-tabs .nav-link i {
            margin-right: 8px;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <!-- begin app -->
    <div class="app">
        <!-- begin app-wrap -->
        <div class="app-wrap">
            <!-- begin pre-loader -->
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->
            <!-- begin app-header -->
            <header class="app-header top-bar">
                <!-- begin navbar -->
                <?php include_once '../../include/navbar.php'; ?>
                <!-- end navbar -->
            </header>
            <!-- end app-header -->
            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-nabar -->
                <?php include_once '../../include/sidebar.php'; ?>
                <!-- end app-navbar -->
                <!-- begin app-main -->
                <div class="app-main" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <!-- begin row -->
                        <div class="row">
                            <div class="col-md-12 m-b-30">
                                <!-- begin page title -->
                                <div class="d-block d-lg-flex flex-nowrap align-items-center">
                                    <div class="page-title mr-4 pr-4 border-right">
                                        <h1>Finance</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Finance
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                <!-- <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSchoolModal">
                                        Add School
                                    </button>
                                </div> -->
                                <!-- end page title -->
                            </div>
                        </div>
                        <!-- top cards -->
                         <div class="row">
                            <div class="col-sm-12">
                                <div class="card card-statistics">
                                    <div class="row no-gutters">
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-xxl-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Collection This Month</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                   
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> 15,640</h3>
                                                        <p>Monthly visitor</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-xxl-right border-bottom border-xxl-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Expenses</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                  
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> 16,656</h3>
                                                        <p>This month</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-lg-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Total revenue</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i>569</h3>
                                                        <p>Avg. Sales per day</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-lg-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Due Payments</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                   
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i>569</h3>
                                                        <p>Avg. Sales per day</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-header">
                                        <h3>Financial Management</h3>
                                    </div>
                                    <div class="card-body">
                                        <!-- Tabs Navigation -->
                                        <ul class="nav nav-tabs" id="financeTab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="payments-tab" data-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="true">
                                                    <i class="ti ti-receipt"></i> Payments
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="billing-tab" data-toggle="tab" href="#billing" role="tab" aria-controls="billing" aria-selected="false">
                                                    <i class="ti ti-invoice"></i> Billing
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="due-tab" data-toggle="tab" href="#due" role="tab" aria-controls="due" aria-selected="false">
                                                    <i class="ti ti-alert"></i> Due Payments
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="defaulters-tab" data-toggle="tab" href="#defaulters" role="tab" aria-controls="defaulters" aria-selected="false">
                                                    <i class="ti ti-ban"></i> Defaulters
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="blockedaccounts-tab" data-toggle="tab" href="#blockedaccounts" role="tab" aria-controls="blockedaccounts" aria-selected="false">
                                                    <i class="ti ti-lock"></i> Blocked Accounts
                                                </a>
                                            </li>
                                        </ul>

                                        <!-- Tabs Content -->
                                        <div class="tab-content" id="financeTabContent">
                                            <!-- Payments Tab -->
                                            <div class="tab-pane fade show active" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                                                <div class="mt-4">
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div></div>
                                                                <div>
                                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#addPaymentModal">Add Payment</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="paymentsTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Payment ID</th>
                                                                    <th>Student/School</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Paid Amount</th>
                                                                    <th>Remaining Amount</th>
                                                                    <th>Payment Date</th>
                                                                    <th>Payment Method</th>
                                                                    <th>Reference No</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($payments)): ?>
                                                                    <?php foreach ($payments as $payment): ?>
                                                                        <?php 
                                                                            $paymentDate = date('M d, Y', strtotime($payment['payment_date']));
                                                                            $totalAmount = formatCurrency($payment['total_amount']);
                                                                            $paidAmount = formatCurrency($payment['paid_amount']);
                                                                            $remainingAmount = $payment['total_amount'] - $payment['paid_amount'];
                                                                            $remainingAmountFormatted = formatCurrency($remainingAmount);
                                                                        ?>
                                                                        <tr>
                                                                            <td>#P<?php echo str_pad($payment['payment_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                                                            <td><?php echo htmlspecialchars($payment['school_name'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo $totalAmount; ?></td>
                                                                            <td><?php echo $paidAmount; ?></td>
                                                                            <td><?php echo $remainingAmountFormatted; ?></td>
                                                                            <td><?php echo $paymentDate; ?></td>
                                                                            <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'] ?? 'N/A')); ?></td>
                                                                            <td><?php echo htmlspecialchars($payment['reference_no'] ?? 'N/A'); ?></td>
                                                                            <td><span class="badge badge-success">Completed</span></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-primary">Edit</button>
                                                                                <button class="btn btn-sm btn-danger">Delete</button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="10" class="text-center text-muted py-4">
                                                                            <i class="ti ti-inbox"></i> No payments recorded yet
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>Payment ID</th>
                                                                    <th>Student/School</th>
                                                                    <th>Amount</th>
                                                                    <th>Payment Date</th>
                                                                    <th>Payment Method</th>
                                                                    <th>Reference No</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Billing Tab -->
                                            <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                                                <div class="mt-4">
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="billingTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School/Client Name</th>
                                                                    <th>Period Start</th>
                                                                    <th>Period End</th>
                                                                    <th>Due Date</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Discounted Amount</th>
                                                                    <th>Payable Amount</th>
                                                                    <th>Paid Amount</th>
                                                                    <th>Remaining Amount</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($allBillings)): ?>
                                                                    <?php foreach ($allBillings as $billing): ?>
                                                                        <?php 
                                                                            $periodStart = date('M d, Y', strtotime($billing['period_start']));
                                                                            $periodEnd = date('M d, Y', strtotime($billing['period_end']));
                                                            $dueDate = date('M d, Y', strtotime($billing['due_date']));
                                                                            $totalAmount = $billing['total_amount'];
                                                                            $discountedAmount = $billing['discounted_amount'] ?? 0;
                                                                            $payableAmount = $totalAmount - $discountedAmount;
                                                                            $paidAmount = $billing['paid_amount'];
                                                                            $remainingAmount = $payableAmount - $paidAmount;
                                                                            $showPayButton = ($billing['status'] != 'paid') && ($remainingAmount > 0);
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($billing['billing_id']); ?></td>
                                                                            <td><?php echo htmlspecialchars($billing['school_name'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo $periodStart; ?></td>
                                                                            <td><?php echo $periodEnd; ?></td>
                                                                            <td><?php echo $dueDate; ?></td>
                                                                            <td><?php echo formatCurrency($totalAmount); ?></td>
                                                                            <td><?php echo formatCurrency($discountedAmount); ?></td>
                                                                            <td><?php echo formatCurrency($payableAmount); ?></td>
                                                                            <td><?php echo formatCurrency($paidAmount); ?></td>
                                                                            <td><?php echo formatCurrency($remainingAmount); ?></td>
                                                                            <td><span class="badge badge-<?php echo $billing['status'] == 'paid' ? 'success' : ($billing['status'] == 'partial' ? 'warning' : 'danger'); ?>"><?php echo ucfirst(htmlspecialchars($billing['status'])); ?></span></td>
                                                                            <td>
                                                                                <?php if ($showPayButton): ?>
                                                                                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#payNowModal" onclick="setPaymentData(<?php echo htmlspecialchars($billing['billing_id']); ?>, <?php echo htmlspecialchars($billing['school_id']); ?>, <?php echo htmlspecialchars($remainingAmount); ?>, <?php echo htmlspecialchars($payableAmount); ?>)">
                                                                                        <i class="ti ti-check"></i> Pay Now
                                                                                    </button>
                                                                                <?php else: ?>
                                                                                    <span class="badge badge-success">Paid</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="12" class="text-center text-muted py-4">
                                                                            <i class="ti ti-mood-smile"></i> No billing cycles available
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School/Client Name</th>
                                                                    <th>Period Start</th>
                                                                    <th>Period End</th>
                                                                    <th>Due Date</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Discounted Amount</th>
                                                                    <th>Payable Amount</th>
                                                                    <th>Paid Amount</th>
                                                                    <th>Remaining Amount</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Due Payments Tab -->
                                            <div class="tab-pane fade" id="due" role="tabpanel" aria-labelledby="due-tab">
                                                <div class="mt-4">
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <div class="alert alert-warning" role="alert">
                                                                <strong>Total Due Amount:</strong> <?php echo formatCurrency($totalUnpaid); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="dueTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School/Client Name</th>
                                                                    <th>Contact</th>
                                                                    <th>Period</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Paid Amount</th>
                                                                    <th>Due Amount</th>
                                                                    <th>Due Date</th>
                                                                    <th>Days Overdue</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($unpaidBillings)): ?>
                                                                    <?php foreach ($unpaidBillings as $billing): ?>
                                                                        <?php 
                                                                            $daysOverdue = calculateDaysOverdue($billing['due_date']);
                                                                            $dueAmount = $billing['total_amount'] - $billing['paid_amount'];
                                                                            $periodStart = date('M d, Y', strtotime($billing['period_start']));
                                                                            $periodEnd = date('M d, Y', strtotime($billing['period_end']));
                                                                            $dueDate = date('M d, Y', strtotime($billing['due_date']));
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($billing['billing_id']); ?></td>
                                                                            <td><?php echo htmlspecialchars($billing['school_name'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo htmlspecialchars($billing['contact_phone'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo $periodStart . ' - ' . $periodEnd; ?></td>
                                                                            <td><?php echo formatCurrency($billing['total_amount']); ?></td>
                                                                            <td><?php echo formatCurrency($billing['paid_amount']); ?></td>
                                                                            <td><?php echo formatCurrency($dueAmount); ?></td>
                                                                            <td><?php echo $dueDate; ?></td>
                                                                            <td>
                                                                                <?php if ($daysOverdue > 0): ?>
                                                                                    <span class="badge badge-danger"><?php echo $daysOverdue; ?></span>
                                                                                <?php else: ?>
                                                                                    <span class="badge badge-warning">Due Soon</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            
                                                                            <td><span class="badge badge-warning">Unpaid</span></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-primary" title="Send Reminder">
                                                                                    <i class="ti ti-email"></i> Remind
                                                                                </button>
                                                                                <button class="btn btn-sm btn-success" title="Mark as Paid">
                                                                                    <i class="ti ti-check"></i> Paid
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="12" class="text-center text-muted py-4">
                                                                            <i class="ti ti-mood-smile"></i> No unpaid billing cycles at this time
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School/Client Name</th>
                                                                    <th>Contact</th>
                                                                    <th>Period</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Paid Amount</th>
                                                                    <th>Due Amount</th>
                                                                    <th>Due Date</th>
                                                                    <th>Days Overdue</th>
                                                                    <th>Email</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Defaulters Tab -->
                                            <div class="tab-pane fade" id="defaulters" role="tabpanel" aria-labelledby="defaulters-tab">
                                                <div class="mt-4">
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <?php
                                                                $totalDefaulters = count($defaulters);
                                                                $totalAmountAtRisk = 0;
                                                                foreach ($defaulters as $defaulter) {
                                                                    $payableAmount = $defaulter['total_amount'] - $defaulter['discounted_amount'];
                                                                    $outstanding = $payableAmount - $defaulter['paid_amount'];
                                                                    $totalAmountAtRisk += $outstanding;
                                                                }
                                                            ?>
                                                            <div class="alert alert-danger" role="alert">
                                                                <strong>Total Defaulters:</strong> <?php echo $totalDefaulters; ?> | <strong>Total Amount at Risk:</strong> <?php echo formatCurrency($totalAmountAtRisk); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="defaultersTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School Name</th>
                                                                    <th>Contact</th>
                                                                    <th>Email</th>
                                                                    <th>Due Date</th>
                                                                    <th>Days Overdue</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Discounted</th>
                                                                    <th>Payable</th>
                                                                    <th>Paid</th>
                                                                    <th>Outstanding</th>
                                                                    <th>Risk Level</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($defaulters)): ?>
                                                                    <?php foreach ($defaulters as $defaulter): ?>
                                                                        <?php
                                                                            $dueDate = new DateTime($defaulter['due_date']);
                                                                            $today = new DateTime();
                                                                            $daysOverdue = $today->diff($dueDate)->days;
                                                                            
                                                                            $payableAmount = $defaulter['total_amount'] - $defaulter['discounted_amount'];
                                                                            $outstanding = $payableAmount - $defaulter['paid_amount'];
                                                                            
                                                                            // Determine risk level
                                                                            if ($daysOverdue >= 90) {
                                                                                $riskBadge = '<span class="badge badge-danger">Critical</span>';
                                                                            } elseif ($daysOverdue >= 60) {
                                                                                $riskBadge = '<span class="badge badge-danger">High</span>';
                                                                            } elseif ($daysOverdue >= 30) {
                                                                                $riskBadge = '<span class="badge badge-warning">Medium</span>';
                                                                            } else {
                                                                                $riskBadge = '<span class="badge badge-info">Low</span>';
                                                                            }
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo $defaulter['billing_id']; ?></td>
                                                                            <td><?php echo htmlspecialchars($defaulter['school_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($defaulter['contact_no'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo htmlspecialchars($defaulter['email'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo date('Y-m-d', strtotime($defaulter['due_date'])); ?></td>
                                                                            <td><span class="badge badge-danger"><?php echo $daysOverdue; ?> days</span></td>
                                                                            <td><?php echo formatCurrency($defaulter['total_amount']); ?></td>
                                                                            <td><?php echo formatCurrency($defaulter['discounted_amount']); ?></td>
                                                                            <td><?php echo formatCurrency($payableAmount); ?></td>
                                                                            <td><?php echo formatCurrency($defaulter['paid_amount']); ?></td>
                                                                            <td><strong><?php echo formatCurrency($outstanding); ?></strong></td>
                                                                            <td><?php echo $riskBadge; ?></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-success" onclick="setPaymentData(<?php echo $defaulter['billing_id']; ?>, <?php echo $defaulter['school_id']; ?>, <?php echo $outstanding; ?>, <?php echo $payableAmount; ?>)" data-toggle="modal" data-target="#payNowModal" title="Record Payment">
                                                                                    <i class="ti ti-dollar"></i> Pay Now
                                                                                </button>
                                                                                <button class="btn btn-sm btn-danger" onclick="openBlockConfirm(<?php echo $defaulter['school_id']; ?>, '<?php echo htmlspecialchars($defaulter['school_name']); ?>')" title="Block School">
                                                                                    <i class="ti ti-ban"></i> Block
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="13" class="text-center text-muted py-4">
                                                                            <i class="ti ti-mood-smile"></i> No defaulters at this time
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>Billing ID</th>
                                                                    <th>School Name</th>
                                                                    <th>Contact</th>
                                                                    <th>Email</th>
                                                                    <th>Due Date</th>
                                                                    <th>Days Overdue</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Discounted</th>
                                                                    <th>Payable</th>
                                                                    <th>Paid</th>
                                                                    <th>Outstanding</th>
                                                                    <th>Risk Level</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Blocked Accounts Tab -->
                                            <div class="tab-pane fade" id="blockedaccounts" role="tabpanel" aria-labelledby="blockedaccounts-tab">
                                                <div class="mt-4">
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <?php $totalBlockedCount = count($blockedAccounts); ?>
                                                            <div class="alert alert-info" role="alert">
                                                                <strong>Total Blocked Accounts:</strong> <?php echo $totalBlockedCount; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="blockedAccountsTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>School ID</th>
                                                                    <th>School Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact</th>
                                                                    <th>Outstanding Balance</th>
                                                                    <th>Blocked Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($blockedAccounts)): ?>
                                                                    <?php foreach ($blockedAccounts as $blocked): ?>
                                                                        <tr>
                                                                            <td><?php echo $blocked['id']; ?></td>
                                                                            <td><?php echo htmlspecialchars($blocked['name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($blocked['email'] ?? 'N/A'); ?></td>
                                                                            <td><?php echo htmlspecialchars($blocked['contact_no'] ?? 'N/A'); ?></td>
                                                                            <td><strong><?php echo formatCurrency($blocked['outstanding_amount']); ?></strong></td>
                                                                            <td><?php echo date('Y-m-d', strtotime($blocked['created_at'])); ?></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-warning" onclick="clearBalance(<?php echo $blocked['id']; ?>, '<?php echo htmlspecialchars($blocked['name']); ?>')" title="Clear Balance">
                                                                                    <i class="ti ti-trash"></i> Clear Balance
                                                                                </button>
                                                                                <button class="btn btn-sm btn-success" onclick="unblockSchool(<?php echo $blocked['id']; ?>, '<?php echo htmlspecialchars($blocked['name']); ?>')" title="Unblock School">
                                                                                    <i class="ti ti-lock-open"></i> Unblock
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center text-muted py-4">
                                                                            <i class="ti ti-mood-smile"></i> No blocked accounts
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>School ID</th>
                                                                    <th>School Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact</th>
                                                                    <th>Outstanding Balance</th>
                                                                    <th>Blocked Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                               
                            </div>
                        </div>
                        <!-- end row -->
                    </div>

                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>

            <!-- end app-container -->
            <!-- begin footer -->
           
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- Add School Modal -->
    <div class="modal fade" id="addSchoolModal" tabindex="-1" role="dialog" aria-labelledby="addSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSchoolModalLabel">Add New School</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addSchoolForm">
                        <div class="form-group">
                            <label for="schoolName">School Name</label>
                            <input type="text" class="form-control" id="schoolName" placeholder="Enter school name" required>
                        </div>
                        <div class="form-group">
                            <label for="domain">Domain</label>
                            <input type="text" class="form-control" id="domain" placeholder="Enter domain" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="contactNo">Contact Number</label>
                            <input type="tel" class="form-control" id="contactNo" placeholder="Enter contact number" required>
                        </div>
                        <div class="form-group">
                            <label for="students">Number of Students</label>
                            <input type="number" class="form-control" id="students" placeholder="Enter number of students" required>
                        </div>
                        <div class="form-group">
                            <label for="plan">Plan</label>
                            <select class="form-control" id="plan" required>
                                <option value="">Select Plan</option>
                                <option value="Basic">Basic</option>
                                <option value="Standard">Standard</option>
                                <option value="Premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dueDate">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSchoolBtn">Save School</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add School Modal -->

    <!-- Block Confirmation Modal -->
    <div class="modal fade" id="blockConfirmModal" tabindex="-1" role="dialog" aria-labelledby="blockConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="blockConfirmModalLabel">
                        <i class="ti ti-alert-triangle mr-2"></i> Confirm Block
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Are you sure you want to block 
                        <strong id="schoolNameDisplay">this school</strong>?
                    </p>
                    <p class="text-muted small mt-2">This action will suspend the school's access until it is unblocked.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBlockBtn">Yes, Block School</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Pay Now Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- Pay Now Modal -->
    <div class="modal fade" id="payNowModal" tabindex="-1" role="dialog" aria-labelledby="payNowModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="payNowModalLabel">
                        <i class="ti ti-check-circle mr-2"></i> Record Payment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="paymentBillingId" name="billing_id">
                        <input type="hidden" id="paymentSchoolId" name="school_id">
                        <input type="hidden" id="paymentTotalAmount" name="total_amount">
                        
                        <div class="form-group">
                            <label>Remaining Amount to Pay</label>
                            <input type="text" class="form-control" id="remainingDisplay" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="paymentAmount">Payment Amount (Rs) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="paymentAmount" name="paid_amount" placeholder="Enter payment amount" required>
                            <small class="form-text text-muted">Enter the amount being paid now</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="paymentMethod">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-control" id="paymentMethod" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                                <option value="check">Check</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="referenceNo">Reference No</label>
                            <input type="text" class="form-control" id="referenceNo" name="reference_no" placeholder="e.g., Check Number, Transaction ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="receivedBy">Received By</label>
                            <input type="text" class="form-control" id="receivedBy" name="received_by" placeholder="Name of person who received payment">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitPaymentBtn" onclick="submitPayment()">
                        <i class="ti ti-check"></i> Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Pay Now Modal -->

    <!-- Block School Confirmation Modal -->
    <div class="modal fade" id="blockConfirmModal" tabindex="-1" role="dialog" aria-labelledby="blockConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="blockConfirmModalLabel">
                        <i class="ti ti-ban mr-2"></i> Confirm Block School
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-alert-triangle mr-2"></i> <strong>Warning!</strong> This action will deactivate the school and block it from accessing the system.
                    </div>
                    <p>Are you sure you want to block the following school?</p>
                    <div class="card">
                        <div class="card-body">
                            <strong>School Name:</strong> <span id="blockSchoolName" class="text-danger"></span>
                            <input type="hidden" id="blockSchoolId">
                        </div>
                    </div>
                    <p class="mt-3 text-muted">
                        <i class="ti ti-info-circle mr-2"></i> Once blocked, the school will be unable to log in or access their dashboard until this action is reversed.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmBlockSchool()">
                        <i class="ti ti-ban"></i> Yes, Block School
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Block School Confirmation Modal -->

    <script>
        // Set payment data when Pay Now button is clicked
        function setPaymentData(billingId, schoolId, remainingAmount, totalAmount) {
            document.getElementById('paymentBillingId').value = billingId;
            document.getElementById('paymentSchoolId').value = schoolId;
            document.getElementById('paymentTotalAmount').value = totalAmount;
            document.getElementById('remainingDisplay').value = 'Rs ' + parseFloat(remainingAmount).toFixed(2);
            document.getElementById('paymentAmount').value = '';
            document.getElementById('paymentMethod').value = '';
            document.getElementById('referenceNo').value = '';
            document.getElementById('receivedBy').value = '';
        }

        // Submit payment form
        function submitPayment() {
            const billingId = document.getElementById('paymentBillingId').value;
            const schoolId = document.getElementById('paymentSchoolId').value;
            const totalAmount = document.getElementById('paymentTotalAmount').value;
            const paidAmount = document.getElementById('paymentAmount').value;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const referenceNo = document.getElementById('referenceNo').value;
            const receivedBy = document.getElementById('receivedBy').value;

            // Validation
            if (!paidAmount || parseFloat(paidAmount) <= 0) {
                alert('Please enter a valid payment amount');
                return;
            }

            if (!paymentMethod) {
                alert('Please select a payment method');
                return;
            }

            // Send data to server
            const formData = new FormData();
            formData.append('action', 'add_payment');
            formData.append('billing_id', billingId);
            formData.append('school_id', schoolId);
            formData.append('total_amount', totalAmount);
            formData.append('paid_amount', paidAmount);
            formData.append('payment_method', paymentMethod);
            formData.append('reference_no', referenceNo);
            formData.append('received_by', receivedBy);

            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment recorded successfully!');
                    $('#payNowModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to record payment'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while recording the payment');
            });
        }

        // Block School Functions
        function openBlockConfirm(schoolId, schoolName) {
            console.log('Opening block confirm for school:', schoolId, schoolName);
            document.getElementById('blockSchoolId').value = schoolId;
            document.getElementById('blockSchoolName').textContent = schoolName;
            $('#blockConfirmModal').modal('show');
        }

        function confirmBlockSchool() {
            console.log('confirmBlockSchool called');
            const schoolId = document.getElementById('blockSchoolId').value;
            console.log('School ID from element:', schoolId);
            
            if (!schoolId) {
                alert('Invalid school - no ID found');
                console.log('No school ID found!');
                return;
            }

            console.log('Proceeding to block school:', schoolId);
            const formData = new FormData();
            formData.append('action', 'block_school');
            formData.append('school_id', schoolId);

            console.log('Sending AJAX request...');
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response Status:', response.status);
                return response.text().then(text => {
                    console.log('Response Text:', text);
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed Data:', data);
                if (data.success) {
                    alert('School has been blocked successfully!');
                    $('#blockConfirmModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to block school'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while blocking the school: ' + error.message);
            });
        }
        
        // Ensure button click works
        document.addEventListener('DOMContentLoaded', function() {
            const blockBtn = document.getElementById('confirmBlockBtn');
            if (blockBtn) {
                console.log('confirmBlockBtn found, adding event listener');
                blockBtn.addEventListener('click', function(e) {
                    console.log('confirmBlockBtn clicked!');
                    e.preventDefault();
                    confirmBlockSchool();
                });
            } else {
                console.log('confirmBlockBtn NOT found!');
            }
        });

        // Clear balance function
        function clearBalance(schoolId, schoolName) {
            if (confirm('Are you sure you want to clear the balance for "' + schoolName + '"?\n\nThis will mark all pending invoices as paid.')) {
                const formData = new FormData();
                formData.append('action', 'clear_balance');
                formData.append('school_id', schoolId);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Balance cleared successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to clear balance'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred: ' + error.message);
                });
            }
        }

        // Unblock school function
        function unblockSchool(schoolId, schoolName) {
            if (confirm('Are you sure you want to unblock "' + schoolName + '"?\n\nThis will restore their access to the system.')) {
                const formData = new FormData();
                formData.append('action', 'unblock_school');
                formData.append('school_id', schoolId);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('School unblocked successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to unblock school'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred: ' + error.message);
                });
            }
        }
    </script>
    <!-- End Pay Now Script -->
