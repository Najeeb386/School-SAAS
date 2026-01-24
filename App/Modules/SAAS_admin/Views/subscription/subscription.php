<?php
// Include database connection
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Models/subscription_model.php';

// Initialize Subscription model
$subscriptionModel = new Subscription($DB_con);

// Get all schools with subscription details
$schools = $subscriptionModel->getAllSchools();
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
                                        <h1>Subscriptions</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Subscriptions
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                <!-- end page title -->
                            </div>
                        </div>
                        <!-- top cards -->
                         
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-header">
                                        <h3>Subscription users</h3>
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
                                        <div class="datatable-wrapper table-responsive">
                                            <table id="datatable" class="display compact table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>School Name</th>
                                                        <th>Domain</th>
                                                        <th>Email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Start Date</th>
                                                        <th>Due Date</th>
                                                        <th>Payment Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($schools)): ?>
                                                        <?php foreach ($schools as $school): ?>
                                                            <?php 
                                                                $status = $subscriptionModel->getSubscriptionStatus($school['expires_at']);
                                                                $statusBadge = '';
                                                                if ($status['status'] === 'active') {
                                                                    $statusBadge = '<span class="badge badge-success">Active</span>';
                                                                } elseif ($status['status'] === 'expiring_soon') {
                                                                    $statusBadge = '<span class="badge badge-warning">Expiring Soon (' . $status['daysLeft'] . ' days)</span>';
                                                                } else {
                                                                    $statusBadge = '<span class="badge badge-danger">Expired</span>';
                                                                }
                                                            ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($school['name']); ?></td>
                                                                <td>
                                                                    <?php echo htmlspecialchars($school['subdomain']); ?>
                                                                    <br>
                                                                    <?php echo $statusBadge; ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($school['email']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['contact_no']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['estimated_students']); ?></td>
                                                                <td><?php echo htmlspecialchars($school['plan']); ?></td>
                                                                <td><?php echo date('Y-m-d', strtotime($school['start_date'])); ?></td>
                                                                <td><?php echo date('Y-m-d', strtotime($school['expires_at'])); ?></td>
                                                                <td>
                                                                    <?php 
                                                                        $today = strtotime(date('Y-m-d'));
                                                                        $expiry = strtotime($school['expires_at']);
                                                                        if ($expiry >= $today) {
                                                                            echo '<span class="badge badge-success">Paid</span>';
                                                                        } else {
                                                                            echo '<span class="badge badge-danger">Expired</span>';
                                                                        }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-primary renew-btn" data-school-id="<?php echo $school['id']; ?>" data-school-name="<?php echo htmlspecialchars($school['name']); ?>" data-plan="<?php echo htmlspecialchars($school['plan']); ?>" data-toggle="modal" data-target="#renewModal">Renew</button>
                                                                    <button class="btn btn-sm btn-info extend-btn" data-school-id="<?php echo $school['id']; ?>" data-school-name="<?php echo htmlspecialchars($school['name']); ?>" data-toggle="modal" data-target="#extendModal">Extend</button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="10" class="text-center text-muted">No schools found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>School Name</th>
                                                        <th>Domain</th>
                                                        <th>Email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Start Date</th>
                                                        <th>Due Date</th>
                                                        <th>Payment Status</th>
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

    <!-- Renew Subscription Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renewModalLabel">Renew Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to renew the subscription for <strong id="renewSchoolName"></strong>?</p>
                    <p>Plan: <strong id="renewPlan"></strong></p>
                    <p>This will extend the subscription by <strong id="renewDuration">1 year</strong> from today.</p>
                    
                    <div class="alert alert-info mt-3">
                        <i class="ti ti-info-circle"></i> Current expiry date will be replaced with the new renewal date.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmRenewBtn">Confirm Renew</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Subscription Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1" role="dialog" aria-labelledby="extendModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendModalLabel">Extend Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Extend subscription for <strong id="extendSchoolName"></strong></p>
                    
                    <div class="form-group">
                        <label for="extendDays">Number of Days to Extend</label>
                        <input type="number" class="form-control" id="extendDays" placeholder="Enter number of days (e.g., 30, 90, 180)" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Preset Duration</label>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-primary active">
                                <input type="radio" name="preset" value="30"> 1 Month
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="preset" value="90"> 3 Months
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="preset" value="180"> 6 Months
                            </label>
                            <label class="btn btn-outline-primary">
                                <input type="radio" name="preset" value="365"> 1 Year
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="ti ti-info-circle"></i> Days will be added to the current expiry date (or from today if already expired).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmExtendBtn">Confirm Extend</button>
                </div>
            </div>
        </div>
    </div>

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <script>
        let selectedSchoolId = '';
        let selectedSchoolName = '';

        $(document).ready(function() {
            // Handle Renew button click
            $(document).on('click', '.renew-btn', function() {
                selectedSchoolId = $(this).data('school-id');
                selectedSchoolName = $(this).data('school-name');
                let plan = $(this).data('plan');
                
                $('#renewSchoolName').text(selectedSchoolName);
                $('#renewPlan').text(plan);
                
                // Set renewal duration based on plan
                let duration = '1 year';
                if (plan.toLowerCase() === 'monthly') {
                    duration = '1 month';
                } else if (plan.toLowerCase() === 'quarterly') {
                    duration = '3 months';
                }
                $('#renewDuration').text(duration);
            });

            // Handle Extend button click
            $(document).on('click', '.extend-btn', function() {
                selectedSchoolId = $(this).data('school-id');
                selectedSchoolName = $(this).data('school-name');
                
                $('#extendSchoolName').text(selectedSchoolName);
                $('#extendDays').val('');
                $('input[name="preset"][value="30"]').prop('checked', true);
            });

            // Handle preset duration selection
            $(document).on('change', 'input[name="preset"]', function() {
                $('#extendDays').val($(this).val());
            });

            // Handle Confirm Renew
            $('#confirmRenewBtn').click(function() {
                $.ajax({
                    url: './handle_subscription.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'renew',
                        schoolId: selectedSchoolId
                    }),
                    success: function(response) {
                        if (response.success) {
                            alert('Subscription renewed successfully!');
                            $('#renewModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error renewing subscription');
                    }
                });
            });

            // Handle Confirm Extend
            $('#confirmExtendBtn').click(function() {
                let days = parseInt($('#extendDays').val());
                
                if (!days || days < 1) {
                    alert('Please enter a valid number of days');
                    return;
                }

                $.ajax({
                    url: './handle_subscription.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'extend',
                        schoolId: selectedSchoolId,
                        days: days
                    }),
                    success: function(response) {
                        if (response.success) {
                            alert('Subscription extended successfully!');
                            $('#extendModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error extending subscription');
                    }
                });
            });
        });
    </script>
</body>
</html>
