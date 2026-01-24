<?php
// Include database connection
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Models/requests_model.php';

// Initialize Requests model
$requestsModel = new Requests($DB_con);

// Get data based on filters
$newRequests = [];
$approvedRequests = [];
$rejectedRequests = [];

// Fetch all requests
$newRequests = $requestsModel->getNewRequests();
$approvedRequests = $requestsModel->getApprovedRequests();
$rejectedRequests = $requestsModel->getRejectedRequests();
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
                                        <h1>Requests</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Requests
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                
                                <!-- end page title -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <!-- Tabs Navigation -->
                                        <ul class="nav nav-tabs" id="requestTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="new-requests-tab" data-toggle="tab" href="#newRequests" role="tab" aria-controls="newRequests" aria-selected="true">
                                                    <i class="ti ti-bell"></i> New Requests
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="approved-requests-tab" data-toggle="tab" href="#approvedRequests" role="tab" aria-controls="approvedRequests" aria-selected="false">
                                                    <i class="ti ti-check"></i> Approved Requests
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="rejected-requests-tab" data-toggle="tab" href="#rejectedRequests" role="tab" aria-controls="rejectedRequests" aria-selected="false">
                                                    <i class="ti ti-close"></i> Rejected Requests
                                                </a>
                                            </li>
                                        </ul>

                                        <!-- Tab Content -->
                                        <div class="tab-content" id="requestTabsContent">
                                            <!-- New Requests Tab -->
                                            <div class="tab-pane fade show active" id="newRequests" role="tabpanel" aria-labelledby="new-requests-tab">
                                                <div class="mt-4">
                                                    <!-- Filters Section -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control filterName" placeholder="Search by School Name">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterStartDate" placeholder="Start Date">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterEndDate" placeholder="End Date">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <button class="btn btn-secondary resetFilter">Reset Filters</button>
                                                        </div>
                                                    </div>
                                                    <!-- End Filters Section -->
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table class="display compact table table-striped table-bordered newRequestsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>School Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact No</th>
                                                                    <th>Estimated Students</th>
                                                                    <th>Plan</th>
                                                                    <th>Request Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($newRequests)): ?>
                                                                    <?php foreach ($newRequests as $request): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($request['school_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_email']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_phone']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['estimated_students']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['plan_type']); ?></td>
                                                                            <td><?php echo date('Y-m-d H:i', strtotime($request['requested_at'])); ?></td>
                                                                            <td>
                                                                                <a href="./request_details.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-primary">Details</a>
                                                                                <a href="#" class="btn btn-sm btn-success approve-btn" data-id="<?php echo $request['request_id']; ?>">Approve</a>
                                                                                <a href="#" class="btn btn-sm btn-danger reject-btn" data-id="<?php echo $request['request_id']; ?>">Reject</a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center text-muted">No new requests found</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Approved Requests Tab -->
                                            <div class="tab-pane fade" id="approvedRequests" role="tabpanel" aria-labelledby="approved-requests-tab">
                                                <div class="mt-4">
                                                    <!-- Filters Section -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control filterName" placeholder="Search by School Name">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterStartDate" placeholder="Approval Date From">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterEndDate" placeholder="Approval Date To">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <button class="btn btn-secondary resetFilter">Reset Filters</button>
                                                        </div>
                                                    </div>
                                                    <!-- End Filters Section -->
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table class="display compact table table-striped table-bordered approvedRequestsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th> Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact No</th>
                                                                    <th>Estimated Students</th>
                                                                    <th>Plan</th>
                                                                    <th>Request Date</th>
                                                                    <th>Approval Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($approvedRequests)): ?>
                                                                    <?php foreach ($approvedRequests as $request): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($request['school_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_email']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_phone']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['estimated_students']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['plan_type']); ?></td>
                                                                            <td><?php echo date('Y-m-d H:i', strtotime($request['requested_at'])); ?></td>
                                                                            <td><?php echo date('Y-m-d H:i', strtotime($request['actioned_at'])); ?></td>
                                                                            <td>
                                                                                <a href="./request_details.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-primary">Details</a>
                                                                                <a href="#" class="btn btn-sm btn-info">View Account</a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="8" class="text-center text-muted">No approved requests found</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rejected Requests Tab -->
                                            <div class="tab-pane fade" id="rejectedRequests" role="tabpanel" aria-labelledby="rejected-requests-tab">
                                                <div class="mt-4">
                                                    <!-- Filters Section -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <input type="text" class="form-control filterName" placeholder="Search by School Name">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterStartDate" placeholder="Rejection Date From">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="date" class="form-control filterEndDate" placeholder="Rejection Date To">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-12">
                                                            <button class="btn btn-secondary resetFilter">Reset Filters</button>
                                                        </div>
                                                    </div>
                                                    <!-- End Filters Section -->
                                                    <div class="datatable-wrapper table-responsive">
                                                        <table class="display compact table table-striped table-bordered rejectedRequestsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>School Name</th>
                                                                    <th>Email</th>
                                                                    <th>Contact No</th>
                                                                    <th>Estimated Students</th>
                                                                    <th>Plan</th>
                                                                    <th>Request Date</th>
                                                                    <th>Rejection Date</th>
                                                                    <th>Reason</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($rejectedRequests)): ?>
                                                                    <?php foreach ($rejectedRequests as $request): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($request['school_name']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_email']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['school_phone']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['estimated_students']); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['plan_type']); ?></td>
                                                                            <td><?php echo date('Y-m-d H:i', strtotime($request['requested_at'])); ?></td>
                                                                            <td><?php echo date('Y-m-d H:i', strtotime($request['actioned_at'])); ?></td>
                                                                            <td><?php echo htmlspecialchars($request['rejection_reason']); ?></td>
                                                                            <td>
                                                                                <a href="./request_details.php?id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-primary">Details</a>
                                                                                <a href="#" class="btn btn-sm btn-warning reconsider-btn" data-id="<?php echo $request['request_id']; ?>">Reconsider</a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="9" class="text-center text-muted">No rejected requests found</td>
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

  

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize tabs
            $('#requestTabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Handle tab change
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href");
                console.log("Tab switched to: " + target);
            });

            // Handle Approve button click
            $(document).on('click', '.approve-btn', function(e) {
                e.preventDefault();
                var requestId = $(this).data('id');
                
                if (confirm('Are you sure you want to approve this request?')) {
                    $.ajax({
                        url: './handle_request.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            action: 'approve',
                            id: requestId
                        }),
                        success: function(response) {
                            if (response.success) {
                                alert('Request approved successfully');
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error approving request');
                        }
                    });
                }
            });

            // Handle Reject button click
            $(document).on('click', '.reject-btn', function(e) {
                e.preventDefault();
                var requestId = $(this).data('id');
                var reason = prompt('Please provide a reason for rejection:');
                
                if (reason !== null && reason.trim() !== '') {
                    $.ajax({
                        url: './handle_request.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            action: 'reject',
                            id: requestId,
                            reason: reason
                        }),
                        success: function(response) {
                            if (response.success) {
                                alert('Request rejected successfully');
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error rejecting request');
                        }
                    });
                }
            });

            // Handle Reconsider button click
            $(document).on('click', '.reconsider-btn', function(e) {
                e.preventDefault();
                var requestId = $(this).data('id');
                
                if (confirm('Are you sure you want to reconsider this request?')) {
                    $.ajax({
                        url: './handle_request.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            action: 'reconsider',
                            id: requestId
                        }),
                        success: function(response) {
                            if (response.success) {
                                alert('Request moved back to pending');
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error reconsidering request');
                        }
                    });
                }
            });
        });
    </script>
