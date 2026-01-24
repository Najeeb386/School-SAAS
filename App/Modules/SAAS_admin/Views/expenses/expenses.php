<?php
session_start();

// Database connection and controller
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Controllers/expense_controller.php';

$expenseController = new ExpenseController($DB_con);

// Handle POST requests (create, update, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete']) && !empty($_POST['expense_id'])) {
            if ($expenseController->delete($_POST['expense_id'])) {
                $_SESSION['success'] = 'Expense deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete expense!';
            }
        } else {
            if (!empty($_POST['expense_id'])) {
                if ($expenseController->update()) {
                    $_SESSION['success'] = 'Expense updated successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to update expense!';
                }
            } else {
                if ($expenseController->store()) {
                    $_SESSION['success'] = 'Expense added successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to add expense!';
                }
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Expense handler error: " . $e->getMessage());
    }
    header('Location: expenses.php');
    exit;
}

$expenses = $expenseController->index();

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
                                        <h3>Expenese</h3>
                                    </div>
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
                                        <div class="d-flex justify-content-between mb-3">
                                            <div></div>
                                            <div>
                                                <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">Add Expense</button>
                                            </div>
                                        </div>

                                        <div class="datatable-wrapper table-responsive">
                                            <table id="datatable" class="display compact table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Category</th>
                                                        <th>Amount</th>
                                                        <th>Payment Method</th>
                                                        <th>Expense Date</th>
                                                        <th>Vendor</th>
                                                        <th>Invoice No</th>
                                                        <th>Created By</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($expenses)) : ?>
                                                        <?php foreach ($expenses as $exp) : ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($exp['title'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($exp['category'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars(number_format((float)($exp['amount'] ?? 0), 2)) ?></td>
                                                                <td><?= htmlspecialchars($exp['payment_method'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($exp['expense_date'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($exp['vendor_name'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($exp['invoice_no'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($exp['creator_name'] ?? '') ?></td>
                                                                <td>
                                                                    <?php if (!empty($exp['status'])) : ?>
                                                                        <span class="badge badge-info"><?= htmlspecialchars($exp['status']) ?></span>
                                                                    <?php else : ?>
                                                                        <span class="badge badge-secondary">N/A</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="expense_detail.php?id=<?= htmlspecialchars($exp['expense_id']) ?>" class="btn btn-sm btn-info">View</a>
                                                                    <button type="button" class="btn btn-sm btn-primary edit-expense-btn"
                                                                        data-expense_id="<?= htmlspecialchars($exp['expense_id']) ?>"
                                                                        data-title="<?= htmlspecialchars($exp['title'] ?? '') ?>"
                                                                        data-description="<?= htmlspecialchars($exp['description'] ?? '') ?>"
                                                                        data-category="<?= htmlspecialchars($exp['category'] ?? '') ?>"
                                                                        data-amount="<?= htmlspecialchars($exp['amount'] ?? '') ?>"
                                                                        data-payment_method="<?= htmlspecialchars($exp['payment_method'] ?? '') ?>"
                                                                        data-expense_date="<?= htmlspecialchars($exp['expense_date'] ?? '') ?>"
                                                                        data-is_recurring="<?= htmlspecialchars($exp['is_recurring'] ?? 0) ?>"
                                                                        data-recurring_cycle="<?= htmlspecialchars($exp['recurring_cycle'] ?? '') ?>"
                                                                        data-vendor_name="<?= htmlspecialchars($exp['vendor_name'] ?? '') ?>"
                                                                        data-invoice_no="<?= htmlspecialchars($exp['invoice_no'] ?? '') ?>"
                                                                        data-status="<?= htmlspecialchars($exp['status'] ?? '') ?>"
                                                                        >Edit</button>

                                                                    <form method="post" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Delete this expense?');">
                                                                        <input type="hidden" name="expense_id" value="<?= htmlspecialchars($exp['expense_id']) ?>">
                                                                        <input type="hidden" name="delete" value="1">
                                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr><td colspan="9" class="text-center">No expenses found.</td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Category</th>
                                                        <th>Amount</th>
                                                        <th>Payment Method</th>
                                                        <th>Expense Date</th>
                                                        <th>Vendor</th>
                                                        <th>Invoice No</th>
                                                        <th>Created By</th>
                                                        <th>Status</th>
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

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExpenseModalLabel">Add Expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" id="addExpenseForm">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Title</label>
                                <input name="title" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Category</label>
                                <select name="category" class="form-control" required>
                                    <option value="">Select category</option>
                                    <option value="hosting">hosting</option>
                                    <option value="salary">salary</option>
                                    <option value="marketing">marketing</option>
                                    <option value="maintenance">maintenance</option>
                                    <option value="software">software</option>
                                    <option value="office">office</option>
                                    <option value="misc">misc</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <!-- Created by is set from logged-in user session -->
                            <div class="form-group col-md-4">
                                <label>Amount</label>
                                <input name="amount" type="number" step="0.01" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="">Select method</option>
                                    <option value="cash">cash</option>
                                    <option value="bank">bank</option>
                                    <option value="card">card</option>
                                    <option value="online">online</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Expense Date</label>
                                <input name="expense_date" type="date" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Vendor Name</label>
                                <input name="vendor_name" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Invoice No</label>
                                <input name="invoice_no" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="paid">paid</option>
                                    <option value="pending">pending</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="form-group col-md-3">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="add_is_recurring" name="is_recurring" value="1">
                                    <label class="form-check-label" for="add_is_recurring">Recurring</label>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Recurring Cycle</label>
                                <select name="recurring_cycle" class="form-control">
                                    <option value="">Select cycle</option>
                                    <option value="yearly">yearly</option>
                                    <option value="monthly">monthly</option>
                                </select>
                            </div>
                            <!-- Created by is set from logged-in user session -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Add Expense Modal -->

    <!-- Edit Expense Modal -->
    <div class="modal fade" id="editExpenseModal" tabindex="-1" role="dialog" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" id="editExpenseForm">
                    <input type="hidden" name="expense_id" id="edit_expense_id">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Title</label>
                                <input name="title" id="edit_title" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Category</label>
                                <select name="category" id="edit_category" class="form-control" required>
                                    <option value="">Select category</option>
                                    <option value="hosting">hosting</option>
                                    <option value="salary">salary</option>
                                    <option value="marketing">marketing</option>
                                    <option value="maintenance">maintenance</option>
                                    <option value="software">software</option>
                                    <option value="office">office</option>
                                    <option value="misc">misc</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Amount</label>
                                <input name="amount" id="edit_amount" type="number" step="0.01" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Payment Method</label>
                                <select name="payment_method" id="edit_payment_method" class="form-control">
                                    <option value="">Select method</option>
                                    <option value="cash">cash</option>
                                    <option value="bank">bank</option>
                                    <option value="card">card</option>
                                    <option value="online">online</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Expense Date</label>
                                <input name="expense_date" id="edit_expense_date" type="date" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Vendor Name</label>
                                <input name="vendor_name" id="edit_vendor_name" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Amount</label>
                                <input name="amount" id="edit_amount" type="number" step="0.01" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Invoice No</label>
                                <input name="invoice_no" id="edit_invoice_no" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="paid">paid</option>
                                    <option value="pending">pending</option>
                                </select>
                                
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="form-group col-md-3">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_is_recurring" name="is_recurring" value="1">
                                    <label class="form-check-label" for="edit_is_recurring">Recurring</label>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Recurring Cycle</label>
                                <select name="recurring_cycle" id="edit_recurring_cycle" class="form-control">
                                    <option value="">Select cycle</option>
                                    <option value="yearly">yearly</option>
                                    <option value="monthly">monthly</option>
                                </select>
                            </div>
                            <!-- Created by is set from logged-in user session -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Expense Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <script>
        // Populate edit modal with data attributes
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('edit-expense-btn')) {
                var btn = e.target;
                document.getElementById('edit_expense_id').value = btn.getAttribute('data-expense_id');
                document.getElementById('edit_title').value = btn.getAttribute('data-title');
                document.getElementById('edit_description').value = btn.getAttribute('data-description');
                document.getElementById('edit_category').value = btn.getAttribute('data-category');
                document.getElementById('edit_amount').value = btn.getAttribute('data-amount');
                document.getElementById('edit_payment_method').value = btn.getAttribute('data-payment_method');
                document.getElementById('edit_expense_date').value = btn.getAttribute('data-expense_date');
                document.getElementById('edit_recurring_cycle').value = btn.getAttribute('data-recurring_cycle');
                document.getElementById('edit_vendor_name').value = btn.getAttribute('data-vendor_name');
                document.getElementById('edit_invoice_no').value = btn.getAttribute('data-invoice_no');
                document.getElementById('edit_status').value = btn.getAttribute('data-status');
                var isRec = btn.getAttribute('data-is_recurring');
                document.getElementById('edit_is_recurring').checked = (isRec == '1' || isRec == 'true');
                $('#editExpenseModal').modal('show');
            }
        });
    </script>
</body>
</html>

