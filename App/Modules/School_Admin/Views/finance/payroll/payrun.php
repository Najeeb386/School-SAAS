<?php
/**
 * Payrun Management UI
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Models/PayrunModel.php';
require_once __DIR__ . '/../../../Models/PayrunItemModel.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$session_id = isset($_SESSION['current_session_id']) ? intval($_SESSION['current_session_id']) : 1;

$ctrl = new PayrunController($DB_con);
$payruns = $ctrl->list($session_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payruns - Payroll</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <style>
        .app, .app-wrap, .app-container { padding-left: 0 !important; }
        .app-main { margin-left: 0 !important; width: 100% !important; }
        .container-fluid { max-width: 1400px; padding-left: 1.5rem; padding-right: 1.5rem; }
        body { overflow-x: hidden; }
        @media (max-width: 768px) {
            .container-fluid { padding-left: 1rem; padding-right: 1rem; }
        }
        #payrunsTable th, #payrunsTable td { white-space: nowrap; color: #000; }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.6rem;
        }
        body { color: #000; }
        .table { color: #000; }
        .card-body, .card-title { color: #000; }
        h3, h5, h6 { color: #000; }
        .breadcrumb-item { color: #000; }
        .breadcrumb-item a { color: #007bff; }
        .text-muted { color: #666 !important; }
        @media print {
            .no-print, #payrunsTable th:last-child, #payrunsTable td:last-child { 
                display: none !important; 
            }
            body { margin: 0; padding: 0; }
            .app, .app-wrap { padding: 0 !important; }
            .container-fluid { max-width: 100%; padding: 0.5rem !important; }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>

            <div class="" id="main" style="width: 100%; margin-left: 0;">
                <div class="container-fluid" style="max-width: 100%;">
                    <div class="row mb-4">
                        <div class="col-11">
                            <h3 class="mb-3">Payruns</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb p-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                    <li class="breadcrumb-item"><a href="../finance.php">Finance</a></li>
                                    <li class="breadcrumb-item"><a href="payroll.php">Payrolls</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Payruns</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-1 mt-5 no-print"><button onclick="history.back()" class="btn btn-sm btn-outline-secondary">Back</button></div>
                    </div>

                    <div class="row mb-3 no-print">
                        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
                            <div class="mb-2 mb-md-0">
                                <a href="payroll.php" class="btn btn-primary">New Payrun</a>
                                <button id="btnPrint" class="btn btn-outline-secondary">Print</button>
                            </div>
                            <div class="form-inline flex-wrap">
                                <label class="mr-2 text-muted d-none d-md-inline">Filter:</label>
                                <select id="filterStatus" class="form-control form-control-sm mr-2 mb-2 mb-md-0" style="min-width: 120px;">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="processed">Processed</option>
                                    <option value="approved">Approved</option>
                                    <option value="paid">Paid</option>
                                </select>
                                <input id="searchInput" type="search" class="form-control form-control-sm mb-2 mb-md-0" placeholder="Search payrun" style="min-width: 150px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">All Payruns</h5>
                                    <div class="table-responsive">
                                        <table id="payrunsTable" class="table table-striped table-hover" style="font-size: 0.875rem;">
                                            <thead>
                                                <tr>
                                                    <th style="min-width: 30px;">#</th>
                                                    <th style="min-width: 120px;">Payrun</th>
                                                    <th style="min-width: 100px;">Period</th>
                                                    <th style="min-width: 80px;">Employees</th>
                                                    <th style="min-width: 130px;">Total Amount</th>
                                                    <th style="min-width: 100px;">Status</th>
                                                    <th style="min-width: 180px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($payruns) && is_array($payruns)): ?>
                                                    <?php $i = 1; foreach ($payruns as $pr): ?>
                                                        <?php 
                                                            $month_name = date('F', mktime(0, 0, 0, $pr['pay_month'], 1));
                                                            $status_badge = [
                                                                'draft' => 'badge-secondary',
                                                                'processed' => 'badge-info',
                                                                'approved' => 'badge-warning',
                                                                'paid' => 'badge-success'
                                                            ];
                                                            $badge_class = $status_badge[$pr['status']] ?? 'badge-secondary';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo htmlspecialchars($month_name . ' ' . $pr['pay_year']); ?></td>
                                                            <td><?php echo htmlspecialchars(date('d M', strtotime($pr['pay_period_start'])) . ' - ' . date('d M', strtotime($pr['pay_period_end']))); ?></td>
                                                            <td><?php echo htmlspecialchars($pr['total_employees']); ?></td>
                                                            <td><?php echo number_format($pr['total_amount'], 2); ?></td>
                                                            <td><span class="badge status-badge <?php echo $badge_class; ?>"><?php echo ucfirst($pr['status']); ?></span></td>
                                                            <td>
                                                                <a href="payrun_detail.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                <?php if ($pr['status'] === 'draft'): ?>
                                                                    <a href="payrun_edit.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                                    <button class="btn btn-sm btn-outline-info btn-process-payrun" data-id="<?php echo $pr['id']; ?>">Process</button>
                                                                <?php elseif ($pr['status'] === 'processed'): ?>
                                                                    <button class="btn btn-sm btn-outline-warning btn-approve-payrun" data-id="<?php echo $pr['id']; ?>">Approve</button>
                                                                <?php elseif ($pr['status'] === 'approved'): ?>
                                                                    <button class="btn btn-sm btn-outline-success btn-pay-payrun" data-id="<?php echo $pr['id']; ?>">Mark as Paid</button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="7" class="text-center">No payruns found.</td></tr>
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

            <footer class="footer">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-left">
                        <p>&copy; Copyright 2019. All rights reserved.</p>
                    </div>
                    <div class="col col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="https://www.templateshub.net">Templates Hub</a></p>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <script>
        // Process Payrun
        document.querySelectorAll('.btn-process-payrun').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                var payrun_id = this.getAttribute('data-id');
                if (confirm('Are you sure you want to process this payrun?')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'payrun_action.php';
                    form.innerHTML = '<input type="hidden" name="action" value="process"><input type="hidden" name="payrun_id" value="' + payrun_id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Approve Payrun
        document.querySelectorAll('.btn-approve-payrun').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                var payrun_id = this.getAttribute('data-id');
                if (confirm('Are you sure you want to approve this payrun?')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'payrun_action.php';
                    form.innerHTML = '<input type="hidden" name="action" value="approve"><input type="hidden" name="payrun_id" value="' + payrun_id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Pay Payrun
        document.querySelectorAll('.btn-pay-payrun').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                var payrun_id = this.getAttribute('data-id');
                if (confirm('Are you sure you want to mark this payrun as paid?')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'payrun_action.php';
                    form.innerHTML = '<input type="hidden" name="action" value="pay"><input type="hidden" name="payrun_id" value="' + payrun_id + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Print
        document.getElementById('btnPrint').addEventListener('click', function(){
            window.print();
        });

        // Search and filter
        document.getElementById('searchInput').addEventListener('input', function(e) {
            var q = e.target.value.toLowerCase();
            var rows = document.querySelectorAll('#payrunsTable tbody tr');
            rows.forEach(function(r) {
                var text = r.innerText.toLowerCase();
                r.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
        });

        document.getElementById('filterStatus').addEventListener('change', function(e) {
            var val = e.target.value;
            var rows = document.querySelectorAll('#payrunsTable tbody tr');
            if (!val) { rows.forEach(r => r.style.display = ''); return; }
            rows.forEach(function(r) {
                var status = r.cells[5].innerText.toLowerCase().trim();
                r.style.display = status.includes(val) ? '' : 'none';
            });
        });

        // Hide loader on load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 300);
        });
    </script>
</body>

</html>
