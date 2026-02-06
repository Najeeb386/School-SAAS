<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../Controllers/StudentAttendanceController.php';
require_once __DIR__ . '/../../../Models/StudentAttendanceModel.php';

// Initialize controller
$school_id = $_SESSION['school_id'] ?? null;
$controller = null;
$totalStudents = 0;
$attendanceStats = ['P' => 0, 'A' => 0, 'L' => 0, 'HD' => 0];
$classWiseData = [];

if ($school_id) {
    $controller = new \App\Modules\School_Admin\Controllers\StudentAttendanceController((int)$school_id);
    $totalStudents = $controller->getTotalStudents();
    $attendanceStats = $controller->getAttendanceStats();
    $classWiseData = $controller->getClassWiseAttendanceSummary();
}
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
    <style>
        .row.g-3 > .col-12.col-sm-6.col-lg-4 {
            display: flex;
            flex-wrap: wrap;
        }
        @media (min-width: 992px) {
            .row.g-3 > .col-12.col-sm-6.col-lg-4 {
                flex: 0 0 calc(33.333% - 0.5rem);
                max-width: calc(33.333% - 0.5rem);
            }
        }
        @media (min-width: 576px) and (max-width: 991px) {
            .row.g-3 > .col-12.col-sm-6.col-lg-4 {
                flex: 0 0 calc(50% - 0.5rem);
                max-width: calc(50% - 0.5rem);
            }
        }
        @media (max-width: 575px) {
            .row.g-3 > .col-12.col-sm-6.col-lg-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
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
                                <h3 class="mb-3">Students Attendance</h3>
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
                                                <h5><?php echo $totalStudents; ?></h5>
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
                                                <h5 id="presentCount" class="text-success"><?php echo $attendanceStats['P']; ?></h5>
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
                                                <h5 id="absentCount" class="text-danger"><?php echo $attendanceStats['A']; ?></h5>
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
                                                <h5 id="leaveCount" class="text-warning"><?php echo $attendanceStats['L']; ?></h5>
                                            </div>
                                            <i class="fas fa-sun fa-2x text-warning opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- stats end here  -->
                         <!-- class wise attendence -->
                          <div class="card">
                            <div class="card-header">
                                <h3>Class Wise Attendance</h3>
                            </div>
                            <div class="card-body">
                               <div class="row g-3">
                               <?php 
                               if (empty($classWiseData)) {
                                   echo '<div class="col-12"><p class="text-muted">No attendance data available for today.</p></div>';
                               } else {
                                   // Group data by class
                                   $classesByName = [];
                                   foreach ($classWiseData as $item) {
                                       $className = $item['class_name'];
                                       if (!isset($classesByName[$className])) {
                                           $classesByName[$className] = [
                                               'class_id' => $item['class_id'],
                                               'sections' => []
                                           ];
                                       }
                                       $classesByName[$className]['sections'][] = $item;
                                   }
                                   
                                   // Display each class with its sections
                                   foreach ($classesByName as $className => $classData) {
                                       $classId = $classData['class_id'];
                                       $sections = $classData['sections'];
                               ?>
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <a href="attendence_marking_classwise.php?class_id=<?php echo $classId; ?>" style="text-decoration: none; color: inherit;">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body">
                                                <p style="color: #dc3545; font-weight: bold; margin-bottom: 0.5rem;">Mark Today's Attendance</p>
                                                <h5 class="card-title mb-3"><?php echo htmlspecialchars($className); ?></h5>
                                                <?php 
                                                foreach ($sections as $section) {
                                                    $present = (int)($section['present_count'] ?? 0);
                                                    $absent = (int)($section['absent_count'] ?? 0);
                                                    $leave = (int)($section['leave_count'] ?? 0);
                                                    $halfDay = (int)($section['half_day_count'] ?? 0);
                                                ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <h6 class="mb-2"><strong><?php echo htmlspecialchars($section['section_name']); ?></strong></h6>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-success"><strong>P: <?php echo $present; ?></strong></small><br>
                                                            <small class="text-danger"><strong>A: <?php echo $absent; ?></strong></small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-warning"><strong>L: <?php echo $leave; ?></strong></small><br>
                                                            <small class="text-info"><strong>HD: <?php echo $halfDay; ?></strong></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php 
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                               <?php 
                                   }
                               }
                               ?>
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
