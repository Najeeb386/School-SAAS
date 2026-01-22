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
                                        <h1>Request Details</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item" aria-current="page">
                                                    new Requests
                                                </li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Request Details 
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                               <div class="d-flex justify-content-end">
                                    <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                                        <i class="ti ti-arrow-left mr-1"></i> Back
                                    </a>
                                </div>
                                <!-- end page title -->
                            </div>
                        </div>
                        <!-- start row -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Request Information</h5>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Request ID:</label>
                                                    <p id="requestId" class="form-control-plaintext">REQ-001234</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Request Date:</label>
                                                    <p id="requestDate" class="form-control-plaintext">2026-01-22</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">School Name:</label>
                                                    <p id="schoolName" class="form-control-plaintext">ABC School</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">School Domain:</label>
                                                    <p id="schoolDomain" class="form-control-plaintext">abc-school.com</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Email:</label>
                                                    <p id="email" class="form-control-plaintext">admin@abc-school.com</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Contact Number:</label>
                                                    <p id="contactNo" class="form-control-plaintext">+92-300-1234567</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Number of Students:</label>
                                                    <p id="students" class="form-control-plaintext">500</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Requested Plan:</label>
                                                    <p id="plan" class="form-control-plaintext">Premium</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Principal Name:</label>
                                                    <p id="principalName" class="form-control-plaintext">John Doe</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Principal Email:</label>
                                                    <p id="principalEmail" class="form-control-plaintext">john@abc-school.com</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Additional Information:</label>
                                                    <p id="additionalInfo" class="form-control-plaintext">This is a sample request for a new school registration in the SAAS platform.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Status:</label>
                                                    <p id="status" class="form-control-plaintext"><span class="badge badge-warning">Pending</span></p>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <h5 class="card-title mb-3">Action</h5>
                                                <button type="button" class="btn btn-success mr-2" id="acceptBtn">
                                                    <i class="ti ti-check mr-1"></i> Accept Request
                                                </button>
                                                <button type="button" class="btn btn-danger" id="rejectBtn">
                                                    <i class="ti ti-close mr-1"></i> Reject Request
                                                </button>
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
    
    <!-- Request Details Script -->
    <script>
        document.getElementById('acceptBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to accept this request?')) {
                // Get request ID
                const requestId = document.getElementById('requestId').textContent;
                const requestData = {
                    requestId: requestId,
                    action: 'accept',
                    timestamp: new Date().toISOString()
                };
                
                console.log('Request Accepted:', requestData);
                
                // Show success message
                alert('Request has been accepted successfully!');
                
                // You can add API call here to submit to backend
                // Example: 
                // fetch('/api/requests/accept', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify(requestData)
                // })
                // .then(response => response.json())
                // .then(data => {
                //     console.log('Success:', data);
                //     // Redirect back to requests list
                //     window.location.href = './requests.php';
                // });
            }
        });

        document.getElementById('rejectBtn').addEventListener('click', function() {
            // Show rejection reason modal or prompt
            const reason = prompt('Please enter the reason for rejection:');
            
            if (reason !== null && reason.trim() !== '') {
                const requestId = document.getElementById('requestId').textContent;
                const requestData = {
                    requestId: requestId,
                    action: 'reject',
                    reason: reason,
                    timestamp: new Date().toISOString()
                };
                
                console.log('Request Rejected:', requestData);
                
                // Show success message
                alert('Request has been rejected successfully!');
                
                // You can add API call here to submit to backend
                // Example:
                // fetch('/api/requests/reject', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify(requestData)
                // })
                // .then(response => response.json())
                // .then(data => {
                //     console.log('Success:', data);
                //     // Redirect back to requests list
                //     window.location.href = './requests.php';
                // });
            }
        });
    </script>