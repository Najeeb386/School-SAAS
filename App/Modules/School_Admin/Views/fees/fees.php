<?php
/**
 * Fees Management UI
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Fees - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Fees management" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        .page-actions { display:flex; gap:10px }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader" style="display:none;"></div>
            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>
            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <div class="app-main" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Fees</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Fees</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <a href="fee_managment.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-cogs fa-2x text-primary"></i></div>
                                            <div>
                                                <h6 class="mb-0">Fee Management</h6>
                                                <small class="text-muted">Categories, items, assignments</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-4">
                                <a href="invoices/fees_invoice.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-file-text-o fa-2x text-warning"></i></div>
                                            <div>
                                                <h6 class="mb-0">Invoices / Fee vouchers</h6>
                                                <small class="text-muted">generates fees vouchers / invoices</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-4">
                                <a href="fees_payment.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-credit-card fa-2x text-success"></i></div>
                                            <div>
                                                <h6 class="mb-0">Fee Payments</h6>
                                                <small class="text-muted">Receive and record payments</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <a href="#transactions" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-file-text-o fa-2x text-warning"></i></div>
                                            <div>
                                                <h6 class="mb-0">Transactions</h6>
                                                <small class="text-muted">Payment ledger & reports</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <a href="concession/concession.php" class="text-dark text-decoration-none">
                                    <div class="card shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="mr-3"><i class="fa fa-cogs fa-2x text-primary"></i></div>
                                            <div>
                                                <h6 class="mb-0">Concession</h6>
                                                <small class="text-muted">Scholarship, Discount, Concession</small>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php // modals (same placeholders as before) ?>
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Add Fee Category</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
          <div class="modal-body">
            <form id="formAddCategory" onsubmit="return false;"><div class="form-group"><label>Name</label><input class="form-control" id="cat_name" required></div><div class="form-group"><label>Description</label><textarea class="form-control" id="cat_desc"></textarea></div></form>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveCategory" class="btn btn-primary">Save</button></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="addFeeItemModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Add Fee Item</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
          <div class="modal-body">
            <form id="formAddFeeItem" onsubmit="return false;"><div class="form-group"><label>Title</label><input class="form-control" id="fee_title" required></div><div class="form-group"><label>Category</label><select id="fee_category" class="form-control"><option value="">-- choose --</option></select></div><div class="form-row"><div class="form-group col-md-6"><label>Amount</label><input class="form-control" id="fee_amount" type="number" step="0.01" required></div><div class="form-group col-md-6"><label>Recurring</label><select id="fee_recurring" class="form-control"><option value="0">No</option><option value="1">Yes</option></select></div></div></form>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveFeeItem" class="btn btn-primary">Save Item</button></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="assignFeeModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Assign Fee</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
          <div class="modal-body">
            <form id="formAssignFee" onsubmit="return false;"><div class="form-group"><label>Fee Item</label><select class="form-control" id="assign_fee_item"><option value="">-- choose --</option></select></div><div class="form-group"><label>Assign To</label><select class="form-control" id="assign_to"><option value="class">Class</option><option value="section">Section</option><option value="student">Student</option></select></div><div class="form-group" id="assign_target_container"><label>Target</label><select class="form-control" id="assign_target"><option value="">-- choose --</option></select></div><div class="form-group"><label>Amount (optional)</label><input class="form-control" id="assign_amount" type="number" step="0.01"></div></form>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button><button id="saveAssignment" class="btn btn-primary">Assign</button></div>
        </div>
      </div>
    </div>

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
    <script>
    // Placeholder handlers
    document.getElementById && (function(){
        var $ = function(sel){return document.querySelector(sel)};
        var $$ = function(sel){return document.querySelectorAll(sel)};
        document.getElementById('btnAddCategory') && document.getElementById('btnAddCategory').addEventListener('click', function(){ $('#addCategoryModal').modal('show'); });
    })();
    </script>
</body>

</html>
