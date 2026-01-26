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

// Database connection
require_once '../../../../Config/connection.php';

// Get dashboard statistics
// 1. Total registered schools
$schoolQuery = "SELECT COUNT(*) as total FROM schools WHERE status != 'blocked'";
$schoolResult = $DB_con->query($schoolQuery)->fetch(PDO::FETCH_ASSOC);
$totalSchools = $schoolResult['total'] ?? 0;

// 2. Active schools
$activeQuery = "SELECT COUNT(*) as total FROM schools WHERE status = 'active'";
$activeResult = $DB_con->query($activeQuery)->fetch(PDO::FETCH_ASSOC);
$activeSchools = $activeResult['total'] ?? 0;

// 3. Total revenue (all paid amounts)
$revenueQuery = "SELECT SUM(paid_amount) as total FROM saas_payments";
$revenueResult = $DB_con->query($revenueQuery)->fetch(PDO::FETCH_ASSOC);
$totalRevenue = floatval($revenueResult['total'] ?? 0);

// 4. Outstanding/Unpaid (Expenses view)
$outstandingQuery = "SELECT SUM(total_amount - paid_amount - discounted_amount) as outstanding FROM saas_billing_cycles WHERE (total_amount - paid_amount - discounted_amount) > 0";
$outstandingResult = $DB_con->query($outstandingQuery)->fetch(PDO::FETCH_ASSOC);
$totalOutstanding = floatval($outstandingResult['outstanding'] ?? 0);

// 5. This month's revenue
$currentMonth = date('Y-m-01');
$currentMonthQuery = "SELECT SUM(paid_amount) as total FROM saas_payments WHERE DATE(payment_date) >= ?";
$monthStmt = $DB_con->prepare($currentMonthQuery);
$monthStmt->execute([$currentMonth]);
$monthResult = $monthStmt->fetch(PDO::FETCH_ASSOC);
$monthRevenue = floatval($monthResult['total'] ?? 0);

// 6. Average daily revenue
$daysInMonth = date('t');
$avgDailyRevenue = $monthRevenue / $daysInMonth;

// 7. Blocked schools
$blockedQuery = "SELECT COUNT(*) as total FROM schools WHERE status = 'blocked'";
$blockedResult = $DB_con->query($blockedQuery)->fetch(PDO::FETCH_ASSOC);
$blockedSchools = $blockedResult['total'] ?? 0;

// 8. Pending schools
$pendingQuery = "SELECT COUNT(*) as total FROM schools WHERE status = 'pending'";
$pendingResult = $DB_con->query($pendingQuery)->fetch(PDO::FETCH_ASSOC);
$pendingSchools = $pendingResult['total'] ?? 0;

// 9. Revenue by month (last 6 months)
$revenueByMonthQuery = "SELECT DATE_TRUNC(payment_date, MONTH) as month, SUM(paid_amount) as total FROM saas_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month DESC LIMIT 6";
$revenueByMonthStmt = $DB_con->prepare("SELECT DATE_TRUNC(payment_date, MONTH) as month, SUM(paid_amount) as total FROM saas_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(payment_date) ORDER BY MONTH(payment_date) DESC LIMIT 6");

// Alternative query that works with MySQL
$revenueByMonthQuery = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(paid_amount) as total FROM saas_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(payment_date, '%Y-%m') ORDER BY month DESC";
$revenueByMonthStmt = $DB_con->prepare($revenueByMonthQuery);
$revenueByMonthStmt->execute();
$revenueByMonth = $revenueByMonthStmt->fetchAll(PDO::FETCH_ASSOC);

// 10. Total billed amount
$billedQuery = "SELECT SUM(total_amount) as total FROM saas_billing_cycles";
$billedResult = $DB_con->query($billedQuery)->fetch(PDO::FETCH_ASSOC);
$totalBilled = floatval($billedResult['total'] ?? 0);

// 11. Total discounted amount
$discountQuery = "SELECT SUM(discounted_amount) as total FROM saas_billing_cycles";
$discountResult = $DB_con->query($discountQuery)->fetch(PDO::FETCH_ASSOC);
$totalDiscount = floatval($discountResult['total'] ?? 0);

// Currency formatter
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 0);
}

function formatCurrencyDecimal($amount) {
    return 'Rs ' . number_format($amount, 2);
}
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
                                                    <a class="mb-0 ml-auto font-weight-bold" href="../../Schools/Views/schools/schools.php"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics7"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> <?php echo $totalSchools; ?></h3>
                                                        <p><?php echo $activeSchools; ?> Active</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-xxl-right border-bottom border-xxl-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Outstanding</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="../../Finance/Views/finance.php?tab=defaulters"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics8"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i> <?php echo formatCurrency($totalOutstanding); ?></h3>
                                                        <p>Unpaid dues</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="p-20 border-lg-right border-bottom border-lg-bottom-0">
                                                <div class="d-flex m-b-10">
                                                    <p class="mb-0 font-regular text-muted font-weight-bold">Revenue</p>
                                                    <a class="mb-0 ml-auto font-weight-bold" href="../../Finance/Views/finance.php?tab=payments"><i class="ti ti-more-alt"></i> </a>
                                                </div>
                                                <div class="d-block d-sm-flex h-100 align-items-center">
                                                    <div class="apexchart-wrapper">
                                                        <div id="analytics9"></div>
                                                    </div>
                                                    <div class="statistics mt-3 mt-sm-0 ml-sm-auto text-center text-sm-right">
                                                        <h3 class="mb-0"><i class="icon-arrow-up-circle"></i><?php echo formatCurrency($avgDailyRevenue); ?></h3>
                                                        <p>Avg. per day</p>
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
                                                                <span><i class="fa fa-circle text-primary pr-2"></i> Total Billed</span> <span class="pl-2 font-weight-bold"><?php echo formatCurrency($totalBilled); ?></span></li>
                                                            <li class="d-flex py-1"><span><i class="fa fa-circle text-warning pr-2"></i> Discounted</span> <span class="pl-2 font-weight-bold"><?php echo formatCurrency($totalDiscount); ?></span></li>
                                                            <li class="d-flex py-1"><span><i class="fa fa-circle text-info pr-2"></i> Revenue</span> <span class="pl-2 font-weight-bold"><?php echo formatCurrency($totalRevenue); ?></span></li>
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
                                                        <p>Total Schools</p>
                                                        <h3 class="tex-dark mb-0"><?php echo $totalSchools; ?></h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-up"></i> <?php echo $activeSchools; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Revenue</p>
                                                        <h3 class="tex-dark mb-0"><?php echo formatCurrency($totalRevenue/1000); ?>K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-up"></i> <?php echo round(($monthRevenue/$totalRevenue)*100); ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Outstanding</p>
                                                        <h3 class="tex-dark mb-0"><?php echo formatCurrency($totalOutstanding/1000); ?>K</h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-arrow-up"></i> <?php echo round(($totalOutstanding/$totalBilled)*100); ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-xs-6 col-lg-3">
                                                <div class="row mb-2 pb-3 align-items-end">
                                                    <div class="col">
                                                        <p>Blocked Schools</p>
                                                        <h3 class="tex-dark mb-0"><?php echo $blockedSchools; ?></h3>
                                                    </div>
                                                    <div class="col ml-auto">
                                                        <span><i class="fa fa-alert"></i> <?php echo $pendingSchools; ?></span>
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
                                                <h2><?php echo formatCurrency($monthRevenue/1000); ?>k</h2>
                                                <span class="d-block mb-2 font-16">This Month Revenue</span>
                                                <span class="d-block mb-2 mb-sm-5"><b class="text-<?php echo ($monthRevenue > $totalRevenue/12) ? 'success' : 'danger'; ?>">
                                                    <?php echo round((($monthRevenue - ($totalRevenue/12))/($totalRevenue/12))*100); ?>%
                                                </b> vs avg month</span>
                                                <p class="mb-3">Track your financial performance with real-time revenue data from all subscribed schools.</p>
                                                <a class="btn btn-round btn-inverse-primary mb-3 mb-sm-0" href="../../Finance/Views/finance.php"><b>View Finance </b></a>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="apexchart-wrapper">
                                                    <div id="analytics2" class="chart-fit"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-top my-4"></div>
                                        <h4 class="card-title">Revenue by Plan</h4>
                                        <div class="row">
                                            <div class="col-12 col-md-3">
                                                <span>Active: <b><?php echo formatCurrency($totalRevenue); ?></b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>Billed: <b><?php echo formatCurrency($totalBilled); ?></b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo round(($totalBilled/$totalBilled)*100); ?>%;" aria-valuenow="<?php echo round(($totalBilled/$totalBilled)*100); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>Outstanding: <b><?php echo formatCurrency($totalOutstanding); ?></b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo round(($totalOutstanding/$totalBilled)*100); ?>%;" aria-valuenow="<?php echo round(($totalOutstanding/$totalBilled)*100); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <span>Discount: <b><?php echo formatCurrency($totalDiscount); ?></b></span>
                                                <div class="progress my-3" style="height: 4px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo round(($totalDiscount/$totalBilled)*100); ?>%;" aria-valuenow="<?php echo round(($totalDiscount/$totalBilled)*100); ?>" aria-valuemin="0" aria-valuemax="100"></div>
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
                        <p><a target="_blank" href="https://www.templateshub.net">School SAAS Management System</a></p>
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