<?php
/**
 * Payrun Detail View
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Config/connection.php';
require_once __DIR__ . '/../../../Models/PayrunModel.php';
require_once __DIR__ . '/../../../Models/PayrunItemModel.php';
require_once __DIR__ . '/../../../Controllers/PayrunController.php';

use App\Modules\School_Admin\Controllers\PayrunController;

$school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
$payrun_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$ctrl = new PayrunController($DB_con);
$payrun_data = $ctrl->getPayrunWithItems($payrun_id);

if (!$payrun_data) {
    header('Location: payrun.php');
    exit;
}

$payrun = $payrun_data;
$items = $payrun_data['items'] ?? [];
$payment_summary = $payrun_data['payment_summary'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payrun Detail - Payroll</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <style>
        .app, .app-wrap, .app-container { padding-left: 0 !important; }
        .app-main { margin-left: 0 !important; width: 100% !important; }
        .container-fluid { max-width: 1400px; padding-left: 1.5rem; padding-right: 1.5rem; }
        body { overflow-x: hidden; }
        @media (max-width: 768px) {
            .container-fluid { padding-left: 1rem; padding-right: 1rem; }
        }
        .stat-box { border-left: 4px solid #007bff; padding: 1rem; background: #f8f9fa; }
        .stat-box h6 { color: #666; font-weight: 600; font-size: 0.85rem; }
        .stat-box .value { font-size: 1.5rem; font-weight: bold; color: #333; }
        @media print {
            .no-print { display: none !important; }
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
                            <h3 class="mb-3">Payrun Details</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb p-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                    <li class="breadcrumb-item"><a href="payrun.php">Payruns</a></li>
                                    <li class="breadcrumb-item active">Detail</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-1 mt-5 no-print">
                            <button onclick="history.back()" class="btn btn-sm btn-outline-secondary">Back</button>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-info">Print</button>
                        </div>
                    </div>

                    <!-- Payrun Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <h6>Payrun Period</h6>
                                <div class="value"><?php echo date('F Y', mktime(0, 0, 0, $payrun['pay_month'], 1, $payrun['pay_year'])); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <h6>Total Employees</h6>
                                <div class="value"><?php echo $payrun['total_employees']; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <h6>Total Amount</h6>
                                <div class="value"><?php echo number_format($payrun['total_amount'], 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <h6>Status</h6>
                                <div class="value">
                                    <?php 
                                        $status_colors = [
                                            'draft' => 'badge-secondary',
                                            'processed' => 'badge-info',
                                            'approved' => 'badge-warning',
                                            'paid' => 'badge-success'
                                        ];
                                        $badge_class = $status_colors[$payrun['status']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($payrun['status']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payrun Items Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Payrun Items</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" style="font-size: 0.875rem;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Staff Type</th>
                                                    <th>Staff ID</th>
                                                    <th>Basic Salary</th>
                                                    <th>Allowance</th>
                                                    <th>Deduction</th>
                                                    <th>Net Salary</th>
                                                    <th>Payment Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($items) && is_array($items)): ?>
                                                    <?php $i = 1; foreach ($items as $item): ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo htmlspecialchars(ucfirst($item['staff_type'])); ?></td>
                                                            <td><?php echo htmlspecialchars($item['staff_type'] . '-' . str_pad($item['staff_id'], 3, '0', STR_PAD_LEFT)); ?></td>
                                                            <td><?php echo number_format($item['basic_salary'], 2); ?></td>
                                                            <td><?php echo number_format($item['allowance'], 2); ?></td>
                                                            <td><?php echo number_format($item['deduction'], 2); ?></td>
                                                            <td><strong><?php echo number_format($item['net_salary'], 2); ?></strong></td>
                                                            <td>
                                                                <?php 
                                                                    $payment_badge = [
                                                                        'pending' => 'badge-warning',
                                                                        'paid' => 'badge-success',
                                                                        'cancelled' => 'badge-danger'
                                                                    ];
                                                                    $p_badge = $payment_badge[$item['payment_status']] ?? 'badge-secondary';
                                                                ?>
                                                                <span class="badge <?php echo $p_badge; ?>"><?php echo ucfirst($item['payment_status']); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="8" class="text-center">No items in this payrun.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <?php if (!empty($payment_summary)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Payment Summary</h5>
                                    <div class="row">
                                        <?php foreach ($payment_summary as $summary): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="stat-box">
                                                <h6><?php echo ucfirst($summary['payment_status']); ?></h6>
                                                <div class="value"><?php echo $summary['count']; ?> Staff</div>
                                                <small class="text-muted">Total: <?php echo number_format($summary['total'], 2); ?></small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 300);
        });
    </script>
</body>

</html>
