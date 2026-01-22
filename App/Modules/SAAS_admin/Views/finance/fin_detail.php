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
                                <div class="mb-3">
                                    <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                                        <i class="ti ti-arrow-left mr-1"></i> Back
                                    </a>
                                </div>
                                <div class="d-block d-lg-flex flex-nowrap align-items-center">
                                    <div class="page-title mr-4 pr-4 border-right">
                                        <h1>Finance Details</h1>
                                    </div>
                                    <div class="breadcrumb-bar d-flex align-items-center">
                                        <nav>
                                            <ol class="breadcrumb p-0 m-b-0">
                                                <li class="breadcrumb-item">
                                                    <a href="index.html"><i class="ti ti-home"></i></a>
                                                </li>
                                                <li class="breadcrumb-item">Dashboard</li>
                                                <li class="breadcrumb-item" aria-current="page">
                                                    Finance
                                                </li>
                                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                                    Details
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                            
                                <!-- end page title -->
                            </div>
                        </div>
                        <!-- School Finance Overview Section -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card card-statistics">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h5 class="card-title">ABC School Finance Summary</h5>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPaymentModal">
                                                <i class="ti ti-plus mr-1"></i> Add Payment
                                            </button>
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">School:</label>
                                                    <p class="form-control-plaintext">ABC School</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Plan:</label>
                                                    <p class="form-control-plaintext">Premium</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Annual Fee:</label>
                                                    <p class="form-control-plaintext text-success font-weight-bold">PKR 50,000</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Total Paid:</label>
                                                    <p class="form-control-plaintext text-info font-weight-bold">PKR 45,000</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Overview -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Payment Status Overview</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center p-3 border-right">
                                                    <h4 class="text-success mb-2">
                                                        <i class="fa fa-check-circle"></i>
                                                    </h4>
                                                    <p class="text-muted mb-1">Full Paid</p>
                                                    <h5 class="font-weight-bold">PKR 25,000</h5>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center p-3 border-right">
                                                    <h4 class="text-warning mb-2">
                                                        <i class="fa fa-adjust"></i>
                                                    </h4>
                                                    <p class="text-muted mb-1">Half Paid</p>
                                                    <h5 class="font-weight-bold">PKR 15,000</h5>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center p-3 border-right">
                                                    <h4 class="text-info mb-2">
                                                        <i class="fa fa-cube"></i>
                                                    </h4>
                                                    <p class="text-muted mb-1">Quarter Paid</p>
                                                    <h5 class="font-weight-bold">PKR 5,000</h5>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center p-3">
                                                    <h4 class="text-danger mb-2">
                                                        <i class="fa fa-times-circle"></i>
                                                    </h4>
                                                    <p class="text-muted mb-1">Pending</p>
                                                    <h5 class="font-weight-bold">PKR 5,000</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Yearly Payment Records -->
                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Yearly Payment Records</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- 2026 -->
                                        <div class="payment-year mb-4">
                                            <h6 class="font-weight-bold mb-3">
                                                <i class="fa fa-calendar mr-2"></i> 2026
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Payment Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>January</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2026-01-15</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 2025 -->
                                        <div class="payment-year mb-4">
                                            <h6 class="font-weight-bold mb-3">
                                                <i class="fa fa-calendar mr-2"></i> 2025
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Payment Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>December</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2025-12-20</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>November</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-warning">Half Paid</span></td>
                                                            <td>2025-11-18</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>October</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-info">Quarter Paid</span></td>
                                                            <td>2025-10-15</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>September</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2025-09-12</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>August</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-danger">Pending</span></td>
                                                            <td>-</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 2024 -->
                                        <div class="payment-year">
                                            <h6 class="font-weight-bold mb-3">
                                                <i class="fa fa-calendar mr-2"></i> 2024
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Amount</th>
                                                            <th>Status</th>
                                                            <th>Payment Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>December</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2024-12-25</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>November</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2024-11-20</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>October</td>
                                                            <td>PKR 4,166</td>
                                                            <td><span class="badge badge-success">Full Paid</span></td>
                                                            <td>2024-10-18</td>
                                                            <td><a href="#" class="btn btn-xs btn-info">View</a></td>
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

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addPaymentForm">
                        <div class="form-group">
                            <label for="paymentMonth">Month</label>
                            <select class="form-control" id="paymentMonth" required>
                                <option value="">Select Month</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paymentYear">Year</label>
                            <select class="form-control" id="paymentYear" required>
                                <option value="">Select Year</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paymentAmount">Payment Amount (PKR)</label>
                            <input type="number" class="form-control" id="paymentAmount" placeholder="Enter payment amount" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="paymentStatus">Payment Status</label>
                            <select class="form-control" id="paymentStatus" required>
                                <option value="">Select Status</option>
                                <option value="Full Paid">Full Paid</option>
                                <option value="Half Paid">Half Paid</option>
                                <option value="Quarter Paid">Quarter Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paymentDate">Payment Date</label>
                            <input type="date" class="form-control" id="paymentDate" required>
                        </div>
                        <div class="form-group">
                            <label for="paymentNotes">Notes (Optional)</label>
                            <textarea class="form-control" id="paymentNotes" rows="3" placeholder="Add any notes about this payment"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePaymentBtn">Save Payment</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Payment Modal -->

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>

    <!-- Payment Form Script -->
    <script>
        document.getElementById('savePaymentBtn').addEventListener('click', function() {
            const form = document.getElementById('addPaymentForm');
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                // Collect form data
                const paymentData = {
                    month: document.getElementById('paymentMonth').value,
                    year: document.getElementById('paymentYear').value,
                    amount: document.getElementById('paymentAmount').value,
                    status: document.getElementById('paymentStatus').value,
                    date: document.getElementById('paymentDate').value,
                    notes: document.getElementById('paymentNotes').value
                };
                
                console.log('Payment Added:', paymentData);
                
                // Show success message
                alert('Payment has been recorded successfully!');
                
                // Reset form
                form.reset();
                // Close modal
                $('#addPaymentModal').modal('hide');
                
                // Here you can add API call to submit to backend
                // Example:
                // fetch('/api/payments/add', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify(paymentData)
                // })
                // .then(response => response.json())
                // .then(data => {
                //     console.log('Success:', data);
                //     // Refresh the page or update the table
                //     location.reload();
                // });
            }
        });
    </script>
    <!-- End Payment Form Script -->
