<?php
/**
 * School Profile View
 * Pure display logic - all business logic handled by controller
 */
require_once __DIR__ . '/../../../../Config/auth_check.php';

// Initialize MVC
require_once __DIR__ . '/../../../../../autoloader.php';

use App\Modules\School_Admin\Controllers\SchoolProfileController;

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    die('School ID not found in session');
}

// Create controller and handle requests
$controller = new SchoolProfileController($school_id);
$controller->handleRequest();

// Get data from controller
$school = $controller->getSchoolData();
$storage_info = $controller->getStorageInfo();
$success_message = $controller->getSuccessMessage();
$error_message = $controller->getErrorMessage();
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <title>School Profile</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School Profile - Manage your school information" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        html, body {
            height: 100%;
        }
        .app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .app-wrap {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .app-container {
            flex: 1;
            display: flex;
        }
        .app-main {
            flex: 1;
            padding-bottom: 30px;
        }
        .footer {
            margin-top: auto;
            background: #f5f5f5;
            border-top: 1px solid #e8e8e8;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .card-body {
            padding: 40px;
            min-height: 400px;
        }
    </style>
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
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
                <!-- end navbar -->
            </header>
            <!-- end app-header -->
            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-navbar -->
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
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
                                        <h1>School Profile</h1>
                                    </div>
                                    <div class="breadcrumb-bar align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="../dashboard/index.php"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    School Admin
                                                </li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">Profile</li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                <!-- end page title -->
                            </div>
                        </div>
                        <!-- end row -->
                        
                        <!-- Update Message -->
                        <?php if ($controller->hasSuccess()): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fa fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($controller->hasError()): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fa fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- begin row - School Info with Tabs -->
                        <div class="row">
                            <div class="col-lg-12 m-b-30">
                                <div class="card card-statistics">
                                    <!-- Nav tabs -->
                                    <div class="card-header">
                                        <ul class="nav nav-tabs nav-border-bottom" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">
                                                    <i class="fa fa-info-circle mr-2"></i>School Information
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="plan-tab" data-toggle="tab" href="#plan" role="tab" aria-controls="plan" aria-selected="false">
                                                    <i class="fa fa-cube mr-2"></i>Plan & Subscription
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="storage-tab" data-toggle="tab" href="#storage" role="tab" aria-controls="storage" aria-selected="false">
                                                    <i class="fa fa-database mr-2"></i>Storage & Usage
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">
                                                    <i class="fa fa-lock mr-2"></i>Security
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Tab Content -->
                                    <div class="card-body">
                                        <div class="tab-content">
                                            <!-- School Information Tab -->
                                            <div class="tab-pane fade active show" id="info" role="tabpanel" aria-labelledby="info-tab">
                                                <form method="POST" class="form-horizontal">
                                                    <input type="hidden" name="action" value="update_school">
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">School Name</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($school['name']); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Email</label>
                                                        <div class="col-sm-6">
                                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($school['email']); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Contact Number</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" name="contact_no" class="form-control" value="<?php echo htmlspecialchars($school['contact_no'] ?? ''); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Address</label>
                                                        <div class="col-sm-6">
                                                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($school['address'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">City</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($school['city'] ?? ''); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Boards</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" name="boards" class="form-control" placeholder="e.g., CBSE, ICSE" value="<?php echo htmlspecialchars($school['boards'] ?? ''); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Subdomain</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($school['subdomain'] ?? ''); ?>" disabled>
                                                            <small class="form-text text-muted">Subdomain cannot be changed</small>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="col-sm-3 control-label">Status</label>
                                                        <div class="col-sm-6">
                                                            <span class="badge badge-<?php echo $school['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                                <?php echo ucfirst($school['status']); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-6 offset-sm-3">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fa fa-save mr-2"></i>Save Changes
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Plan & Subscription Tab -->
                                            <div class="tab-pane fade" id="plan" role="tabpanel" aria-labelledby="plan-tab">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h4>Current Plan: <strong><?php echo ucfirst($school['plan']); ?></strong></h4>
                                                        <hr>
                                                        
                                                        <div class="plan-details">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong>Start Date:</strong></p>
                                                                    <p><?php echo !empty($school['start_date']) ? date('d-M-Y', strtotime($school['start_date'])) : 'N/A'; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Expiry Date:</strong></p>
                                                                    <p><?php echo !empty($school['expires_at']) ? date('d-M-Y', strtotime($school['expires_at'])) : 'N/A'; ?></p>
                                                                </div>
                                            </div>

                                                            <div class="row mt-3">
                                                                <div class="col-md-6">
                                                                    <p><strong>Estimated Students:</strong></p>
                                                                    <p><?php echo $school['estimated_students'] ?? 'N/A'; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Current Students:</strong></p>
                                                                    <p><?php echo $school['total_student'] ?? 'N/A'; ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Plan Details</h5>
                                                                <p class="card-text">Manage your subscription, view billing history, and upgrade or downgrade your plan.</p>
                                                                <a href="#" class="btn btn-primary btn-sm">Manage Plan</a>
                                                                <a href="../billing/history.php" target="_blank" class="btn btn-secondary btn-sm">Billing History</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Storage & Usage Tab -->
                                            <div class="tab-pane fade" id="storage" role="tabpanel" aria-labelledby="storage-tab">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h4>Storage Usage</h4>
                                                        <hr>
                                                        
                                                        <div class="storage-info">
                                                            <div class="mb-3">
                                                                <p><strong>Total Storage Used: <?php echo number_format($storage_info['total_usage'], 2); ?> GB</strong></p>
                                                                <div class="progress" style="height: 25px;">
                                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min($storage_info['percentage'], 100); ?>%;" aria-valuenow="<?php echo $storage_info['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                                        <?php echo number_format($storage_info['percentage'], 1); ?>%
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-4">
                                                                <div class="col-md-6">
                                                                    <p><strong>File Storage:</strong></p>
                                                                    <p><?php echo number_format($storage_info['storage_used'], 2); ?> GB</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Database Size:</strong></p>
                                                                    <p><?php echo number_format($storage_info['db_size'], 2); ?> GB</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Storage Info</h5>
                                                                <p class="card-text">Monitor your storage usage and upgrade when needed.</p>
                                                                <a href="#" class="btn btn-primary btn-sm">View Details</a>
                                                                <a href="#" class="btn btn-secondary btn-sm">Upgrade Storage</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Security Tab -->
                                            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h4>Security Settings</h4>
                                                        <hr>
                                                        
                                                        <div class="security-options">
                                                            <div class="row mb-4">
                                                                <div class="col-md-6">
                                                                    <h5>Change Password</h5>
                                                                    <p class="text-muted">Update your school admin password</p>
                                                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>Two-Factor Authentication</h5>
                                                                    <p class="text-muted">Add an extra layer of security</p>
                                                                    <a href="#" class="btn btn-secondary btn-sm">Enable 2FA</a>
                                                                </div>
                                                            </div>

                                                            <div class="row mb-4">
                                                                <div class="col-md-6">
                                                                    <h5>Login History</h5>
                                                                    <p class="text-muted">View your login activity</p>
                                                                    <a href="#" class="btn btn-secondary btn-sm">View History</a>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>Active Sessions</h5>
                                                                    <p class="text-muted">Manage your active sessions</p>
                                                                    <a href="#" class="btn btn-secondary btn-sm">Manage Sessions</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Security Tips</h5>
                                                                <ul class="list-unstyled">
                                                                    <li><i class="fa fa-check text-success mr-2"></i>Use strong passwords</li>
                                                                    <li><i class="fa fa-check text-success mr-2"></i>Enable 2FA</li>
                                                                    <li><i class="fa fa-check text-success mr-2"></i>Monitor login history</li>
                                                                    <li><i class="fa fa-check text-success mr-2"></i>Regular backups</li>
                                                                </ul>
                                                            </div>
                                                        </div>
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
                    <div class="col col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="#">School SAAS</a></p>
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

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="changePasswordForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_password">
                        
                        <!-- Alert Messages -->
                        <div id="passwordModalAlert" class="alert d-none" role="alert"></div>

                        <div class="form-group">
                            <label for="currentPassword">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter your current password" required>
                            <small class="form-text text-muted">Enter your current password to verify your identity</small>
                        </div>

                        <div class="form-group">
                            <label for="newPassword">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" required>
                            <small class="form-text text-muted">Password must be at least 8 characters long</small>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
                            <small class="form-text text-muted">Re-enter your new password</small>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimum 8 characters long</li>
                                <li>Current password must match</li>
                                <li>New passwords must match</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const changePasswordForm = document.getElementById('changePasswordForm');
            const passwordModalAlert = document.getElementById('passwordModalAlert');

            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const currentPassword = document.getElementById('currentPassword').value;
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;

                    // Clear previous alerts
                    passwordModalAlert.classList.add('d-none');
                    passwordModalAlert.innerHTML = '';
                    passwordModalAlert.className = 'alert d-none';

                    // Validation
                    if (!currentPassword) {
                        showAlert('Current password is required', 'danger');
                        return;
                    }

                    if (!newPassword || !confirmPassword) {
                        showAlert('New password fields are required', 'danger');
                        return;
                    }

                    if (newPassword !== confirmPassword) {
                        showAlert('New passwords do not match', 'danger');
                        return;
                    }

                    if (newPassword.length < 8) {
                        showAlert('Password must be at least 8 characters long', 'danger');
                        return;
                    }

                    // Submit form
                    const formData = new FormData(changePasswordForm);
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Check if success message is in the response
                        if (html.includes('Password updated successfully')) {
                            showAlert('Password updated successfully! Logging out...', 'success');
                            // Call logout function after 1 second
                            setTimeout(function() {
                                logout();
                            }, 1000);
                        } else if (html.includes('did not match') || html.includes('Current password')) {
                            showAlert('Current password did not match. Please try again.', 'danger');
                        } else {
                            showAlert('An error occurred. Please try again.', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred. Please try again.', 'danger');
                    });
                });

                function showAlert(message, type) {
                    passwordModalAlert.innerHTML = message;
                    passwordModalAlert.className = 'alert alert-' + type;
                    passwordModalAlert.classList.remove('d-none');
                }
            }
        });

        // Logout function
        function logout() {
            const logoutForm = document.createElement('form');
            logoutForm.method = 'POST';
            logoutForm.innerHTML = '<input type="hidden" name="action" value="logout">';
            document.body.appendChild(logoutForm);
            logoutForm.submit();
        }
    </script>
</body>


</html>