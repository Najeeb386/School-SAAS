<?php
/**
 * School Admin - Payrolls
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
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

            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../../include/navbar.php'; ?>
            </header>

            <div class="app-container">
                <?php include_once __DIR__ . '/../../../include/sidebar.php'; ?>

                <div class="app-main" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Payrolls</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item"><a href="../finance.php">Finance</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Payrolls</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <button class="btn btn-primary mr-2">New Payrun</button>
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
                                <a href="payruns.php" class="text-dark text-decoration-none">
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
                                                        <th>Date</th>
                                                        <th>Employees</th>
                                                        <th>Total Amount</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>September 2025</td>
                                                        <td>2025-09-30</td>
                                                        <td>45</td>
                                                        <td>₦4,500,000</td>
                                                        <td><span class="badge badge-success">Completed</span></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary">View</button>
                                                            <button class="btn btn-sm btn-outline-secondary">Download</button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>August 2025</td>
                                                        <td>2025-08-31</td>
                                                        <td>45</td>
                                                        <td>₦4,300,000</td>
                                                        <td><span class="badge badge-warning">Pending</span></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary">View</button>
                                                            <button class="btn btn-sm btn-outline-secondary">Process</button>
                                                        </td>
                                                    </tr>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 400);
        });
    </script>
</body>

</html>
