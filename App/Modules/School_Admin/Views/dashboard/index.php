<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in to access this page
 */
require_once __DIR__ . '/../../../../Config/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>School Admin Dashboard</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="School Admin Dashboard - Manage your school" />
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
                        <!-- Welcome Banner and To-dos Row -->
                        <div class="row mb-4 align-items-stretch">
                            <!-- Welcome Banner -->
                            <div class="col-lg-8">
                                <div class="card" style="background: linear-gradient(135deg, #1a2a4a 0%, #2d4563 100%); border: none; border-radius: 15px; overflow: hidden; height: 100%;">
                                    <div class="card-body p-5" style="color: white; display: flex; flex-direction: column; justify-content: center;">
                                        <h1 style="font-size: 36px; font-weight: 700; margin-bottom: 10px; margin-top: 0; color: #fff;">Welcome back</h1>
                                        <h2 style="font-size: 32px; margin-bottom: 20px; margin-top: 0; color: #fff;">
                                            <?php 
                                                $school_id = $_SESSION['school_id'] ?? null;
                                                $school_name = 'School Admin';
                                                if ($school_id) {
                                                    try {
                                                        require_once __DIR__ . '/../../../../Core/database.php';
                                                        $db = \Database::connect();
                                                        $stmt = $db->prepare("SELECT name FROM schools WHERE id = :id LIMIT 1");
                                                        $stmt->execute(['id' => $school_id]);
                                                        $school = $stmt->fetch(PDO::FETCH_ASSOC);
                                                        if ($school && !empty($school['name'])) {
                                                            $school_name = htmlspecialchars($school['name']);
                                                        }
                                                    } catch (Exception $e) {
                                                        // Use default name
                                                    }
                                                }
                                                echo $school_name;
                                            ?> 👋
                                        </h2>
                                        <p style="font-size: 14px; opacity: 0.9; margin: 0; color: #fff;">Here's what's happening in your school today.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- To-dos Card -->
                            <div class="col-lg-4">
                                <div class="card" style="border: none; border-radius: 15px; height: 100%;">
                                    <div class="card-body d-flex flex-column justify-content-center">
                                        <h5 style="color: #999; margin-bottom: 20px; font-weight: 600;">To-dos</h5>
                                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; min-height: 120px;">
                                            <p style="color: #ccc; font-size: 14px; margin: 0 0 20px 0;">No To-dos</p>
                                            <button class="btn btn-dark btn-sm" style="border-radius: 6px;"><i class="fa fa-plus mr-2"></i> Add New</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Action Cards -->
                        <div class="row mb-4">
                            <div class="col-lg-2 col-md-6 mb-3">
                                <div class="card" style="background: #e8f1ff; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <div class="card-body text-center p-4">
                                        <div style="font-size: 40px; margin-bottom: 10px;">👥</div>
                                        <h6 class="text-primary">Add Students</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-3">
                                <div class="card" style="background: #f8e8ff; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <div class="card-body text-center p-4">
                                        <div style="font-size: 40px; margin-bottom: 10px;">👨‍🏫</div>
                                        <h6 style="color: #d946ef;">Add Staff</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-3">
                                <div class="card" style="background: #fffde8; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <div class="card-body text-center p-4">
                                        <div style="font-size: 40px; margin-bottom: 10px;">💰</div>
                                        <h6 style="color: #eab308;">Add Income/Expense</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-3">
                                <div class="card" style="background: #ffe8f0; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <div class="card-body text-center p-4">
                                        <div style="font-size: 40px; margin-bottom: 10px;">💳</div>
                                        <h6 style="color: #ec4899;">Add Student Fee</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-3">
                                <div class="card" style="background: #ffe8d8; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <div class="card-body text-center p-4">
                                        <div style="font-size: 40px; margin-bottom: 10px;">🚫</div>
                                        <h6 style="color: #f97316;">Add Fine</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="row">
                            <!-- Total Profit with Three Layers -->
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card" style="border: none; border-radius: 10px;">
                                    <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                                        <h6 style="font-weight: 600; margin: 0;">Financial Overview</h6>
                                        <select class="form-control form-control-sm" style="width: auto; border: 1px solid #ddd;">
                                            <option>Today</option>
                                            <option>Week</option>
                                            <option>Month</option>
                                            <option>Year</option>
                                            <option>Custom</option>
                                        </select>
                                    </div>
                                    <div class="card-body" style="text-align: center;">
                                        <!-- Three Layer Donut Chart -->
                                        <div style="display: flex; justify-content: center; align-items: center; position: relative; width: 200px; height: 200px; margin: 0 auto 30px;">
                                            <svg width="200" height="200" style="transform: rotate(-90deg);">
                                                <!-- Layer 1: Income (Blue) - Outer -->
                                                <circle cx="100" cy="100" r="85" fill="none" stroke="#3b82f6" stroke-width="20" stroke-dasharray="160 565" stroke-linecap="round"/>
                                                
                                                <!-- Layer 2: Expense (Orange) - Middle -->
                                                <circle cx="100" cy="100" r="60" fill="none" stroke="#f97316" stroke-width="18" stroke-dasharray="85 377" stroke-linecap="round"/>
                                                
                                                <!-- Layer 3: Profit (Green) - Inner -->
                                                <circle cx="100" cy="100" r="35" fill="none" stroke="#22c55e" stroke-width="16" stroke-dasharray="110 220" stroke-linecap="round"/>
                                            </svg>
                                            <div style="position: absolute; text-align: center;">
                                                <div style="font-size: 28px; font-weight: 700; color: #1a1a1a;">0</div>
                                                <div style="font-size: 12px; color: #666;">Total</div>
                                            </div>
                                        </div>

                                        <!-- Legend -->
                                        <div style="text-align: left; padding: 0 10px;">
                                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                                <span style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px; margin-right: 10px;"></span>
                                                <div>
                                                    <div style="font-size: 12px; color: #666;">Income</div>
                                                    <div style="font-size: 16px; font-weight: 600; color: #1a1a1a;">PKR 0</div>
                                                </div>
                                            </div>
                                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                                <span style="width: 12px; height: 12px; background: #f97316; border-radius: 2px; margin-right: 10px;"></span>
                                                <div>
                                                    <div style="font-size: 12px; color: #666;">Expense</div>
                                                    <div style="font-size: 16px; font-weight: 600; color: #1a1a1a;">PKR 0</div>
                                                </div>
                                            </div>
                                            <div style="display: flex; align-items: center;">
                                                <span style="width: 12px; height: 12px; background: #22c55e; border-radius: 2px; margin-right: 10px;"></span>
                                                <div>
                                                    <div style="font-size: 12px; color: #666;">Profit</div>
                                                    <div style="font-size: 16px; font-weight: 600; color: #1a1a1a;">PKR 0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Analytics -->
                            <div class="col-lg-8 col-md-6 mb-4">
                                <div class="card" style="border: none; border-radius: 10px; height: 100%;">
                                    <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                                        <h6 style="font-weight: 600; margin: 0;">Yearly Analytics</h6>
                                        <select class="form-control form-control-sm" style="width: 200px;">
                                            <option>24 Nov 2024 - 24 Nov 2025</option>
                                        </select>
                                    </div>
                                    <div class="card-body" style="display: flex; flex-direction: column;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                                            <div>
                                                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                                    <span style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px; margin-right: 8px;"></span>
                                                    <span style="color: #666; font-size: 12px;">Total income</span>
                                                </div>
                                                <div style="display: flex; align-items: center;">
                                                    <span style="width: 12px; height: 12px; background: #fbbf24; border-radius: 2px; margin-right: 8px;"></span>
                                                    <span style="color: #666; font-size: 12px;">Total expenses</span>
                                                </div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">0</div>
                                                <div style="color: #999; font-size: 12px;">0</div>
                                            </div>
                                        </div>
                                        <!-- Bar Chart -->
                                        <div style="height: 250px; display: flex; align-items: flex-end; justify-content: space-around; gap: 20px; padding: 20px 0; flex: 1;">
                                            <div style="width: 8%; background: #e5e7eb; height: 30%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 25%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 35%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 40%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 28%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 32%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 38%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 30%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 26%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 34%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 29%;"></div>
                                            <div style="width: 8%; background: #e5e7eb; height: 33%;"></div>
                                        </div>
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
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

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
