<?php
session_start();

// Database connection
require_once '../../../../Config/connection.php';
require_once '../../Controllers/School_controller.php';
require_once '../../Controllers/plain_controller.php';

// Initialize controllers
$schoolController = new SchoolController($DB_con);
$planController = new PlanController($DB_con);

// Get all plans for dropdown
$plans = $planController->index();

// Get school ID from URL
$schoolId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$schoolId) {
    $_SESSION['error'] = 'No school ID provided!';
    header("Location: schools.php");
    exit;
}

// Fetch school details
$school = $schoolController->getSchoolById($schoolId);

if (!$school) {
    $_SESSION['error'] = 'School not found!';
    header("Location: schools.php");
    exit;
}

// Get subscription and billing data
$subscriptionQuery = "SELECT * FROM saas_school_subscriptions WHERE school_id = ?";
$subscriptionStmt = $DB_con->prepare($subscriptionQuery);
$subscriptionStmt->execute([$schoolId]);
$subscriptions = $subscriptionStmt->fetchAll(PDO::FETCH_ASSOC);

// Get billing cycles
$billingQuery = "SELECT * FROM saas_billing_cycles WHERE school_id = ? ORDER BY created_at DESC";
$billingStmt = $DB_con->prepare($billingQuery);
$billingStmt->execute([$schoolId]);
$billings = $billingStmt->fetchAll(PDO::FETCH_ASSOC);

// Get payments
$paymentQuery = "SELECT * FROM saas_payments WHERE school_id = ? ORDER BY created_at DESC";
$paymentStmt = $DB_con->prepare($paymentQuery);
$paymentStmt->execute([$schoolId]);
$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

// Currency formatter
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

// Handle POST request for updating school from details page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle upgrade plan submission
        if (isset($_POST['action']) && $_POST['action'] === 'upgrade') {
            $upgradeData = [
                'estimated_students' => isset($_POST['estimated_students']) ? $_POST['estimated_students'] : $school['estimated_students'],
                'plan' => isset($_POST['plan']) ? $_POST['plan'] : $school['plan']
            ];
            
            if ($schoolController->update($schoolId, $upgradeData)) {
                $_SESSION['success'] = 'Plan upgraded successfully!';
                $school = $schoolController->getSchoolById($schoolId);
                header("Location: school_details.php?id=" . htmlspecialchars($schoolId));
                exit;
            } else {
                $_SESSION['error'] = 'Failed to upgrade plan!';
            }
        } else {
            // Handle regular school update
            if ($schoolController->update()) {
                $_SESSION['success'] = 'School updated successfully!';
                $school = $schoolController->getSchoolById($schoolId);
                header("Location: school_details.php?id=" . htmlspecialchars($schoolId));
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update school!';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        if ($schoolController->delete($schoolId)) {
            $_SESSION['success'] = 'School deleted successfully!';
            header("Location: schools.php");
            exit;
        } else {
            $_SESSION['error'] = 'Failed to delete school!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
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

    <!-- TABS -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($school['name']); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="schools.php" class="btn btn-secondary">Back to Schools</a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#subscriptions">Subscriptions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#usage">Usage & Limits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#billing">Billing</a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">
            <div class="row mb-3">
                <div class="col-md-12 text-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#editSchoolModal">Edit</button>
                    <a href="school_details.php?id=<?php echo htmlspecialchars($school['id']); ?>&action=delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this school?');">Delete</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>School Overview</h5>
                </div>

                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="30%">School Name</th><td><?php echo htmlspecialchars($school['name']); ?></td></tr>
                        <tr><th>Subdomain</th><td><?php echo htmlspecialchars($school['subdomain']); ?>.yoursaas.com</td></tr>
                        <tr><th>Plan</th><td><?php echo htmlspecialchars($school['plan']); ?></td></tr>
                        <tr><th>Status</th><td><span class="badge badge-<?php echo ($school['status'] === 'active') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($school['status']); ?></span></td></tr>
                        <tr><th>Contact Email</th><td><?php echo htmlspecialchars($school['email']); ?></td></tr>
                        <tr><th>Contact No</th><td><?php echo htmlspecialchars($school['contact_no']); ?></td></tr>
                        <tr><th>Estimated Students</th><td><?php echo htmlspecialchars($school['estimated_students']); ?></td></tr>
                        <tr><th>Start Date</th><td><?php echo htmlspecialchars($school['start_date']); ?></td></tr>
                        <tr><th>Expires At</th><td><?php echo htmlspecialchars($school['expires_at']); ?></td></tr>
                        <tr><th>Storage Used</th><td><?php echo htmlspecialchars($school['storage_used']); ?> MB</td></tr>
                        <tr><th>Database Size</th><td><?php echo htmlspecialchars($school['db_size']); ?> MB</td></tr>
                        <tr><th>Created At</th><td><?php echo htmlspecialchars($school['created_at']); ?></td></tr>
                        <tr><th>Last Updated</th><td><?php echo htmlspecialchars($school['updated_at']); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= SUBSCRIPTIONS ================= -->
        <div class="tab-pane fade" id="subscriptions">
            <?php if (!empty($subscriptions)): ?>
            <div class="row">
                <?php foreach ($subscriptions as $sub): ?>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><?php echo htmlspecialchars($sub['plan_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr><th width="50%">Subscription ID</th><td><?php echo $sub['subscription_id']; ?></td></tr>
                                <tr><th>Plan Name</th><td><?php echo htmlspecialchars($sub['plan_name']); ?></td></tr>
                                <tr><th>Price Per Student</th><td><?php echo formatCurrency($sub['price_per_student']); ?></td></tr>
                                <tr><th>Students Count</th><td><?php echo $sub['students_count']; ?></td></tr>
                                <tr><th>Billing Cycle</th><td><span class="badge badge-info"><?php echo ucfirst($sub['billing_cycle']); ?></span></td></tr>
                                <tr><th>Status</th><td><span class="badge badge-<?php echo ($sub['status'] === 'active') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($sub['status']); ?></span></td></tr>
                                <tr><th>Start Date</th><td><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td></tr>
                                <tr><th>End Date</th><td><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">No subscriptions found for this school.</div>
            <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="usage">
            <div class="row">

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Students</h6>
                            <h4>0 / <?php echo htmlspecialchars($school['estimated_students']); ?></h4>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width:<?php echo ($school['estimated_students'] > 0) ? '0' : '0'; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Storage</h6>
                            <?php 
                                $storageUsed = isset($school['storage_used']) ? floatval($school['storage_used']) : 0;
                                $storageLimit = 2048; // 2GB in MB
                                $storagePercent = ($storageUsed > 0) ? min(100, ($storageUsed / $storageLimit) * 100) : 0;
                                $storageGB = $storageUsed / 1024;
                            ?>
                            <h4><?php echo number_format($storageGB, 2); ?> GB / 2 GB</h4>
                            <div class="progress">
                                <div class="progress-bar <?php echo ($storagePercent > 80) ? 'bg-danger' : ($storagePercent > 60 ? 'bg-warning' : 'bg-success'); ?>" style="width:<?php echo $storagePercent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Database Size</h6>
                            <?php 
                                $dbUsed = isset($school['db_size']) ? floatval($school['db_size']) : 0;
                                $dbLimit = 1024; // 1GB in MB
                                $dbPercent = ($dbUsed > 0) ? min(100, ($dbUsed / $dbLimit) * 100) : 0;
                                $dbGB = $dbUsed / 1024;
                            ?>
                            <h4><?php echo number_format($dbGB, 2); ?> GB / 1 GB</h4>
                            <div class="progress">
                                <div class="progress-bar <?php echo ($dbPercent > 80) ? 'bg-danger' : ($dbPercent > 60 ? 'bg-warning' : 'bg-success'); ?>" style="width:<?php echo $dbPercent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-right mt-3">
                <button class="btn btn-success" data-toggle="modal" data-target="#upgradePlanModal">
                    Upgrade Plan
                </button>
            </div>
        </div>

        <!-- ================= BILLING ================= -->
        <div class="tab-pane fade" id="billing">
            
            <!-- Billing Cycles -->
            <?php if (!empty($billings)): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Billing Cycles</h5>
                    <div style="gap: 8px; display: flex;">
                        <button class="btn btn-sm btn-primary" onclick="printBillingTable()"><i class="ti ti-printer"></i> Print</button>
                        <button class="btn btn-sm btn-danger" onclick="saveBillingPDF()"><i class="ti ti-file-pdf"></i> PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="billingTable">
                            <thead>
                                <tr>
                                    <th>Billing ID</th>
                                    <th>Period</th>
                                    <th>Due Date</th>
                                    <th>Total Amount</th>
                                    <th>Discounted</th>
                                    <th>Payable</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $totalAmount = 0;
                                    $totalDiscounted = 0;
                                    $totalPayable = 0;
                                    $totalPaid = 0;
                                    $totalOutstanding = 0;
                                    foreach (array_slice($billings, 0, 10) as $bill): 
                                        $payable = $bill['total_amount'] - $bill['discounted_amount'];
                                        $outstanding = $bill['total_amount'] - $bill['paid_amount'] - $bill['discounted_amount'];
                                        $totalAmount += $bill['total_amount'];
                                        $totalDiscounted += $bill['discounted_amount'];
                                        $totalPayable += $payable;
                                        $totalPaid += $bill['paid_amount'];
                                        $totalOutstanding += $outstanding;
                                ?>
                                <tr>
                                    <td><?php echo $bill['billing_id']; ?></td>
                                    <td>
                                        <?php echo date('M d', strtotime($bill['period_start'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($bill['period_end'])); ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($bill['due_date'])); ?></td>
                                    <td><?php echo formatCurrency($bill['total_amount']); ?></td>
                                    <td><?php echo formatCurrency($bill['discounted_amount']); ?></td>
                                    <td><?php echo formatCurrency($payable); ?></td>
                                    <td><?php echo formatCurrency($bill['paid_amount']); ?></td>
                                    <td><?php echo formatCurrency($outstanding); ?></td>
                                    <td><span class="badge badge-<?php echo ($bill['status'] === 'paid') ? 'success' : ($bill['status'] === 'partial' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($bill['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f0f0f0; font-weight: bold;">
                                    <td colspan="3" class="text-right">TOTAL:</td>
                                    <td><?php echo formatCurrency($totalAmount); ?></td>
                                    <td><?php echo formatCurrency($totalDiscounted); ?></td>
                                    <td><?php echo formatCurrency($totalPayable); ?></td>
                                    <td><?php echo formatCurrency($totalPaid); ?></td>
                                    <td><?php echo formatCurrency($totalOutstanding); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">No billing cycles found for this school.</div>
            <?php endif; ?>

            <!-- Payment History -->
            <?php if (!empty($payments)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Payment History</h5>
                    <div style="gap: 8px; display: flex;">
                        <button class="btn btn-sm btn-primary" onclick="printPaymentTable()"><i class="ti ti-printer"></i> Print</button>
                        <button class="btn btn-sm btn-danger" onclick="savePaymentPDF()"><i class="ti ti-file-pdf"></i> PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="paymentTable">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Billing ID</th>
                                    <th>Payment Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Payment Method</th>
                                    <th>Reference</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $paymentTotal = 0;
                                    $paymentPaid = 0;
                                    foreach (array_slice($payments, 0, 10) as $pay): 
                                        $paymentTotal += $pay['total_amount'];
                                        $paymentPaid += $pay['paid_amount'];
                                ?>
                                <tr>
                                    <td><?php echo $pay['payment_id']; ?></td>
                                    <td><?php echo $pay['billing_id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($pay['payment_date'])); ?></td>
                                    <td><?php echo formatCurrency($pay['total_amount']); ?></td>
                                    <td><strong><?php echo formatCurrency($pay['paid_amount']); ?></strong></td>
                                    <td><span class="badge badge-primary"><?php echo ucfirst($pay['payment_method']); ?></span></td>
                                    <td><?php echo htmlspecialchars($pay['reference_no'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($pay['received_by'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f0f0f0; font-weight: bold;">
                                    <td colspan="3" class="text-right">TOTAL:</td>
                                    <td><?php echo formatCurrency($paymentTotal); ?></td>
                                    <td><?php echo formatCurrency($paymentPaid); ?></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">No payments found for this school.</div>
            <?php endif; ?>
        </div>

    </div>
</div>
<!-- modals -->
 <div class="modal fade" id="upgradePlanModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Upgrade Plan & Limits</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="upgrade">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($school['id']); ?>">
                    
                    <div class="form-group">
                        <label>Estimated Students Limit</label>
                        <input type="number" class="form-control" name="estimated_students" value="<?php echo htmlspecialchars($school['estimated_students']); ?>" required>
                        <small class="form-text text-muted">Current: <?php echo htmlspecialchars($school['estimated_students']); ?></small>
                    </div>

                    <div class="form-group">
                        <label>Storage Limit (GB)</label>
                        <input type="number" class="form-control" name="storage_limit" value="2" step="0.5" required>
                        <small class="form-text text-muted">Current: 2 GB</small>
                    </div>

                    <div class="form-group">
                        <label>Database Size Limit (GB)</label>
                        <input type="number" class="form-control" name="db_limit" value="1" step="0.5" required>
                        <small class="form-text text-muted">Current: 1 GB</small>
                    </div>

                    <div class="form-group">
                        <label>Plan</label>
                        <select class="form-control" name="plan" required>
                            <option value="">Select Plan</option>
                            <?php if(!empty($plans)): ?>
                                <?php foreach($plans as $plan): ?>
                                    <option value="<?php echo htmlspecialchars($plan['name']); ?>" <?php echo ($school['plan'] === $plan['name']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($plan['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Upgrade Plan</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

 <div class="modal fade" id="editSchoolModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit School</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($school['id']); ?>">
                    
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($school['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Subdomain</label>
                        <input type="text" class="form-control" name="subdomain" value="<?php echo htmlspecialchars($school['subdomain']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($school['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" class="form-control" name="contact_no" value="<?php echo htmlspecialchars($school['contact_no']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Estimated Students</label>
                        <input type="number" class="form-control" name="estimated_students" value="<?php echo htmlspecialchars($school['estimated_students']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Plan</label>
                        <select class="form-control" name="plan" required>
                            <option value="">Select Plan</option>
                            <?php if(!empty($plans)): ?>
                                <?php foreach($plans as $plan): ?>
                                    <option value="<?php echo htmlspecialchars($plan['name']); ?>" <?php echo ($school['plan'] === $plan['name']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($plan['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" required>
                            <option value="active" <?php echo ($school['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($school['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="pending" <?php echo ($school['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo (isset($school['start_date']) && !empty($school['start_date'])) ? htmlspecialchars(date('Y-m-d', strtotime($school['start_date']))) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Expires At</label>
                        <input type="date" class="form-control" name="expires_at" value="<?php echo (isset($school['expires_at']) && !empty($school['expires_at'])) ? htmlspecialchars(date('Y-m-d', strtotime($school['expires_at']))) : ''; ?>" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

 <!-- modals -->

                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>
            <!-- end app-container -->
            <!-- begin footer -->
            <footer class="footer">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-left">
                        <p>&copy; Copyright 2026. All rights reserved.</p>
                    </div>
                   <div class="col  col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="https://www.templateshub.net">Inventory Hub</a></p>
                    </div>
                </div>
            </footer>
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        // Print Billing Table
        function printBillingTable() {
            const printWindow = window.open('', '', 'height=600,width=800');
            const billingTable = document.getElementById('billingTable');
            printWindow.document.write('<html><head><title>Billing Cycles Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
            printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
            printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
            printWindow.document.write('tfoot tr { background-color: #f9f9f9; font-weight: bold; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Billing Cycles Report</h2>');
            printWindow.document.write(billingTable.outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            setTimeout(() => printWindow.print(), 100);
        }

        // Print Payment Table
        function printPaymentTable() {
            const printWindow = window.open('', '', 'height=600,width=800');
            const paymentTable = document.getElementById('paymentTable');
            printWindow.document.write('<html><head><title>Payment History Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
            printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
            printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
            printWindow.document.write('tfoot tr { background-color: #f9f9f9; font-weight: bold; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Payment History Report</h2>');
            printWindow.document.write(paymentTable.outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            setTimeout(() => printWindow.print(), 100);
        }

        // Save Billing Table as PDF
        function saveBillingPDF() {
            const element = document.getElementById('billingTable');
            const schoolName = document.querySelector('.page-title h1').textContent;
            const filename = 'Billing-Cycles-' + schoolName + '-' + new Date().toISOString().split('T')[0] + '.pdf';
            
            const opt = {
                margin: 5,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
            };
            
            html2pdf().set(opt).from(element).save();
        }

        // Save Payment Table as PDF
        function savePaymentPDF() {
            const element = document.getElementById('paymentTable');
            const schoolName = document.querySelector('.page-title h1').textContent;
            const filename = 'Payment-History-' + schoolName + '-' + new Date().toISOString().split('T')[0] + '.pdf';
            
            const opt = {
                margin: 5,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>


</html>