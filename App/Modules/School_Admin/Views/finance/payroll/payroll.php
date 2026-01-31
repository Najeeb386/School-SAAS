<?php
/**
 * School Admin - Payrolls
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
$recent_payruns = $ctrl->getRecent($session_id, 5);

// Get current month/year for new payrun modal
$current_month = intval(date('n'));
$current_year = intval(date('Y'));
$next_payrun_month = $current_month;
$next_payrun_year = $current_year;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payrolls - School Admin</title>
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
        body { color: #000; }
        .table { color: #000; }
        .table th, .table td { color: #000; }
        .card-body, .card-title { color: #000; }
        h3, h5, h6, h4 { color: #000; }
        .badge { color: #fff; }
        .breadcrumb-item { color: #000; }
        .breadcrumb-item a { color: #007bff; }
        .text-muted { color: #666 !important; }
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

            

            <div class="app-container">
                

                <div class="" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-11">
                                <h3 class="mb-3">Payrolls</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item"><a href="../finance.php">Finance</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Payrolls</li>
                                    </ol>
                                </nav>
                                 </div>
                                 <div class="col-1 mt-5 no-print"><button onclick="history.back()" class="btn btn-sm btn-outline-secondary">Back</button></div>
                            
                        </div>
                        

                        <div class="row">
                            <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <button id="btnNewPayrun" class="btn btn-primary mr-2">New Payrun</button>
                                    <button class="btn btn-outline-secondary">Import Salaries</button>
                                </div>
                                <div>
                                    <div class="form-inline">
                                        <label class="mr-2 text-muted">Filter:</label>
                                        <select class="form-control mr-2">
                                            <option>All</option>
                                            <option>Pending</option>
                                            <option>Completed</option>
                                        </select>
                                        <input type="search" class="form-control" placeholder="Search staff or payrun">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <a href="payrun.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-calendar fa-2x text-primary"></i></div>
                                            <div>
                                                <h6 class="mb-0">Payruns</h6>
                                                <small class="text-muted">Create and manage payruns</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-4">
                                <a href="salaries.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-users fa-2x text-success"></i></div>
                                            <div>
                                                <h6 class="mb-0">Staff Salaries</h6>
                                                <small class="text-muted">Manage pay grades</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-4">
                                <a href="deductions.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-minus-circle fa-2x text-warning"></i></div>
                                            <div>
                                                <h6 class="mb-0">Deductions</h6>
                                                <small class="text-muted">Loans, taxes, others</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-4">
                                <a href="payslips.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-file-text fa-2x text-info"></i></div>
                                            <div>
                                                <h6 class="mb-0">Pay Slips</h6>
                                                <small class="text-muted">Generate and download</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Recent Payruns</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Payrun</th>
                                                        <th>Date Range</th>
                                                        <th>Employees</th>
                                                        <th>Total Amount</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($recent_payruns) && is_array($recent_payruns)): ?>
                                                        <?php $i = 1; foreach ($recent_payruns as $pr): ?>
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
                                                                <td><?php echo htmlspecialchars($pr['pay_period_start'] . ' to ' . $pr['pay_period_end']); ?></td>
                                                                <td><?php echo htmlspecialchars($pr['total_employees']); ?></td>
                                                                <td><?php echo number_format($pr['total_amount'], 2); ?></td>
                                                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($pr['status']); ?></span></td>
                                                                <td>
                                                                    <a href="payrun_detail.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                                    <?php if ($pr['status'] === 'draft'): ?>
                                                                        <a href="payrun.php?id=<?php echo $pr['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="7" class="text-center">No payruns found. <a href="#" id="createFirstPayrun">Create one now</a></td></tr>
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
    <script src="../../../../../../public/assets/js/app.js"></script>

    <!-- New Payrun Modal -->
    <div class="modal fade" id="payrunModal" tabindex="-1" role="dialog" aria-labelledby="payrunModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="payrunModalLabel">Create New Payrun</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form method="post" action="save_payrun.php" id="payrunForm">
            <div class="modal-body">
              <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
              
              <div class="form-group">
                <label for="modal_session_id">Session</label>
                <input type="number" class="form-control" id="modal_session_id" name="session_id" value="<?php echo $session_id; ?>" required>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="modal_pay_month">Pay Month</label>
                  <select class="form-control" id="modal_pay_month" name="pay_month" required>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m === $current_month) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="modal_pay_year">Pay Year</label>
                  <input type="number" class="form-control" id="modal_pay_year" name="pay_year" value="<?php echo $current_year; ?>" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="modal_pay_period_start">Period Start</label>
                  <input type="date" class="form-control" id="modal_pay_period_start" name="pay_period_start" value="<?php echo date('Y-m-01'); ?>" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="modal_pay_period_end">Period End</label>
                  <input type="date" class="form-control" id="modal_pay_period_end" name="pay_period_end" value="<?php echo date('Y-m-t'); ?>" required>
                </div>
              </div>

              <div class="alert alert-info">
                <small>A new payrun will be created and automatically populated with staff salaries from the selected session.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Create Payrun</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        // Open payrun modal
        document.getElementById('btnNewPayrun').addEventListener('click', function(){
            if (window.jQuery && typeof jQuery('#payrunModal').modal === 'function') {
                jQuery('#payrunModal').modal('show');
            }
        });

        // Quick link to open modal
        var createFirstPayrunLink = document.getElementById('createFirstPayrun');
        if (createFirstPayrunLink) {
            createFirstPayrunLink.addEventListener('click', function(e){
                e.preventDefault();
                if (window.jQuery && typeof jQuery('#payrunModal').modal === 'function') {
                    jQuery('#payrunModal').modal('show');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 400);
        });
    </script>
</body>

</html>
