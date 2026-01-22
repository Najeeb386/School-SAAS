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

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#usage">Usage & Limits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#billing">Billing</a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>School Overview</h5>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editSchoolModal">
                        Edit
                    </button>
                </div>

                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th>School Name</th><td>ABC School</td></tr>
                        <tr><th>Subdomain</th><td>abc.yoursaas.com</td></tr>
                        <tr><th>Plan</th><td>Basic</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-success">Active</span></td></tr>
                        <tr><th>Contact Email</th><td>admin@abcschool.com</td></tr>
                        <tr><th>Created At</th><td>10-Jan-2026</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= USAGE & LIMITS ================= -->
        <div class="tab-pane fade" id="usage">
            <div class="row">

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Students</h6>
                            <h4>450 / 500</h4>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width:90%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Teachers</h6>
                            <h4>25 / 30</h4>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width:83%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Storage</h6>
                            <h4>1.2GB / 2GB</h4>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width:60%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-right mt-3">
                <button class="btn btn-success">
                    Upgrade Plan
                </button>
            </div>
        </div>

        <!-- ================= BILLING ================= -->
        <div class="tab-pane fade" id="billing">
            <div class="card">
                <div class="card-header">
                    <h5>Billing Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr><th>Current Plan</th><td>Basic</td></tr>
                        <tr><th>Amount</th><td>PKR 180 / Student / Year</td></tr>
                        <tr><th>Last payment date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Upcoming payment Date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Due Date</th><td>25-Jan-2026</td></tr>
                        <tr><th>Payment Status</th><td><span class="badge badge-warning">Due</span></td></tr>
                    </table>

                    <div class="text-right">
                        <button class="btn btn-primary btn-sm">Mark as Paid</button>
                        <button class="btn btn-danger btn-sm">Suspend Portal</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- modals -->
 <div class="modal fade" id="editSchoolModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit School</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" class="form-control" value="ABC School">
                    </div>

                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" class="form-control" value="admin@abcschool.com">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control">
                            <option selected>Active</option>
                            <option>Suspended</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Changes</button>
            </div>

        </div>
    </div>
</div>

 <!-- modals -->

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

    <!-- plugins -->
    <script src="../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../public/assets/js/app.js"></script>
</body>


</html>