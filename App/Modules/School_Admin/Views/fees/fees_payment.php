<?php
/**
 * Fees Payment UI - placeholder page (UI only)
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fees Payments - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        .card-compact .card-body{padding:12px}
        .muted-small{font-size:0.9rem;color:#6c757d}
    </style>
</head>
<body>
    <div class="app">
        <div class="app-wrap">
            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>
            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <div class="app-main" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-2">Fee Payments</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Payments</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="card card-compact mb-4">
                                    <div class="card-body">
                                        <h6 class="mb-1">Collect Payment</h6>
                                        <p class="muted-small mb-2">Quick action to record a student payment.</p>
                                        <button class="btn btn-primary btn-block" id="btnRecordPayment"><i class="fa fa-credit-card mr-2"></i>Record Payment</button>
                                    </div>
                                </div>

                                <div class="card card-compact mb-4">
                                    <div class="card-body">
                                        <h6 class="mb-1">Filters</h6>
                                        <div class="form-group mt-2">
                                            <label>Session</label>
                                            <select class="form-control">
                                                <option>2025-2026</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Class</label>
                                            <select class="form-control">
                                                <option>All classes</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary btn-block mt-2" id="btnApplyFilters">Apply</button>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-9">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <strong>Payments</strong>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary" id="refreshPayments"><i class="fa fa-sync"></i></button>
                                            <button class="btn btn-sm btn-primary" id="openRecordPayment"><i class="fa fa-plus"></i> New Payment</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead class="thead-light"><tr><th>#</th><th>Date</th><th>Student</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Reference</th><th>Actions</th></tr></thead>
                                                <tbody id="paymentsTable">
                                                    <tr><td colspan="8" class="text-muted">No payments recorded.</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header"><strong>Recent Transactions</strong></div>
                                    <div class="card-body">
                                        <p class="text-muted">Transaction ledger will be shown here.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal fade" id="recordPaymentModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
          <div class="modal-body">
            <form id="formRecordPayment" onsubmit="return false;">
                <div class="form-row">
                    <div class="form-group col-md-6"><label>Student</label><select class="form-control" id="pay_student"><option value="">-- choose student --</option></select></div>
                    <div class="form-group col-md-3"><label>Payment Date</label><input type="date" class="form-control" id="pay_date" value="<?php echo date('Y-m-d'); ?>"></div>
                    <div class="form-group col-md-3"><label>Amount</label><input type="number" class="form-control" id="pay_amount" step="0.01"></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4"><label>Method</label><select class="form-control" id="pay_method"><option value="cash">Cash</option><option value="bank">Bank</option><option value="card">Card</option></select></div>
                    <div class="form-group col-md-4"><label>Reference</label><input class="form-control" id="pay_ref"></div>
                    <div class="form-group col-md-4"><label>Notes</label><input class="form-control" id="pay_notes"></div>
                </div>
            </form>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="savePayment" class="btn btn-primary">Save Payment</button></div>
        </div>
      </div>
    </div>

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
    <script>
    (function(){
        var $ = function(sel){return document.querySelector(sel)};
        document.getElementById('btnRecordPayment') && document.getElementById('btnRecordPayment').addEventListener('click', function(){ $('#recordPaymentModal').modal('show'); });
        document.getElementById('openRecordPayment') && document.getElementById('openRecordPayment').addEventListener('click', function(){ $('#recordPaymentModal').modal('show'); });
        document.getElementById('savePayment') && document.getElementById('savePayment').addEventListener('click', function(){
            alert('Save payment not implemented yet'); $('#recordPaymentModal').modal('hide');
        });
    })();
    </script>
</body>
</html>
