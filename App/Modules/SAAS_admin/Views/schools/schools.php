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

// Handle POST request for adding/updating school
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if this is an update (has id) or create
        if (!empty($_POST['id'])) {
            // Update school
            if ($schoolController->update()) {
                $_SESSION['success'] = 'School updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update school!';
            }
        } else {
            // Create new school
            if ($schoolController->store()) {
                $_SESSION['success'] = 'School added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add school!';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
    header("Location: schools.php");
    exit;
}

// Get all schools
$schools = $schoolController->index();

// Handle GET request for deleting school
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        if ($schoolController->delete($_GET['id'])) {
            $_SESSION['success'] = 'School deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete school!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header("Location: schools.php");
    exit;
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
                        <!-- begin row -->
                        <div class="row">
                            <div class="col-md-12 m-b-30">
                                <!-- begin page title -->
                                <div class="d-block d-lg-flex flex-nowrap align-items-center">
                                    <div class="page-title mr-4 pr-4 border-right">
                                        <h1>Schools</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Schools
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSchoolModal">
                                        Add School
                                    </button>
                                </div>
                                <!-- end page title -->
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
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <!-- Filters Section -->
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <input type="text" id="filterName" class="form-control" placeholder="Search by School Name">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" id="filterStartDate" class="form-control" placeholder="Start Date">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" id="filterEndDate" class="form-control" placeholder="End Date">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <button id="resetFilter" class="btn btn-secondary">Reset Filters</button>
                                            </div>
                                        </div>
                                        <!-- End Filters Section -->
                                        <div class="datatable-wrapper table-responsive">
                                            <table id="datatable" class="display compact table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>School Name</th>
                                                        <th>Subdomain</th>
                                                        <th>Email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Price/Student</th>
                                                        <th>Billing Cycle</th>
                                                        <th>Status</th>
                                                        <th>Starts</th>
                                                        <th>Expires</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(!empty($schools)): ?>
                                                        <?php foreach($schools as $school): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($school['id']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['name']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['subdomain']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['email']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['contact_no']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['estimated_students']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['plan']); ?></td>
                                                                <td>
                                                                    <?php 
                                                                    // Try to get subscription data for price
                                                                    $subsQuery = $DB_con->prepare("SELECT price_per_student FROM saas_school_subscriptions WHERE school_id = ? LIMIT 1");
                                                                    $subsQuery->execute([$school['id']]);
                                                                    $subData = $subsQuery->fetch(PDO::FETCH_ASSOC);
                                                                    echo htmlspecialchars($subData['price_per_student'] ?? '0.00');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php 
                                                                    // Get billing cycle from subscription
                                                                    $billQuery = $DB_con->prepare("SELECT billing_cycle FROM saas_school_subscriptions WHERE school_id = ? LIMIT 1");
                                                                    $billQuery->execute([$school['id']]);
                                                                    $billData = $billQuery->fetch(PDO::FETCH_ASSOC);
                                                                    echo htmlspecialchars($billData['billing_cycle'] ?? 'N/A');
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-<?php echo ($school['status'] === 'active') ? 'success' : 'warning'; ?>">
                                                                        <?php echo htmlspecialchars($school['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($school['start_date']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['expires_at']); ?></td>
                                                                <td>
                                                                    <a href="school_details.php?id=<?php echo htmlspecialchars($school['id']); ?>" class="btn btn-sm btn-info" title="View Details">
                                                                        <i class="ti ti-info-circle"></i> Details
                                                                    </a>
                                                                    <button class="btn btn-sm btn-primary" onclick="editSchool(<?php echo htmlspecialchars(json_encode($school)); ?>)" data-toggle="modal" data-target="#editSchoolModal">Edit</button>
                                                                    <button class="btn btn-sm btn-danger" onclick="setDeleteId(<?php echo htmlspecialchars($school['id']); ?>)" data-toggle="modal" data-target="#deleteConfirmModal">Delete</button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="13" class="text-center text-muted">No schools found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>School Name</th>
                                                        <th>Subdomain</th>
                                                        <th>Email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Price/Student</th>
                                                        <th>Billing Cycle</th>
                                                        <th>Status</th>
                                                        <th>Starts</th>
                                                        <th>Expires</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
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
                <form id="addSchoolForm" method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter school name" required>
                        </div>

                        <div class="form-group">
                            <label for="subdomain">Subdomain <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subdomain" name="subdomain" placeholder="e.g., myschool" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_no">Contact No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Enter contact number" required>
                        </div>

                        <div class="form-group">
                            <label for="estimated_students">Estimated Students <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="estimated_students" name="estimated_students" placeholder="Enter number of students" required>
                        </div>

                        <div class="form-group">
                            <label for="plan">Plan <span class="text-danger">*</span></label>
                            <select class="form-control" id="plan" name="plan" required>
                                <option value="">Select Plan</option>
                                <?php if(!empty($plans)): ?>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['name']); ?>" data-price="<?php echo htmlspecialchars($plan['price_per_student_year'] ?? 0); ?>" data-plan-id="<?php echo htmlspecialchars($plan['id']); ?>"><?php echo htmlspecialchars($plan['name']); ?> - Rs <?php echo htmlspecialchars($plan['price_per_student_year']); ?>/student/year</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Hidden fields for pricing -->
                        <input type="hidden" id="price_per_student" name="price_per_student" value="">
                        <input type="hidden" id="plan_id" name="plan_id" value="">

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                                <option value="blocked">Blocked</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>

                        <div class="form-group">
                            <label for="expires_at">Expires At <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at" required>
                        </div>

                        <!-- Billing Section -->
                        <hr>
                        <h6 class="mb-3">Billing Information</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="start_billing" name="start_billing">
                            <label class="form-check-label" for="start_billing">
                                Start Billing Now
                            </label>
                        </div>

                        <div id="billing_section" style="display: none;">
                            <div class="form-group">
                                <label for="total_amount">Total Amount (Readonly) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_amount" name="total_amount" placeholder="0.00" readonly step="0.01">
                                <small class="form-text text-muted">Automatically calculated as: Student Count × Price per Student</small>
                            </div>

                            <!-- Discount/Concession Section -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="discount_type">Discount Type</label>
                                    <select class="form-control" id="discount_type" name="discount_type">
                                        <option value="">-- No Discount --</option>
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount (Rs)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="discount_value">Discount Value</label>
                                    <input type="number" class="form-control" id="discount_value" name="discount_value" placeholder="0.00" min="0" step="0.01">
                                    <small class="form-text text-muted" id="discount_help">Select discount type</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="final_total_amount">Final Amount After Discount (Readonly)</label>
                                <input type="number" class="form-control" id="final_total_amount" name="final_total_amount" placeholder="0.00" readonly step="0.01" style="font-weight: bold; color: green;">
                                <small class="form-text text-muted">Total Amount - Discount</small>
                            </div>

                            <div class="form-group">
                                <label for="paid_amount">Paid Amount</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" placeholder="0.00" min="0" step="0.01">
                                <small class="form-text text-muted">Leave blank or 0 if payment not received yet</small>
                            </div>

                            <div class="form-group" id="payment_method_group" style="display: none;">
                                <label for="payment_method">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method">
                                    <option value="">-- Select Method --</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>

                            <div class="form-group" id="reference_no_group" style="display: none;">
                                <label for="reference_no">Reference No (Transaction ID)</label>
                                <input type="text" class="form-control" id="reference_no" name="reference_no" placeholder="e.g., TXN123456">
                            </div>

                            <div class="form-group" id="received_by_group" style="display: none;">
                                <label for="received_by">Received By</label>
                                <input type="text" class="form-control" id="received_by" name="received_by" placeholder="Name of person who received payment">
                            </div>

                            <div class="form-group">
                                <label for="billing_cycle">Billing Cycle <span class="text-danger">*</span></label>
                                <select class="form-control" id="billing_cycle" name="billing_cycle" required>
                                    <option value="">Select Billing Cycle</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="semi-annual">Semi-Annual</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Add School Modal -->

    <!-- Edit School Modal -->
    <div class="modal fade" id="editSchoolModal" tabindex="-1" role="dialog" aria-labelledby="editSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSchoolModalLabel">Edit School</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="editSchoolId" name="id">
                        
                        <div class="form-group">
                            <label for="editName">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" placeholder="Enter school name" required>
                        </div>

                        <div class="form-group">
                            <label for="editSubdomain">Subdomain <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSubdomain" name="subdomain" placeholder="e.g., myschool" required>
                        </div>

                        <div class="form-group">
                            <label for="editEmail">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editEmail" name="email" placeholder="Enter email" required>
                        </div>

                        <div class="form-group">
                            <label for="editContactNo">Contact No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editContactNo" name="contact_no" placeholder="Enter contact number" required>
                        </div>

                        <div class="form-group">
                            <label for="editEstimatedStudents">Estimated Students <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editEstimatedStudents" name="estimated_students" placeholder="Enter number of students" required>
                        </div>

                        <div class="form-group">
                            <label for="editPlan">Plan <span class="text-danger">*</span></label>
                            <select class="form-control editPlanSelect" id="editPlan" name="plan" required>
                                <option value="">Select Plan</option>
                                <?php if(!empty($plans)): ?>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['name']); ?>" data-price="<?php echo htmlspecialchars($plan['price'] ?? 0); ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Hidden field for price per student -->
                        <input type="hidden" id="editPricePerStudent" name="price_per_student" value="">

                        <div class="form-group">
                            <label for="editBillingCycle">Billing Cycle <span class="text-danger">*</span></label>
                            <select class="form-control" id="editBillingCycle" name="billing_cycle" required>
                                <option value="">Select Billing Cycle</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi-annual">Semi-Annual</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editStatus">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                                <option value="blocked">Blocked</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editStartDate">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editStartDate" name="start_date" required>
                        </div>

                        <div class="form-group">
                            <label for="editExpiresAt">Expires At <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editExpiresAt" name="expires_at" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit School Modal -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Are you sure you want to delete this school?</strong></p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Confirmation Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>
    
    <!-- Modal Script -->
    <script>
        function editSchool(schoolData) {
            console.log('Edit School Data:', schoolData);
            
            // Populate the edit modal with school data
            document.getElementById('editSchoolId').value = schoolData.id || '';
            document.getElementById('editName').value = schoolData.name || '';
            document.getElementById('editSubdomain').value = schoolData.subdomain || '';
            document.getElementById('editEmail').value = schoolData.email || '';
            document.getElementById('editContactNo').value = schoolData.contact_no || '';
            document.getElementById('editEstimatedStudents').value = schoolData.estimated_students || '';
            document.getElementById('editPlan').value = schoolData.plan || '';
            document.getElementById('editStatus').value = schoolData.status || 'active';
            
            // Format and populate dates (remove time portion if present)
            const startDate = schoolData.start_date ? schoolData.start_date.split(' ')[0] : '';
            const expiresAt = schoolData.expires_at ? schoolData.expires_at.split(' ')[0] : '';
            
            console.log('Start Date:', startDate);
            console.log('Expires At:', expiresAt);
            
            document.getElementById('editStartDate').value = startDate;
            document.getElementById('editExpiresAt').value = expiresAt;
            
            console.log('Status set to:', schoolData.status);
            console.log('Start Date set to:', startDate);
            console.log('Expires At set to:', expiresAt);
            
            // Fetch subscription data for this school
            fetch('get_subscription.php?school_id=' + schoolData.id)
                .then(response => response.json())
                .then(data => {
                    console.log('Subscription data:', data);
                    if (data.success && data.subscription) {
                        document.getElementById('editPricePerStudent').value = data.subscription.price_per_student || '';
                        document.getElementById('editBillingCycle').value = data.subscription.billing_cycle || '';
                    }
                })
                .catch(error => console.error('Error fetching subscription:', error));
        }

        function setDeleteId(schoolId) {
            // Set the delete URL when delete button is clicked
            const deleteUrl = 'schools.php?action=delete&id=' + schoolId;
            document.getElementById('confirmDeleteBtn').href = deleteUrl;
        }

        // Function to calculate expiry date based on billing cycle
        function calculateExpiryDate(billingCycle) {
            const today = new Date();
            const expiryDate = new Date(today);
            
            switch(billingCycle) {
                case 'monthly':
                    expiryDate.setMonth(expiryDate.getMonth() + 1);
                    break;
                case 'quarterly':
                    expiryDate.setMonth(expiryDate.getMonth() + 3);
                    break;
                case 'semi-annual':
                    expiryDate.setMonth(expiryDate.getMonth() + 6);
                    break;
                case 'yearly':
                    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                    break;
                default:
                    expiryDate.setMonth(expiryDate.getMonth() + 1); // Default to monthly
            }
            
            return expiryDate.toISOString().split('T')[0];
        }

        // Function to set dates for Add School modal
        function setAddSchoolDates() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').value = today;
            
            const billingCycle = document.getElementById('billing_cycle').value;
            if (billingCycle) {
                document.getElementById('expires_at').value = calculateExpiryDate(billingCycle);
            }
        }

        // Function to set dates for Edit School modal
        function setEditSchoolDates() {
            const billingCycle = document.getElementById('editBillingCycle').value;
            if (billingCycle) {
                document.getElementById('editExpiresAt').value = calculateExpiryDate(billingCycle);
            }
        }

        // Auto-populate price when plan is selected in Add modal
        document.getElementById('plan').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const planId = selectedOption.getAttribute('data-plan-id');
            if (price) {
                document.getElementById('price_per_student').value = price;
                document.getElementById('plan_id').value = planId;
                // Recalculate total amount if billing is enabled
                calculateTotalAmount();
            }
        });

        // Handle Start Billing checkbox
        document.getElementById('start_billing').addEventListener('change', function() {
            const billingSection = document.getElementById('billing_section');
            if (this.checked) {
                billingSection.style.display = 'block';
                calculateTotalAmount();
            } else {
                billingSection.style.display = 'none';
                document.getElementById('total_amount').value = '';
                document.getElementById('paid_amount').value = '';
            }
        });

        // Handle paid amount change
        document.getElementById('paid_amount').addEventListener('change', function() {
            const paidAmount = parseFloat(this.value) || 0;
            const paymentMethodGroup = document.getElementById('payment_method_group');
            const referenceNoGroup = document.getElementById('reference_no_group');
            const receivedByGroup = document.getElementById('received_by_group');
            
            if (paidAmount > 0) {
                paymentMethodGroup.style.display = 'block';
                referenceNoGroup.style.display = 'block';
                receivedByGroup.style.display = 'block';
                document.getElementById('payment_method').setAttribute('required', 'required');
            } else {
                paymentMethodGroup.style.display = 'none';
                referenceNoGroup.style.display = 'none';
                receivedByGroup.style.display = 'none';
                document.getElementById('payment_method').removeAttribute('required');
                document.getElementById('reference_no').value = '';
                document.getElementById('received_by').value = '';
            }
        });

        // Calculate total amount
        function calculateTotalAmount() {
            const studentCount = parseInt(document.getElementById('estimated_students').value) || 0;
            const pricePerStudent = parseFloat(document.getElementById('price_per_student').value) || 0;
            const totalAmount = studentCount * pricePerStudent;
            document.getElementById('total_amount').value = totalAmount.toFixed(2);
            calculateFinalAmount();
        }

        // Calculate final amount after discount
        function calculateFinalAmount() {
            const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
            const discountType = document.getElementById('discount_type').value;
            const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
            
            let finalAmount = totalAmount;
            
            if (discountType === 'percentage') {
                const discountPercentage = (totalAmount * discountValue) / 100;
                finalAmount = totalAmount - discountPercentage;
            } else if (discountType === 'fixed') {
                finalAmount = totalAmount - discountValue;
            }
            
            // Ensure final amount is not negative
            finalAmount = Math.max(0, finalAmount);
            document.getElementById('final_total_amount').value = finalAmount.toFixed(2);
        }

        // Handle discount type change
        document.getElementById('discount_type').addEventListener('change', function() {
            const helpText = document.getElementById('discount_help');
            const discountValueInput = document.getElementById('discount_value');
            
            if (this.value === 'percentage') {
                helpText.textContent = 'Enter discount percentage (e.g., 10 for 10%)';
                discountValueInput.placeholder = '10';
            } else if (this.value === 'fixed') {
                helpText.textContent = 'Enter fixed discount amount in Rs';
                discountValueInput.placeholder = '1000';
            } else {
                helpText.textContent = 'Select discount type';
                discountValueInput.placeholder = '0.00';
            }
            
            discountValueInput.value = '';
            calculateFinalAmount();
        });

        // Handle discount value change
        document.getElementById('discount_value').addEventListener('change', function() {
            calculateFinalAmount();
        });
        
        document.getElementById('discount_value').addEventListener('keyup', function() {
            calculateFinalAmount();
        });

        // Handle student count change
        document.getElementById('estimated_students').addEventListener('change', function() {
            if (document.getElementById('start_billing').checked) {
                calculateTotalAmount();
            }
        });

        // Update expiry date when billing cycle changes in Add modal
        document.getElementById('billing_cycle').addEventListener('change', function() {
            setAddSchoolDates();
        });

        // Auto-populate price when plan is selected in Edit modal
        document.querySelectorAll('.editPlanSelect').forEach(select => {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                if (price) {
                    document.getElementById('editPricePerStudent').value = price;
                }
            });
        });

        // Update expiry date when billing cycle changes in Edit modal
        if (document.getElementById('editBillingCycle')) {
            document.getElementById('editBillingCycle').addEventListener('change', function() {
                setEditSchoolDates();
            });
        }

        // Reset form when modal opens for adding new school
        document.getElementById('addSchoolModal').addEventListener('show.bs.modal', function() {
            const form = document.getElementById('addSchoolForm');
            form.reset();
            document.getElementById('price_per_student').value = '';
            document.getElementById('plan_id').value = '';
            document.getElementById('start_billing').checked = false;
            document.getElementById('billing_section').style.display = 'none';
            // Set today's date and clear expiry
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').value = today;
            document.getElementById('expires_at').value = '';
        });

        // Reset form on close modal
        document.getElementById('addSchoolModal').addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addSchoolForm');
            form.reset();
            document.getElementById('billing_section').style.display = 'none';
        });
    </script>