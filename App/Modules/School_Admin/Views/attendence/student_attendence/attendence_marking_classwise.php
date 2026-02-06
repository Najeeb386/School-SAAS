<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Students attendence</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School Admin Dashboard - Manage your school" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
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
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->
            <!-- begin app-container -->
            <div class="app-container">
              
                <!-- begin app-main -->
                <div class="" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-11">
                                <h3 class="mb-3">class name Attendance</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Students Attendance</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-1 mt-3">
                                <button onclick="window.history.back()" class="btn btn-primary"> <i class="fas fa-arrow-left"></i> Back</button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">Total Students</p>
                                                <h5 id="totalStaff">0</h5>
                                            </div>
                                            <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">Present Today</p>
                                                <h5 id="presentCount" class="text-success">0</h5>
                                            </div>
                                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-danger">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">Absent Today</p>
                                                <h5 id="absentCount" class="text-danger">0</h5>
                                            </div>
                                            <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-left-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">On Leave</p>
                                                <h5 id="leaveCount" class="text-warning">0</h5>
                                            </div>
                                            <i class="fas fa-sun fa-2x text-warning opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- stats end here  -->
                         <!-- Month/Year Filter Section (Enhanced Bootstrap Design) -->
                        <div class="card mt-4 mb-3 shadow-sm border-0">
                            <div class="card-header bg-white border-0 pb-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-2 fw-bold text-primary">
                                        <i class="fas fa-calendar-alt me-2"></i> Filter by Month & Year
                                    </h6>
                                </div>
                                <hr class="mt-2">
                            </div>

                            <div class="card-body pt-2">
                                <div class="row g-3 align-items-end">

                                    <div class="col-md-3">
                                        <label for="filterMonth" class="form-label fw-semibold">
                                            <i class="far fa-calendar me-1"></i> Month
                                        </label>
                                        <select id="filterMonth" class="form-select shadow-sm">
                                            <option value="0">January</option>
                                            <option value="1">February</option>
                                            <option value="2">March</option>
                                            <option value="3">April</option>
                                            <option value="4">May</option>
                                            <option value="5">June</option>
                                            <option value="6">July</option>
                                            <option value="7">August</option>
                                            <option value="8">September</option>
                                            <option value="9">October</option>
                                            <option value="10">November</option>
                                            <option value="11">December</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="filterYear" class="form-label fw-semibold">
                                            <i class="fas fa-clock me-1"></i> Year
                                        </label>
                                        <select id="filterYear" class="form-select shadow-sm">
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="filterDept" class="form-label fw-semibold">
                                            <i class="fas fa-building me-1"></i> Sections
                                        </label>
                                        <select id="filterDept" class="form-select shadow-sm">
                                            <option value="">All Departments</option>
                                            <option value="Teaching">Teaching</option>
                                            <option value="Library">Library</option>
                                            <option value="Admin">Admin</option>
                                            <option value="Support">Support</option>
                                            <option value="Finance">Finance</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end gap-2">
                                        <button id="applyMonthFilter" class="btn btn-primary flex-grow-1 shadow-sm">
                                            <i class="fas fa-filter me-1"></i> Apply Filter
                                        </button>
                                        <button id="resetMonthFilter" class="btn btn-outline-secondary shadow-sm">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>
            <!-- end app-container -->
            <!-- begin footer -->
            
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../../public/assets/js/app.js"></script>

    <!-- Hide loader on page load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 500);
        });
        
        // Fallback: hide loader after 2 seconds
        window.addEventListener('load', function() {
            var loader = document.querySelector('.loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
    </script>
</body>

</html>
