<?php
/**
 * Fees Management UI
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Fees - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Fees management" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .page-actions { display:flex; gap:10px }
        .card-hero .icon { font-size:28px }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader" style="display:none;"></div>
            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>
            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <div class="app-main" id="main">
                    <div class="container-fluid">
                       <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Students</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Students</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
    <div class="row mb-4">
		<div class="col-md-3">
			<a href="new_student.php">
                <div class="card card-hero p-3 text-center shadow-sm" id="cardNewStudent">
				<div class="icon text-primary mb-2"><i class="fas fa-user-plus"></i></div>
				<h5>New Student</h5>
				<p class="muted-small">Register a single student</p>
			</div>
            </a>
		</div>
		<div class="col-md-3">
			<a href="student_list.php">
                <div class="card card-hero p-3 text-center shadow-sm" id="cardStudentsList">
				<div class="icon text-success mb-2"><i class="fas fa-users"></i></div>
				<h5>Students List</h5>
				<p class="muted-small">View and manage all students</p>
			</div>
            </a>
		</div>
		<div class="col-md-3">
			<div class="card card-hero p-3 text-center shadow-sm" id="cardImport">
				<div class="icon text-warning mb-2"><i class="fas fa-file-upload"></i></div>
				<h5>Import</h5>
				<p class="muted-small">Bulk import from CSV</p>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card card-hero p-3 text-center shadow-sm" id="cardReports">
				<div class="icon text-info mb-2"><i class="fas fa-chart-line"></i></div>
				<h5>Reports</h5>
				<p class="muted-small">Attendance, performance & exports</p>
			</div>
		</div>
	</div>

                         

                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
    
</body>

</html>