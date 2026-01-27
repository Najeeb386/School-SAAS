<?php
session_start();
require_once __DIR__ . '/../../../../Config/connection.php';

// Get financial data for reports
$reportData = [];

// Fetch expenses
$expenseStmt = $DB_con->prepare("SELECT SUM(amount) as total_expenses FROM saas_expenses WHERE status = 'approved'");
$expenseStmt->execute();
$expenseData = $expenseStmt->fetch(PDO::FETCH_ASSOC);
$reportData['total_expenses'] = $expenseData['total_expenses'] ?? 0;

// Fetch revenue from payments
$revenueStmt = $DB_con->prepare("SELECT SUM(paid_amount) as total_revenue FROM saas_payments");
$revenueStmt->execute();
$revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);
$reportData['total_revenue'] = $revenueData['total_revenue'] ?? 0;

// Fetch due payments
$dueStmt = $DB_con->prepare("SELECT SUM(total_amount - paid_amount) as total_due FROM saas_billing_cycles WHERE status IN ('due', 'partial', 'overdue')");
$dueStmt->execute();
$dueData = $dueStmt->fetch(PDO::FETCH_ASSOC);
$reportData['total_due'] = $dueData['total_due'] ?? 0;

// Fetch defaulters count
$defaultersStmt = $DB_con->prepare("SELECT COUNT(DISTINCT school_id) as defaulters_count FROM saas_billing_cycles WHERE status = 'overdue'");
$defaultersStmt->execute();
$defaultersData = $defaultersStmt->fetch(PDO::FETCH_ASSOC);
$reportData['defaulters_count'] = $defaultersData['defaulters_count'] ?? 0;

// Fetch active subscriptions count
$activeSubStmt = $DB_con->prepare("SELECT COUNT(*) as active_count FROM saas_school_subscriptions WHERE status = 'active'");
$activeSubStmt->execute();
$activeSubData = $activeSubStmt->fetch(PDO::FETCH_ASSOC);
$reportData['active_subscriptions'] = $activeSubData['active_count'] ?? 0;

// Fetch renewals needed (expiring within 30 days)
$renewalStmt = $DB_con->prepare("SELECT COUNT(*) as renewal_count FROM saas_school_subscriptions WHERE status = 'active' AND DATEDIFF(end_date, CURDATE()) <= 30 AND DATEDIFF(end_date, CURDATE()) > 0");
$renewalStmt->execute();
$renewalData = $renewalStmt->fetch(PDO::FETCH_ASSOC);
$reportData['renewals_needed'] = $renewalData['renewal_count'] ?? 0;

// Calculate Profit/Loss
$profit_loss = $reportData['total_revenue'] - $reportData['total_expenses'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>School SAAS - Reports</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Financial and operational reports dashboard" />
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
        .report-card {
            border-left: 4px solid #007bff;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .report-card.revenue {
            border-left-color: #28a745;
        }
        .report-card.expenses {
            border-left-color: #dc3545;
        }
        .report-card.profit {
            border-left-color: #ffc107;
        }
        .report-card h6 {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.875rem;
        }
        .report-card .amount {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 20px;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
            background-color: transparent;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #007bff;
        }
        .tab-content {
            padding: 20px 0;
        }
        .tab-pane {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .filter-section h6 {
            margin-bottom: 15px;
            color: #495057;
            font-weight: 600;
        }
        .btn-action-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        @media print {
            .filter-section,
            .btn-action-group,
            .nav-tabs,
            .app-navbar,
            .app-header {
                display: none !important;
            }
            .container-fluid {
                width: 100%;
                padding: 0;
            }
            .tab-content {
                display: block !important;
            }
            .tab-pane {
                display: block !important;
                page-break-after: avoid;
            }
            .tab-pane:not(.active) {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            table {
                font-size: 12px;
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
                <!-- begin app-navbar -->
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
                                        <h1>Reports</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Reports
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                <!-- end page title -->
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-action-group">
                                    <button class="btn btn-primary" id="printBtn" onclick="printReport()">
                                        <i class="ti ti-printer"></i> Print Report
                                    </button>
                                    <button class="btn btn-danger" id="pdfBtn" onclick="savePDF()">
                                        <i class="ti ti-file-pdf"></i> Save as PDF
                                    </button>
                                    <button class="btn btn-secondary" id="resetBtn" onclick="resetFilters()">
                                        <i class="ti ti-reload"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Key Metrics Row -->
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="report-card revenue">
                                    <h6>Total Revenue</h6>
                                    <div class="amount text-success">₨<?php echo number_format($reportData['total_revenue'], 2); ?></div>
                                    <small class="text-muted">From all payments received</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="report-card expenses">
                                    <h6>Total Expenses</h6>
                                    <div class="amount text-danger">₨<?php echo number_format($reportData['total_expenses'], 2); ?></div>
                                    <small class="text-muted">Approved expenses only</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="report-card profit">
                                    <h6>Profit / Loss</h6>
                                    <div class="amount <?php echo ($profit_loss >= 0) ? 'text-success' : 'text-danger'; ?>">
                                        ₨<?php echo number_format($profit_loss, 2); ?>
                                    </div>
                                    <small class="text-muted">Revenue - Expenses</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="report-card">
                                    <h6>Total Due</h6>
                                    <div class="amount text-warning">₨<?php echo number_format($reportData['total_due'], 2); ?></div>
                                    <small class="text-muted">Pending payments</small>
                                </div>
                            </div>
                        </div>

                        <!-- Reports Tabs -->
                        <div class="row m-t-30">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="profit-loss-tab" data-toggle="tab" href="#profitLoss" role="tab">
                                                    <i class="ti ti-bar-chart"></i> Profit & Loss
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="revenue-tab" data-toggle="tab" href="#revenue" role="tab">
                                                    <i class="ti ti-arrow-up"></i> Revenue
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="expenses-tab" data-toggle="tab" href="#expenses" role="tab">
                                                    <i class="ti ti-arrow-down"></i> Expenses
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="due-payments-tab" data-toggle="tab" href="#duePayments" role="tab">
                                                    <i class="ti ti-alert"></i> Due Payments
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="defaulters-tab" data-toggle="tab" href="#defaulters" role="tab">
                                                    <i class="ti ti-close"></i> Defaulters
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="subscriptions-tab" data-toggle="tab" href="#subscriptions" role="tab">
                                                    <i class="ti ti-check"></i> Active Subscriptions
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="renewals-tab" data-toggle="tab" href="#renewals" role="tab">
                                                    <i class="ti ti-reload"></i> Renewals
                                                </a>
                                            </li>
                                        </ul>

                                        <!-- Filters Section -->
                                        <div class="filter-section">
                                            <h6><i class="ti ti-filter"></i> Report Filters</h6>
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label>Quick Filters</label>
                                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="setQuickFilter('today')">Today</button>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="setQuickFilter('weekly')">This Week</button>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="setQuickFilter('monthly')">This Month</button>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="setQuickFilter('yearly')">This Year</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label for="fromDate">From Date</label>
                                                    <input type="date" id="fromDate" class="form-control" onchange="applyFilters()" value="<?php echo date('Y-m-01'); ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="toDate">To Date</label>
                                                    <input type="date" id="toDate" class="form-control" onchange="applyFilters()" value="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="categoryFilter">Category</label>
                                                    <select id="categoryFilter" class="form-control" onchange="applyFilters()">
                                                        <option value="">All Categories</option>
                                                        <option value="hosting">Hosting</option>
                                                        <option value="salary">Salary</option>
                                                        <option value="marketing">Marketing</option>
                                                        <option value="maintenance">Maintenance</option>
                                                        <option value="software">Software</option>
                                                        <option value="office">Office</option>
                                                        <option value="misc">Miscellaneous</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="statusFilter">Status</label>
                                                    <select id="statusFilter" class="form-control" onchange="applyFilters()">
                                                        <option value="">All Status</option>
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="suspended">Suspended</option>
                                                        <option value="overdue">Overdue</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab content -->
                                        <div class="tab-content" id="reportTabContent">
                                            <!-- Profit & Loss Tab -->
                                            <div class="tab-pane fade show active" id="profitLoss" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Profit & Loss Statement</h5>
                                                    <table class="table table-hover">
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Total Revenue</strong></td>
                                                                <td class="text-right text-success"><strong id="plRevenue">₨<?php echo number_format($reportData['total_revenue'], 2); ?></strong></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Total Expenses</strong></td>
                                                                <td class="text-right text-danger"><strong id="plExpenses">₨<?php echo number_format($reportData['total_expenses'], 2); ?></strong></td>
                                                            </tr>
                                                            <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
                                                                <td><strong>Net Profit / Loss</strong></td>
                                                                <td class="text-right"><strong id="plNetProfit" class="<?php echo ($profit_loss >= 0) ? 'text-success' : 'text-danger'; ?>">₨<?php echo number_format($profit_loss, 2); ?></strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Revenue Tab -->
                                            <div class="tab-pane fade" id="revenue" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Revenue Report</h5>
                                                    <div class="alert alert-info">
                                                        <i class="ti ti-info-circle"></i> Total revenue from all payments: <strong>₨<?php echo number_format($reportData['total_revenue'], 2); ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>School</th>
                                                                <th>Amount</th>
                                                                <th>Payment Date</th>
                                                                <th>Payment Method</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $revenueList = $DB_con->query("SELECT sp.*, s.name as school_name FROM saas_payments sp JOIN schools s ON sp.school_id = s.id ORDER BY sp.payment_date DESC LIMIT 10");
                                                            $revenues = $revenueList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($revenues)):
                                                                foreach ($revenues as $rev):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($rev['school_name']); ?></td>
                                                                    <td class="text-right">₨<?php echo number_format($rev['paid_amount'], 2); ?></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($rev['payment_date'])); ?></td>
                                                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($rev['payment_method']); ?></span></td>
                                                                    <td><span class="badge badge-success">Received</span></td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted">No revenue records found</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Expenses Tab -->
                                            <div class="tab-pane fade" id="expenses" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Expenses Report</h5>
                                                    <div class="alert alert-danger">
                                                        <i class="ti ti-alert"></i> Total approved expenses: <strong>₨<?php echo number_format($reportData['total_expenses'], 2); ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>Title</th>
                                                                <th>Category</th>
                                                                <th>Amount</th>
                                                                <th>Date</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $expenseList = $DB_con->query("SELECT * FROM saas_expenses WHERE status = 'approved' ORDER BY expense_date DESC LIMIT 10");
                                                            $expenses = $expenseList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($expenses)):
                                                                foreach ($expenses as $exp):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($exp['title']); ?></td>
                                                                    <td><span class="badge badge-secondary"><?php echo htmlspecialchars($exp['category']); ?></span></td>
                                                                    <td class="text-right">₨<?php echo number_format($exp['amount'], 2); ?></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($exp['expense_date'])); ?></td>
                                                                    <td><span class="badge badge-success">Approved</span></td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted">No expenses found</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Due Payments Tab -->
                                            <div class="tab-pane fade" id="duePayments" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Due Payments Report</h5>
                                                    <div class="alert alert-warning">
                                                        <i class="ti ti-alert"></i> Total due amount: <strong>₨<?php echo number_format($reportData['total_due'], 2); ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>School</th>
                                                                <th>Total Amount</th>
                                                                <th>Paid Amount</th>
                                                                <th>Due Amount</th>
                                                                <th>Due Date</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $dueList = $DB_con->query("SELECT sbc.*, s.name as school_name FROM saas_billing_cycles sbc JOIN schools s ON sbc.school_id = s.id WHERE sbc.status IN ('due', 'partial', 'overdue') ORDER BY sbc.due_date ASC");
                                                            $dues = $dueList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($dues)):
                                                                foreach ($dues as $due):
                                                                    $dueAmount = $due['total_amount'] - $due['paid_amount'];
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($due['school_name']); ?></td>
                                                                    <td class="text-right">₨<?php echo number_format($due['total_amount'], 2); ?></td>
                                                                    <td class="text-right">₨<?php echo number_format($due['paid_amount'], 2); ?></td>
                                                                    <td class="text-right text-danger"><strong>₨<?php echo number_format($dueAmount, 2); ?></strong></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($due['due_date'])); ?></td>
                                                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($due['status']); ?></span></td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted">No due payments found</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Defaulters Tab -->
                                            <div class="tab-pane fade" id="defaulters" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Defaulters Report</h5>
                                                    <div class="alert alert-danger">
                                                        <i class="ti ti-close"></i> Total defaulters: <strong><?php echo $reportData['defaulters_count']; ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>School</th>
                                                                <th>Contact</th>
                                                                <th>Overdue Amount</th>
                                                                <th>Days Overdue</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $defaultersList = $DB_con->query("SELECT DISTINCT sbc.school_id, s.name, s.contact_no, SUM(sbc.total_amount - sbc.paid_amount) as overdue_amount, DATEDIFF(CURDATE(), sbc.due_date) as days_overdue FROM saas_billing_cycles sbc JOIN schools s ON sbc.school_id = s.id WHERE sbc.status = 'overdue' GROUP BY sbc.school_id ORDER BY days_overdue DESC");
                                                            $defaulters = $defaultersList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($defaulters)):
                                                                foreach ($defaulters as $def):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($def['name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($def['contact_no']); ?></td>
                                                                    <td class="text-right text-danger"><strong>₨<?php echo number_format($def['overdue_amount'], 2); ?></strong></td>
                                                                    <td><span class="badge badge-danger"><?php echo $def['days_overdue']; ?> days</span></td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning">Send Notice</button>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted">No defaulters found</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Active Subscriptions Tab -->
                                            <div class="tab-pane fade" id="subscriptions" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Active Subscriptions Report</h5>
                                                    <div class="alert alert-success">
                                                        <i class="ti ti-check"></i> Total active subscriptions: <strong><?php echo $reportData['active_subscriptions']; ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>School</th>
                                                                <th>Plan</th>
                                                                <th>Students</th>
                                                                <th>Price Per Student</th>
                                                                <th>Billing Cycle</th>
                                                                <th>End Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $activeSubsList = $DB_con->query("SELECT sss.*, s.name as school_name FROM saas_school_subscriptions sss JOIN schools s ON sss.school_id = s.id WHERE sss.status = 'active' ORDER BY sss.end_date ASC");
                                                            $activeSubs = $activeSubsList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($activeSubs)):
                                                                foreach ($activeSubs as $sub):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($sub['school_name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($sub['plan_name']); ?></td>
                                                                    <td><?php echo $sub['students_count']; ?></td>
                                                                    <td class="text-right">₨<?php echo number_format($sub['price_per_student'], 2); ?></td>
                                                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($sub['billing_cycle']); ?></span></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($sub['end_date'])); ?></td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted">No active subscriptions found</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Renewals Tab -->
                                            <div class="tab-pane fade" id="renewals" role="tabpanel">
                                                <div class="m-t-20">
                                                    <h5>Renewals Needed</h5>
                                                    <div class="alert alert-info">
                                                        <i class="ti ti-reload"></i> Subscriptions expiring within 30 days: <strong><?php echo $reportData['renewals_needed']; ?></strong>
                                                    </div>
                                                    <table class="table table-striped table-bordered datatable">
                                                        <thead>
                                                            <tr>
                                                                <th>School</th>
                                                                <th>Plan</th>
                                                                <th>Current Students</th>
                                                                <th>Expiry Date</th>
                                                                <th>Days Left</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $renewalsList = $DB_con->query("SELECT sss.*, s.name as school_name, s.estimated_students, DATEDIFF(sss.end_date, CURDATE()) as days_left FROM saas_school_subscriptions sss JOIN schools s ON sss.school_id = s.id WHERE sss.status = 'active' AND DATEDIFF(sss.end_date, CURDATE()) <= 30 AND DATEDIFF(sss.end_date, CURDATE()) > 0 ORDER BY sss.end_date ASC");
                                                            $renewals = $renewalsList->fetchAll(PDO::FETCH_ASSOC);
                                                            if (!empty($renewals)):
                                                                foreach ($renewals as $ren):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($ren['school_name']); ?></td>
                                                                    <td><?php echo htmlspecialchars($ren['plan_name']); ?></td>
                                                                    <td><?php echo $ren['estimated_students']; ?></td>
                                                                    <td><?php echo date('Y-m-d', strtotime($ren['end_date'])); ?></td>
                                                                    <td>
                                                                        <?php if ($ren['days_left'] <= 7): ?>
                                                                            <span class="badge badge-danger"><?php echo $ren['days_left']; ?> days</span>
                                                                        <?php else: ?>
                                                                            <span class="badge badge-warning"><?php echo $ren['days_left']; ?> days</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <a href="../subscription/subscription.php" class="btn btn-sm btn-primary">Process Renewal</a>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                                endforeach;
                                                            else:
                                                            ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted">No renewals needed</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>
            <!-- end app-container -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- html2pdf library for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        // Print function
        function printReport() {
            window.print();
        }

        // Save PDF function
        function savePDF() {
            const element = document.querySelector('.app-main');
            const reportTitle = 'School-SAAS-Reports-' + new Date().toISOString().split('T')[0];
            
            const opt = {
                margin: 5,
                filename: reportTitle + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, allowTaint: true, useCORS: true },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };

            html2pdf().set(opt).from(element).save();
        }

        // Quick filter functions
        function setQuickFilter(period) {
            const today = new Date();
            let fromDate, toDate = new Date();
            toDate = toDate.toISOString().split('T')[0];

            switch(period) {
                case 'today':
                    fromDate = new Date();
                    fromDate = fromDate.toISOString().split('T')[0];
                    break;
                case 'weekly':
                    fromDate = new Date();
                    fromDate.setDate(fromDate.getDate() - 7);
                    fromDate = fromDate.toISOString().split('T')[0];
                    break;
                case 'monthly':
                    fromDate = new Date();
                    fromDate.setMonth(fromDate.getMonth() - 1);
                    fromDate = fromDate.toISOString().split('T')[0];
                    break;
                case 'yearly':
                    fromDate = new Date();
                    fromDate.setFullYear(fromDate.getFullYear() - 1);
                    fromDate = fromDate.toISOString().split('T')[0];
                    break;
            }

            document.getElementById('fromDate').value = fromDate;
            document.getElementById('toDate').value = toDate;
            applyFilters();
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('fromDate').value = '<?php echo date('Y-m-01'); ?>';
            document.getElementById('toDate').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            applyFilters();
        }

        // Apply filters
        function applyFilters() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const status = document.getElementById('statusFilter').value.toLowerCase();

            // Filter all tables
            filterTable('revenue', fromDate, toDate, '', '');
            filterTable('expenses', fromDate, toDate, category, '');
            filterTable('duePayments', fromDate, toDate, '', status);
            filterTable('defaulters', fromDate, toDate, '', '');
            filterTable('subscriptions', fromDate, toDate, '', status);
            filterTable('renewals', fromDate, toDate, '', '');

            // Update Profit & Loss based on filtered data
            updateProfitAndLoss();
        }

        // Update Profit and Loss based on visible rows
        function updateProfitAndLoss() {
            let totalRevenue = 0;
            let totalExpenses = 0;

            // Calculate revenue from visible rows in revenue tab
            const revenueRows = document.querySelectorAll('#revenue table tbody tr');
            revenueRows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    const amountCell = row.querySelectorAll('td')[1];
                    if (amountCell) {
                        const amount = parseFloat(amountCell.textContent.replace(/[₨,]/g, ''));
                        if (!isNaN(amount)) {
                            totalRevenue += amount;
                        }
                    }
                }
            });

            // Calculate expenses from visible rows in expenses tab
            const expenseRows = document.querySelectorAll('#expenses table tbody tr');
            expenseRows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    const amountCell = row.querySelectorAll('td')[2];
                    if (amountCell) {
                        const amount = parseFloat(amountCell.textContent.replace(/[₨,]/g, ''));
                        if (!isNaN(amount)) {
                            totalExpenses += amount;
                        }
                    }
                }
            });

            // Calculate profit/loss
            const netProfit = totalRevenue - totalExpenses;

            // Update P&L display
            const revenueDisplay = document.getElementById('plRevenue');
            const expensesDisplay = document.getElementById('plExpenses');
            const profitDisplay = document.getElementById('plNetProfit');

            if (revenueDisplay) {
                revenueDisplay.textContent = '₨' + totalRevenue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
            if (expensesDisplay) {
                expensesDisplay.textContent = '₨' + totalExpenses.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
            if (profitDisplay) {
                profitDisplay.textContent = '₨' + netProfit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                // Update color based on profit/loss
                if (netProfit >= 0) {
                    profitDisplay.classList.remove('text-danger');
                    profitDisplay.classList.add('text-success');
                } else {
                    profitDisplay.classList.remove('text-success');
                    profitDisplay.classList.add('text-danger');
                }
            }
        }

        // Generic filter function for tables
        function filterTable(tabId, fromDate, toDate, category, status) {
            const tabElement = document.getElementById(tabId);
            if (!tabElement) return;

            const rows = tabElement.querySelectorAll('table tbody tr');
            
            rows.forEach(function(row) {
                let show = true;

                // Get all cells in the row
                const cells = row.querySelectorAll('td');
                if (cells.length === 0) return;

                // Different logic based on tab
                if (tabId === 'revenue') {
                    // Payment Date is typically in column 2
                    const dateText = cells[2] ? cells[2].textContent.trim() : '';
                    show = filterByDate(dateText, fromDate, toDate);
                } 
                else if (tabId === 'expenses') {
                    // Category is in column 1, Date is in column 3
                    const categoryText = cells[1] ? cells[1].textContent.toLowerCase().trim() : '';
                    const dateText = cells[3] ? cells[3].textContent.trim() : '';
                    show = filterByDate(dateText, fromDate, toDate) && 
                           (!category || categoryText.includes(category));
                } 
                else if (tabId === 'duePayments') {
                    // Due Date is in column 4, Status is in column 5
                    const dateText = cells[4] ? cells[4].textContent.trim() : '';
                    const statusText = cells[5] ? cells[5].textContent.toLowerCase().trim() : '';
                    show = filterByDate(dateText, fromDate, toDate) && 
                           (!status || statusText.includes(status));
                } 
                else if (tabId === 'defaulters') {
                    // Filter by date if available
                    show = true;
                } 
                else if (tabId === 'subscriptions') {
                    // End Date is in column 5
                    const dateText = cells[5] ? cells[5].textContent.trim() : '';
                    show = filterByDate(dateText, fromDate, toDate);
                } 
                else if (tabId === 'renewals') {
                    // Expiry Date is in column 3
                    const dateText = cells[3] ? cells[3].textContent.trim() : '';
                    show = filterByDate(dateText, fromDate, toDate);
                }

                row.style.display = show ? '' : 'none';
            });
        }

        // Helper function to filter by date
        function filterByDate(dateString, fromDate, toDate) {
            if (!dateString) return true;

            try {
                // Parse the date string (format: YYYY-MM-DD)
                const rowDate = new Date(dateString);
                const from = fromDate ? new Date(fromDate) : new Date('1900-01-01');
                const to = toDate ? new Date(toDate) : new Date('2100-12-31');

                return rowDate >= from && rowDate <= to;
            } catch (e) {
                return true;
            }
        }

        // Initialize on document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTables if jQuery is available
            if (typeof jQuery !== 'undefined' && $.fn.DataTable) {
                $('.datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'asc']],
                    lengthChange: true,
                    searching: true
                });
            }
        });
    </script>
</body>
</html>
