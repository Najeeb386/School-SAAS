<?php
// Initialize database connection
require_once __DIR__ . '/../../../../Core/database.php';
require_once __DIR__ . '/../../Controllers/billing_cycles_controller.php';

$db = Database::connect();
$billingController = new BillingCyclesController($db);

// Fetch unpaid billing cycles
$unpaidBillings = $billingController->getUnpaid();
$totalUnpaid = $billingController->getTotalUnpaidAmount();

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
    return '$' . number_format($amount, 2);
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
                                                <a class="nav-link" id="due-tab" data-toggle="tab" href="#due" role="tab" aria-controls="due" aria-selected="false">
                                                    <i class="ti ti-alert"></i> Due Payments
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="defaulters-tab" data-toggle="tab" href="#defaulters" role="tab" aria-controls="defaulters" aria-selected="false">
                                                    <i class="ti ti-ban"></i> Defaulters
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
                                                                    <th>Amount</th>
                                                                    <th>Payment Date</th>
                                                                    <th>Payment Method</th>
                                                                    <th>Reference No</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>#P001</td>
                                                                    <td>John Doe</td>
                                                                    <td>$5,000</td>
                                                                    <td>2026-01-20</td>
                                                                    <td>Bank Transfer</td>
                                                                    <td>REF001</td>
                                                                    <td><span class="badge badge-success">Completed</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary">Edit</button>
                                                                        <button class="btn btn-sm btn-danger">Delete</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>#P002</td>
                                                                    <td>Jane Smith</td>
                                                                    <td>$3,500</td>
                                                                    <td>2026-01-19</td>
                                                                    <td>Cash</td>
                                                                    <td>REF002</td>
                                                                    <td><span class="badge badge-success">Completed</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary">Edit</button>
                                                                        <button class="btn btn-sm btn-danger">Delete</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>#P003</td>
                                                                    <td>ABC School</td>
                                                                    <td>$10,000</td>
                                                                    <td>2026-01-18</td>
                                                                    <td>Card</td>
                                                                    <td>REF003</td>
                                                                    <td><span class="badge badge-success">Completed</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary">Edit</button>
                                                                        <button class="btn btn-sm btn-danger">Delete</button>
                                                                    </td>
                                                                </tr>
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
                                                            <div class="alert alert-danger" role="alert">
                                                                <strong>Total Defaulters:</strong> 8 | <strong>Total Amount at Risk:</strong> $45,000
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table id="defaultersTable" class="display compact table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Student/School Name</th>
                                                                    <th>Outstanding Amount</th>
                                                                    <th>Last Payment Date</th>
                                                                    <th>Days Since Last Payment</th>
                                                                    <th>Contact</th>
                                                                    <th>Email</th>
                                                                    <th>Risk Level</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Robert Brown</td>
                                                                    <td>$15,000</td>
                                                                    <td>2025-11-15</td>
                                                                    <td><span class="badge badge-danger">70</span></td>
                                                                    <td>555-0105</td>
                                                                    <td>robert@email.com</td>
                                                                    <td><span class="badge badge-danger">High</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning">Take Action</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Global Learning Center</td>
                                                                    <td>$20,000</td>
                                                                    <td>2025-11-01</td>
                                                                    <td><span class="badge badge-danger">84</span></td>
                                                                    <td>555-0106</td>
                                                                    <td>info@globallearning.com</td>
                                                                    <td><span class="badge badge-danger">High</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning">Take Action</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Emma Harris</td>
                                                                    <td>$10,000</td>
                                                                    <td>2025-10-30</td>
                                                                    <td><span class="badge badge-danger">86</span></td>
                                                                    <td>555-0107</td>
                                                                    <td>emma@email.com</td>
                                                                    <td><span class="badge badge-danger">Critical</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning">Take Action</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>Student/School Name</th>
                                                                    <th>Outstanding Amount</th>
                                                                    <th>Last Payment Date</th>
                                                                    <th>Days Since Last Payment</th>
                                                                    <th>Contact</th>
                                                                    <th>Email</th>
                                                                    <th>Risk Level</th>
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
    <!-- End Block Confirmation Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- Block Confirmation Script -->
    <script>
        var selectedSchoolName = '';
        
        // Handle block button click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('block-school-btn')) {
                selectedSchoolName = e.target.getAttribute('data-school-name');
                document.getElementById('schoolNameDisplay').textContent = selectedSchoolName;
            }
        });
        
        // Handle confirm block button
        document.getElementById('confirmBlockBtn').addEventListener('click', function() {
            console.log('School blocked:', selectedSchoolName);
            
            // Show success message
            alert('School "' + selectedSchoolName + '" has been successfully blocked!');
            
            // Close modal
            $('#blockConfirmModal').modal('hide');
            
            // Here you can add API call to block the school
            // Example:
            // fetch('/api/schools/block', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json'
            //     },
            //     body: JSON.stringify({
            //         schoolName: selectedSchoolName
            //     })
            // })
            // .then(response => response.json())
            // .then(data => {
            //     console.log('Success:', data);
            //     // Refresh the table
            //     location.reload();
            // });
        });
    </script>
    <!-- End Block Confirmation Script -->
