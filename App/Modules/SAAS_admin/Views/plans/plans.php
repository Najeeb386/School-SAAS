<?php
session_start();

// Database connection
require_once '../../../../Config/connection.php';
require_once '../../Controllers/plain_controller.php';

// Initialize controller
$planController = new PlanController($DB_con);

// Handle POST request for adding/updating plan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if this is an update (has id) or create
        if (!empty($_POST['id'])) {
            // Update plan
            if ($planController->update()) {
                $_SESSION['success'] = 'Plan updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update plan!';
            }
        } else {
            // Create new plan
            if ($planController->store()) {
                $_SESSION['success'] = 'Plan added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add plan!';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
    header("Location: plans.php");
    exit;
}

// Get all plans
$plans = $planController->index();

// Handle GET request for deleting plan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        if ($planController->delete($_GET['id'])) {
            $_SESSION['success'] = 'Plan deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete plan!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    header("Location: plans.php");
    exit;
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
                                        <h1>Plans</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Plans
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                
                                <!-- end page title -->
                            </div>
                        </div>

                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPlanModal">
                                                <i class="ti ti-plus"></i> Add New Plan
                                            </button>
                                        </div>
                                        <div class="datatable-wrapper table-responsive">
                                            <table id="datatable" class="display compact table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Plan Name</th>
                                                        <th>Price Per Student/Year</th>
                                                        <th>Hosting Type</th>
                                                        <th>Features</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(!empty($plans)): ?>
                                                        <?php foreach($plans as $plan): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($plan['id']); ?></td>
                                                                <td><?php echo htmlspecialchars($plan['name']); ?></td>
                                                                <td><?php echo htmlspecialchars($plan['price_per_student_year']); ?></td>
                                                                <td><?php echo htmlspecialchars($plan['hosting_type']); ?></td>
                                                                <td><?php echo htmlspecialchars(substr($plan['features'], 0, 50)); ?>...</td>
                                                                <td>
                                                                    <span class="badge badge-<?php echo ($plan['status'] === 'active') ? 'success' : 'danger'; ?>">
                                                                        <?php echo htmlspecialchars($plan['status']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editPlanModal" 
                                                                        onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteConfirmModal" onclick="setDeleteId(<?php echo htmlspecialchars($plan['id']); ?>)">Delete</button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted">No plans found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Plan Name</th>
                                                        <th>Price Per Student/Year</th>
                                                        <th>Hosting Type</th>
                                                        <th>Features</th>
                                                        <th>Status</th>
                                                        <th>Created At</th>
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

  

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- Add Plan Modal -->
    <div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlanModalLabel">Add New Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="planName">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="planName" name="name" placeholder="Enter plan name" required>
                        </div>

                        <div class="form-group">
                            <label for="pricePerStudent">Price Per Student/Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pricePerStudent" name="price_per_student_year" placeholder="Enter price" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="hostingType">Hosting Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="hostingType" name="hosting_type" required>
                                <option value="">Select hosting type</option>
                                <option value="shared">Shared</option>
                                <option value="dedicated">Dedicated</option>
                                <option value="cloud">Cloud</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="features">Features <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="features" name="features" rows="4" placeholder="Enter plan features (comma-separated or describe)" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Select status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Add Plan Modal -->

    <!-- Edit Plan Modal -->
    <div class="modal fade" id="editPlanModal" tabindex="-1" role="dialog" aria-labelledby="editPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlanModalLabel">Edit Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="editPlanId" name="id">
                        
                        <div class="form-group">
                            <label for="editPlanName">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editPlanName" name="name" placeholder="Enter plan name" required>
                        </div>

                        <div class="form-group">
                            <label for="editPricePerStudent">Price Per Student/Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editPricePerStudent" name="price_per_student_year" placeholder="Enter price" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="editHostingType">Hosting Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="editHostingType" name="hosting_type" required>
                                <option value="">Select hosting type</option>
                                <option value="shared">Shared</option>
                                <option value="dedicated">Dedicated</option>
                                <option value="cloud">Cloud</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editFeatures">Features <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="editFeatures" name="features" rows="4" placeholder="Enter plan features" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="editStatus">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="">Select status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Plan Modal -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Are you sure you want to delete this plan?</strong></p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Confirmation Modal -->

    <script>
        function editPlan(planData) {
            // Populate the edit modal with plan data
            document.getElementById('editPlanId').value = planData.id;
            document.getElementById('editPlanName').value = planData.name;
            document.getElementById('editPricePerStudent').value = planData.price_per_student_year;
            document.getElementById('editHostingType').value = planData.hosting_type;
            document.getElementById('editFeatures').value = planData.features;
            document.getElementById('editStatus').value = planData.status;
        }

        function setDeleteId(planId) {
            // Set the delete URL when delete button is clicked
            const deleteUrl = 'plans.php?action=delete&id=' + planId;
            document.getElementById('confirmDeleteBtn').href = deleteUrl;
        }
    </script>