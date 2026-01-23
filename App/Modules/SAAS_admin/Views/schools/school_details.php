<?php
session_start();

// Database connection
require_once '../../../../Config/connection.php';
require_once '../../Controllers/School_controller.php';

// Initialize controller
$schoolController = new SchoolController($DB_con);

// Get school ID from URL
$schoolId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$schoolId) {
    $_SESSION['error'] = 'No school ID provided!';
    header("Location: schools.php");
    exit;
}

// Fetch school details
$school = $schoolController->getSchoolById($schoolId);

if (!$school) {
    $_SESSION['error'] = 'School not found!';
    header("Location: schools.php");
    exit;
}

// Handle POST request for updating school from details page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle upgrade plan submission
        if (isset($_POST['action']) && $_POST['action'] === 'upgrade') {
            $upgradeData = [
                'estimated_students' => isset($_POST['estimated_students']) ? $_POST['estimated_students'] : $school['estimated_students'],
                'plan' => isset($_POST['plan']) ? $_POST['plan'] : $school['plan']
            ];
            
            if ($schoolController->update($schoolId, $upgradeData)) {
                $_SESSION['success'] = 'Plan upgraded successfully!';
                $school = $schoolController->getSchoolById($schoolId);
                header("Location: school_details.php?id=" . htmlspecialchars($schoolId));
                exit;
            } else {
                $_SESSION['error'] = 'Failed to upgrade plan!';
            }
        } else {
            // Handle regular school update
            if ($schoolController->update()) {
                $_SESSION['success'] = 'School updated successfully!';
                $school = $schoolController->getSchoolById($schoolId);
                header("Location: school_details.php?id=" . htmlspecialchars($schoolId));
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update school!';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        if ($schoolController->delete($schoolId)) {
            $_SESSION['success'] = 'School deleted successfully!';
            header("Location: schools.php");
            exit;
        } else {
            $_SESSION['error'] = 'Failed to delete school!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
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

    <!-- TABS -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($school['name']); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="schools.php" class="btn btn-secondary">Back to Schools</a>
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

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#usage">Usage & Limits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#billing">Billing</a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">
            <div class="row mb-3">
                <div class="col-md-12 text-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#editSchoolModal">Edit</button>
                    <a href="school_details.php?id=<?php echo htmlspecialchars($school['id']); ?>&action=delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this school?');">Delete</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>School Overview</h5>
                </div>

                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="30%">School Name</th><td><?php echo htmlspecialchars($school['name']); ?></td></tr>
                        <tr><th>Subdomain</th><td><?php echo htmlspecialchars($school['subdomain']); ?>.yoursaas.com</td></tr>
                        <tr><th>Plan</th><td><?php echo htmlspecialchars($school['plan']); ?></td></tr>
                        <tr><th>Status</th><td><span class="badge badge-<?php echo ($school['status'] === 'active') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($school['status']); ?></span></td></tr>
                        <tr><th>Contact Email</th><td><?php echo htmlspecialchars($school['email']); ?></td></tr>
                        <tr><th>Contact No</th><td><?php echo htmlspecialchars($school['contact_no']); ?></td></tr>
                        <tr><th>Estimated Students</th><td><?php echo htmlspecialchars($school['estimated_students']); ?></td></tr>
                        <tr><th>Start Date</th><td><?php echo htmlspecialchars($school['start_date']); ?></td></tr>
                        <tr><th>Expires At</th><td><?php echo htmlspecialchars($school['expires_at']); ?></td></tr>
                        <tr><th>Storage Used</th><td><?php echo htmlspecialchars($school['storage_used']); ?> MB</td></tr>
                        <tr><th>Database Size</th><td><?php echo htmlspecialchars($school['db_size']); ?> MB</td></tr>
                        <tr><th>Created At</th><td><?php echo htmlspecialchars($school['created_at']); ?></td></tr>
                        <tr><th>Last Updated</th><td><?php echo htmlspecialchars($school['updated_at']); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= USAGE & LIMITS ================= -->
        <div class="tab-pane fade" id="usage">
            <div class="row">

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Students</h6>
                            <h4>0 / <?php echo htmlspecialchars($school['estimated_students']); ?></h4>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width:<?php echo ($school['estimated_students'] > 0) ? '0' : '0'; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Storage</h6>
                            <?php 
                                $storageUsed = isset($school['storage_used']) ? floatval($school['storage_used']) : 0;
                                $storageLimit = 2048; // 2GB in MB
                                $storagePercent = ($storageUsed > 0) ? min(100, ($storageUsed / $storageLimit) * 100) : 0;
                                $storageGB = $storageUsed / 1024;
                            ?>
                            <h4><?php echo number_format($storageGB, 2); ?> GB / 2 GB</h4>
                            <div class="progress">
                                <div class="progress-bar <?php echo ($storagePercent > 80) ? 'bg-danger' : ($storagePercent > 60 ? 'bg-warning' : 'bg-success'); ?>" style="width:<?php echo $storagePercent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Database Size</h6>
                            <?php 
                                $dbUsed = isset($school['db_size']) ? floatval($school['db_size']) : 0;
                                $dbLimit = 1024; // 1GB in MB
                                $dbPercent = ($dbUsed > 0) ? min(100, ($dbUsed / $dbLimit) * 100) : 0;
                                $dbGB = $dbUsed / 1024;
                            ?>
                            <h4><?php echo number_format($dbGB, 2); ?> GB / 1 GB</h4>
                            <div class="progress">
                                <div class="progress-bar <?php echo ($dbPercent > 80) ? 'bg-danger' : ($dbPercent > 60 ? 'bg-warning' : 'bg-success'); ?>" style="width:<?php echo $dbPercent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-right mt-3">
                <button class="btn btn-success" data-toggle="modal" data-target="#upgradePlanModal">
                    Upgrade Plan
                </button>
            </div>
        </div>

        <!-- ================= BILLING ================= -->
        <div class="tab-pane fade" id="billing">
            <div class="card">
                <div class="card-header">
                    <h5>Billing Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr><th>Current Plan</th><td>Basic</td></tr>
                        <tr><th>Amount</th><td>PKR 180 / Student / Year</td></tr>
                        <tr><th>Last payment date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Upcoming payment Date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Due Date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Payment Status</th><td><span class="badge badge-warning">Due</span></td></tr>
                    </table>

                    <div class="text-right">
                        <button class="btn btn-primary btn-sm">Mark as Paid</button>
                        <button class="btn btn-danger btn-sm">Suspend Portal</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- modals -->
 <div class="modal fade" id="upgradePlanModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Upgrade Plan & Limits</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="upgrade">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($school['id']); ?>">
                    
                    <div class="form-group">
                        <label>Estimated Students Limit</label>
                        <input type="number" class="form-control" name="estimated_students" value="<?php echo htmlspecialchars($school['estimated_students']); ?>" required>
                        <small class="form-text text-muted">Current: <?php echo htmlspecialchars($school['estimated_students']); ?></small>
                    </div>

                    <div class="form-group">
                        <label>Storage Limit (GB)</label>
                        <input type="number" class="form-control" name="storage_limit" value="2" step="0.5" required>
                        <small class="form-text text-muted">Current: 2 GB</small>
                    </div>

                    <div class="form-group">
                        <label>Database Size Limit (GB)</label>
                        <input type="number" class="form-control" name="db_limit" value="1" step="0.5" required>
                        <small class="form-text text-muted">Current: 1 GB</small>
                    </div>

                    <div class="form-group">
                        <label>Plan</label>
                        <select class="form-control" name="plan" required>
                            <option value="Basic" <?php echo ($school['plan'] === 'Basic') ? 'selected' : ''; ?>>Basic</option>
                            <option value="Standard" <?php echo ($school['plan'] === 'Standard') ? 'selected' : ''; ?>>Standard</option>
                            <option value="Premium" <?php echo ($school['plan'] === 'Premium') ? 'selected' : ''; ?>>Premium</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Upgrade Plan</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

 <div class="modal fade" id="editSchoolModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit School</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($school['id']); ?>">
                    
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($school['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Subdomain</label>
                        <input type="text" class="form-control" name="subdomain" value="<?php echo htmlspecialchars($school['subdomain']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($school['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" class="form-control" name="contact_no" value="<?php echo htmlspecialchars($school['contact_no']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Estimated Students</label>
                        <input type="number" class="form-control" name="estimated_students" value="<?php echo htmlspecialchars($school['estimated_students']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Plan</label>
                        <select class="form-control" name="plan" required>
                            <option value="">Select Plan</option>
                            <option value="Basic" <?php echo ($school['plan'] === 'Basic') ? 'selected' : ''; ?>>Basic</option>
                            <option value="Standard" <?php echo ($school['plan'] === 'Standard') ? 'selected' : ''; ?>>Standard</option>
                            <option value="Premium" <?php echo ($school['plan'] === 'Premium') ? 'selected' : ''; ?>>Premium</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" required>
                            <option value="active" <?php echo ($school['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($school['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="pending" <?php echo ($school['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo (isset($school['start_date']) && !empty($school['start_date'])) ? htmlspecialchars(date('Y-m-d', strtotime($school['start_date']))) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Expires At</label>
                        <input type="date" class="form-control" name="expires_at" value="<?php echo (isset($school['expires_at']) && !empty($school['expires_at'])) ? htmlspecialchars(date('Y-m-d', strtotime($school['expires_at']))) : ''; ?>" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

 <!-- modals -->

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
</body>


</html>