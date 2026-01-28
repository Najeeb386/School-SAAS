<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
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
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h4 class="mb-0">Staff Directory</h4>
                                            <div>
                                                <button class="btn btn-primary" data-toggle="modal" data-target="#addStaffModal">Add Staff</button>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="btn-group" role="group" aria-label="Staff filter">
                                                    <button type="button" class="btn btn-outline-secondary filter-btn active" data-role="all">All</button>
                                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-role="teacher">Teachers</button>
                                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-role="accountant">Accountant</button>
                                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-role="admin">Admin</button>
                                                    <button type="button" class="btn btn-outline-secondary filter-btn" data-role="other">Other</button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <input id="staffSearch" type="text" class="form-control" placeholder="Search by name or email">
                                                    <div class="input-group-append">
                                                        <button id="clearSearch" class="btn btn-outline-secondary" type="button">Clear</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover" id="staffTable">
                                                <thead>
                                                    <tr>
                                                        <th>Photo</th>
                                                        <th>Name</th>
                                                        <th>Role</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($staffs) && is_array($staffs) && count($staffs) > 0): ?>
                                                        <?php foreach ($staffs as $staff): ?>
                                                            <?php
                                                                $role = isset($staff['role']) ? strtolower($staff['role']) : 'other';
                                                                $name = isset($staff['name']) ? htmlspecialchars($staff['name']) : '-';
                                                                $email = isset($staff['email']) ? htmlspecialchars($staff['email']) : '-';
                                                                $phone = isset($staff['phone']) ? htmlspecialchars($staff['phone']) : '-';
                                                                $photoUrl = isset($staff['photo']) && $staff['photo'] !== '' ? $staff['photo'] : '../../../../../public/assets/img/avatar.png';
                                                            ?>
                                                            <tr data-role="<?= $role ?>">
                                                                <td style="width:60px;"><img src="<?= $photoUrl ?>" alt="photo" class="rounded" style="width:48px;height:48px;object-fit:cover;"></td>
                                                                <td><?= $name ?></td>
                                                                <td class="text-capitalize"><?= $role ?></td>
                                                                <td><?= $email ?></td>
                                                                <td><?= $phone ?></td>
                                                                <td>
                                                                    <a href="edit_staff.php?id=<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                                    <a href="delete_staff.php?id=<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this staff member?');">Delete</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">No staff found. Use the "Add Staff" button to create new staff records.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Staff Modal -->
                        <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="post" action="add_staff.php" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addStaffModalLabel">Add Staff</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="staffName">Full Name</label>
                                                <input id="staffName" name="name" type="text" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="staffEmail">Email</label>
                                                <input id="staffEmail" name="email" type="email" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="staffRole">Role</label>
                                                <select id="staffRole" name="role" class="form-control">
                                                    <option value="teacher">Teacher</option>
                                                    <option value="accountant">Accountant</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="staffPhone">Phone</label>
                                                <input id="staffPhone" name="phone" type="text" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label for="staffPhoto">Photo</label>
                                                <input id="staffPhoto" name="photo" type="file" class="form-control-file">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
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
    <script>
        (function() {
            function applyFilters() {
                var activeBtn = document.querySelector('.filter-btn.active');
                var role = activeBtn ? activeBtn.getAttribute('data-role') : 'all';
                var q = (document.getElementById('staffSearch').value || '').toLowerCase().trim();
                var rows = document.querySelectorAll('#staffTable tbody tr');
                rows.forEach(function(r) {
                    var rRole = r.getAttribute('data-role') || 'other';
                    var name = (r.children[1] && r.children[1].textContent || '').toLowerCase();
                    var email = (r.children[3] && r.children[3].textContent || '').toLowerCase();
                    var roleMatch = (role === 'all') || (rRole === role);
                    var searchMatch = q === '' || name.indexOf(q) !== -1 || email.indexOf(q) !== -1;
                    r.style.display = (roleMatch && searchMatch) ? '' : 'none';
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var filterButtons = document.querySelectorAll('.filter-btn');
                filterButtons.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        filterButtons.forEach(function(b){ b.classList.remove('active'); });
                        btn.classList.add('active');
                        applyFilters();
                    });
                });

                var searchInput = document.getElementById('staffSearch');
                if (searchInput) {
                    searchInput.addEventListener('input', function() { applyFilters(); });
                }

                var clearBtn = document.getElementById('clearSearch');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function() { document.getElementById('staffSearch').value = ''; applyFilters(); });
                }

                // Initial filter pass
                applyFilters();
            });
        })();
    </script>
</body>

</html>
