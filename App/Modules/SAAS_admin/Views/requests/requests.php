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
                                        <h1>New Requests</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    New Requests
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                                
                                <!-- end page title -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <!-- Filters Section -->
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <input type="text" id="filterName" class="form-control" placeholder="Search by School Name">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" id="filterStartDate" class="form-control" placeholder="Start Date">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="date" id="filterEndDate" class="form-control" placeholder="End Date">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <button id="resetFilter" class="btn btn-secondary">Reset Filters</button>
                                            </div>
                                        </div>
                                        <!-- End Filters Section -->
                                        <div class="datatable-wrapper table-responsive">
                                            <table id="datatable" class="display compact table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>School Name</th>
                                                        <th>Domain</th>
                                                        <th>email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Tiger Nixon</td>
                                                        <td>System Architect</td>
                                                        <td>Edinburgh</td>
                                                        <td>61</td>
                                                        <td>2011/04/25</td>
                                                        <td>$320,800</td>
                                                        <td>2026/02/15</td>
                                                        <td>test</td>
                                                        <td><a href="./request_details.php" class="btn btn-primary">Details</a></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>School Name</th>
                                                        <th>Domain</th>
                                                        <th>email</th>
                                                        <th>Contact No</th>
                                                        <th>Students</th>
                                                        <th>Plan</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
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

    <!-- Add School Modal -->
    <div class="modal fade" id="addSchoolModal" tabindex="-1" role="dialog" aria-labelledby="addSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSchoolModalLabel">Add New School</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addSchoolForm">
                        <div class="form-group">
                            <label for="schoolName">School Name</label>
                            <input type="text" class="form-control" id="schoolName" placeholder="Enter school name" required>
                        </div>
                        <div class="form-group">
                            <label for="domain">Domain</label>
                            <input type="text" class="form-control" id="domain" placeholder="Enter domain" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="contactNo">Contact Number</label>
                            <input type="tel" class="form-control" id="contactNo" placeholder="Enter contact number" required>
                        </div>
                        <div class="form-group">
                            <label for="students">Number of Students</label>
                            <input type="number" class="form-control" id="students" placeholder="Enter number of students" required>
                        </div>
                        <div class="form-group">
                            <label for="plan">Plan</label>
                            <select class="form-control" id="plan" required>
                                <option value="">Select Plan</option>
                                <option value="Basic">Basic</option>
                                <option value="Standard">Standard</option>
                                <option value="Premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dueDate">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSchoolBtn">Save School</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add School Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>
    
    <!-- Modal Script -->
    <script>
        document.getElementById('saveSchoolBtn').addEventListener('click', function() {
            const form = document.getElementById('addSchoolForm');
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                // Here you can add the code to submit the form data
                console.log('Form is valid - Ready for backend submission');
                const formData = {
                    schoolName: document.getElementById('schoolName').value,
                    domain: document.getElementById('domain').value,
                    email: document.getElementById('email').value,
                    contactNo: document.getElementById('contactNo').value,
                    students: document.getElementById('students').value,
                    plan: document.getElementById('plan').value,
                    dueDate: document.getElementById('dueDate').value,
                    status: document.getElementById('status').value
                };
                console.log(formData);
                
                // Reset form
                form.reset();
                // Close modal
                $('#addSchoolModal').modal('hide');
            }
        });
    </script>