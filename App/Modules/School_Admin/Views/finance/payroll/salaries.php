<?php
/**
 * Staff Salaries UI (no sidebar)
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Staff Salaries - Payroll</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>

            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../../include/navbar.php'; ?>
            </header>

            <!-- main content only (no sidebar) -->
            <div class="app-main" id="main">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h3 class="mb-3">Staff Salaries</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb p-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                    <li class="breadcrumb-item"><a href="../finance.php">Finance</a></li>
                                    <li class="breadcrumb-item"><a href="../payroll.php">Payrolls</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staff Salaries</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <div>
                                <button id="btnAddSalary" class="btn btn-primary">Add Salary</button>
                                <button id="btnImport" class="btn btn-outline-secondary">Bulk Import</button>
                                <button id="btnExport" class="btn btn-outline-secondary">Export</button>
                            </div>
                            <div class="form-inline">
                                <label class="mr-2 text-muted">Filter:</label>
                                <select id="filterGrade" class="form-control mr-2">
                                    <option value="all">All Grades</option>
                                    <option value="grade1">Grade 1</option>
                                    <option value="grade2">Grade 2</option>
                                </select>
                                <input id="searchInput" type="search" class="form-control" placeholder="Search staff">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Salaries List</h5>
                                    <div class="table-responsive">
                                        <table id="salariesTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Staff ID</th>
                                                    <th>Name</th>
                                                    <th>Designation</th>
                                                    <th>Pay Grade</th>
                                                    <th>Basic Salary</th>
                                                    <th>Allowances</th>
                                                    <th>Deductions</th>
                                                    <th>Net Pay</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- sample rows to be replaced by dynamic data -->
                                                <tr>
                                                    <td>1</td>
                                                    <td>STF-001</td>
                                                    <td>John Doe</td>
                                                    <td>Teacher</td>
                                                    <td>Grade 2</td>
                                                    <td>₦120,000</td>
                                                    <td>₦20,000</td>
                                                    <td>₦5,000</td>
                                                    <td>₦135,000</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                        <button class="btn btn-sm btn-outline-secondary">View</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>STF-002</td>
                                                    <td>Jane Smith</td>
                                                    <td>Accountant</td>
                                                    <td>Grade 3</td>
                                                    <td>₦150,000</td>
                                                    <td>₦25,000</td>
                                                    <td>₦10,000</td>
                                                    <td>₦165,000</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                        <button class="btn btn-sm btn-outline-secondary">View</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-right">
                            <button class="btn btn-outline-dark">Save Changes</button>
                        </div>
                    </div>

                </div>
            </div>

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

        </div>
    </div>

    <script src="../../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../../public/assets/js/app.js"></script>
    <script>
        // Minimal client-side interactions for the UI
        document.getElementById('searchInput').addEventListener('input', function(e) {
            var q = e.target.value.toLowerCase();
            var rows = document.querySelectorAll('#salariesTable tbody tr');
            rows.forEach(function(r) {
                var text = r.innerText.toLowerCase();
                r.style.display = text.indexOf(q) > -1 ? '' : 'none';
            });
        });

        document.getElementById('filterGrade').addEventListener('change', function(e) {
            var val = e.target.value;
            var rows = document.querySelectorAll('#salariesTable tbody tr');
            if (val === 'all') { rows.forEach(r => r.style.display = ''); return; }
            rows.forEach(function(r) {
                var grade = r.cells[4].innerText.toLowerCase();
                r.style.display = grade.indexOf(val.replace('grade', 'grade ')) > -1 ? '' : 'none';
            });
        });
    </script>
</body>

</html>
