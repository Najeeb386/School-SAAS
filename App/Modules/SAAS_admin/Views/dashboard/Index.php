<?php
// Start session and check authentication
session_start();

// Require authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /School-SAAS/App/Modules/Auth/login.php');
    exit;
}

// Prevent caching - force browser to reload on back button
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
 <script>
        // Prevent back button after logout
        // Store a flag in sessionStorage when page loads
        window.addEventListener('load', function() {
            sessionStorage.setItem('dashboardLoaded', 'true');
        });

        // Check on page show (when coming back)
        window.addEventListener('pageshow', function(event) {
            // If coming from browser back/forward
            if (event.persisted) {
                // Verify user is still logged in via AJAX
                fetch('/School-SAAS/App/Modules/Auth/check_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.logged_in) {
                            // User is not logged in, redirect to login
                            window.location.href = '/School-SAAS/App/Modules/Auth/login.php';
                        }
                    })
                    .catch(err => {
                        // If check fails, redirect to login for safety
                        window.location.href = '/School-SAAS/App/Modules/Auth/login.php';
                    });
            }
        });

        // Also check periodically if session is still valid
        setInterval(function() {
            fetch('/School-SAAS/App/Modules/Auth/check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        window.location.href = '/School-SAAS/App/Modules/Auth/login.php';
                    }
                })
                .catch(err => {
                    // Silent fail for periodic check
                });
        }, 5000); // Check every 5 seconds
    </script>
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
    <link rel="shortcut icon" href="assets/img/favicon.ico">
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
                            <div class="col-sm-12">
                                <div class="card card-statistics">
                                    <div class="row no-gutters">
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-xxl-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Registered Schools</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics7"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> 15,640</h3>
                                                        <p>Monthly visitor</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-xxl-right border-bottom border-xxl-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Expenses</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics8"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> 16,656</h3>
                                                        <p>This month</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-lg-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Revenue</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="#"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics9"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i>569</h3>
                                                        <p>Avg. Sales per day</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20">
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics10"></div>
                                                    </div>
                                                    <div class="statistics ml-sm-auto mt-4 mt-sm-0 pr-sm-5">
                                                        <ul class="list-style-none p-0">
                                                            <li class="d-flex py-1">
                                                                <span><i class="fa fa-circle text-primary pr-2"></i> Expenses</span> <span class="pl-2 font-weight-bold">456</span></li>
                                                            <li class="d-flex py-1"><span><i class="fa fa-circle text-warning pr-2"></i> Revenue</span> <span class="pl-2 font-weight-bold">256</span></li>
                                                            <li class="d-flex py-1"><span><i class="fa fa-circle text-info pr-2"></i> profit</span> <span class="pl-2 font-weight-bold">128</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xxl-7 m-b-30">
                                <div class="card card-statistics h-100 mb-0 apexchart-tool-force-top">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-heading">
                                            <h4 class="card-title">Site activity</h4>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Users</p>
                                                        <h3 class="tex-dark mb-0">8.6K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-down"></i> 2.5%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Revenue</p>
                                                        <h3 class="tex-dark mb-0">176K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-up"></i> 15%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Rate</p>
                                                        <h3 class="tex-dark mb-0">6.2K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-down"></i> 6.5%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Sessions</p>
                                                        <h3 class="tex-dark mb-0">44K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-down"></i> 1.8%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 px-0">
                                                <div class="apexchart-wrapper p-inherit">
                                                    <div id="analytics1"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-5 m-b-30">
                                <div class="card card-statistics h-100 mb-0">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-heading">
                                            <h4 class="card-title">Income Analysis</h4>
                                        </div>
                                        <div class="dropdown">
                                            <a class="p-2" href="#!" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fe fe-circle"></i>
                                            </a>
                                            <div class="dropdown-menu custom-dropdown dropdown-menu-right p-4">
                                                <h6 class="mb-1">Action</h6>
                                                <a class="dropdown-item" href="#!"><i class="fa-fw fa fa-file-o pr-2"></i>View reports</a>
                                                <a class="dropdown-item" href="#!"><i class="fa-fw fa fa-edit pr-2"></i>Edit reports</a>
                                                <a class="dropdown-item" href="#!"><i class="fa-fw fa fa-bar-chart-o pr-2"></i>Statistics</a>
                                                <h6 class="mb-1 mt-3">Export</h6>
                                                <a class="dropdown-item" href="#!"><i class="fa-fw fa fa-file-pdf-o pr-2"></i>Export to PDF</a>
                                                <a class="dropdown-item" href="#!"><i class="fa-fw fa fa-file-excel-o pr-2"></i>Export to CSV</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <h2>3.8k</h2>
                                                <span class="d-block mb-2 font-16">AVG sessions</span>
                                                <span class="d-block mb-2 mb-sm-5"><b class="text-primary">-65.88%</b> vs last 1 months</span>
                                                <p class="mb-3">Sapiente corporis fugiat, doloremque eveniet nostrum id molestiae quaerat!</p>
                                                <a class="btn btn-round btn-inverse-primary mb-3 mb-sm-0" href="#"><b>View details </b></a>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="apexchart-wrapper">
                                                    <div id="analytics2" class="chart-fit"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-top my-4"></div>
                                        <h4 class="card-title">Income by department</h4>
                                        <div class="row">
                                            <div class="col-12 col-md-3">
                                                <span>FCBK: <b>$1,475</b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>GGL: <b>$23,475</b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 66%;" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>APL: <b>$1,658</b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>SMG: <b>$12,489</b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: 55%;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
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
                        <p>&copy; Copyright 2019. All rights reserved.</p>
                    </div>
                   <div class="col  col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="https://www.templateshub.net">Templates Hub</a></p>
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