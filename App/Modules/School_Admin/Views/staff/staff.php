<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
// load DB and teacher controller/model
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Controllers/TeacherController.php';
require_once __DIR__ . '/../../Models/TeacherModel.php';
use App\Modules\School_Admin\Controllers\TeacherController;

$teacherCtrl = new TeacherController($DB_con);
$staffs = $teacherCtrl->list();
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
    <!-- DataTables CSS (CDN) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        /* Creative modal header */
        .staff-modal-header { background: linear-gradient(90deg,#4e73df,#1cc88a); color:#fff; }
        .staff-avatar-preview { width:84px; height:84px; border-radius:8px; object-fit:cover; border:2px solid #fff; }
        .dt-search-wrapper { display:flex; gap:0.5rem; align-items:center; }
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
                                            <div class="col-md-6 dt-search-wrapper">
                                                <input id="staffSearch" type="text" class="form-control" placeholder="Search by name or email">
                                                <button id="clearSearch" class="btn btn-outline-secondary" type="button">Clear</button>
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
                                                                // determine photo path (model stores in 'photo_path')
                                                                $photoUrl = '../../../../../public/assets/img/avatar.png';
                                                                $candidate = null;
                                                                if (isset($staff['photo_path']) && !empty($staff['photo_path'])) {
                                                                    $candidate = $staff['photo_path'];
                                                                } elseif (isset($staff['photo']) && !empty($staff['photo'])) {
                                                                    $candidate = $staff['photo'];
                                                                }
                                                                if ($candidate) {
                                                                    // if candidate is a relative project path like 'Storage/..', prefix with proper upward path
                                                                    if (strpos($candidate, 'Storage/') === 0) {
                                                                        $rel = '../../../../../' . $candidate;
                                                                    } else {
                                                                        $rel = $candidate;
                                                                    }
                                                                    // verify file exists on disk before exposing URL
                                                                    $fsPath = realpath(__DIR__ . '/../../../../..') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($candidate, '/'));
                                                                    if ($fsPath && file_exists($fsPath)) {
                                                                        $photoUrl = $rel;
                                                                    } else {
                                                                        // fallback: try the prefixed path anyway
                                                                        $photoUrl = $rel;
                                                                    }
                                                                }
                                                            ?>
                                                            <tr data-role="<?= $role ?>">
                                                                <td style="width:60px;"><img src="<?= $photoUrl ?>" alt="photo" class="rounded" style="width:48px;height:48px;object-fit:cover;"></td>
                                                                <td><?= $name ?></td>
                                                                <td class="text-capitalize"><?= $role ?></td>
                                                                <td><?= $email ?></td>
                                                                <td><?= $phone ?></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                                                                        data-id="<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>"
                                                                        data-name="<?= isset($staff['name']) ? htmlspecialchars($staff['name'], ENT_QUOTES) : '' ?>"
                                                                        data-email="<?= isset($staff['email']) ? htmlspecialchars($staff['email'], ENT_QUOTES) : '' ?>"
                                                                        data-phone="<?= isset($staff['phone']) ? htmlspecialchars($staff['phone'], ENT_QUOTES) : '' ?>"
                                                                        data-idno="<?= isset($staff['id_no']) ? htmlspecialchars($staff['id_no'], ENT_QUOTES) : '' ?>"
                                                                        data-role="<?= isset($staff['role']) ? htmlspecialchars($staff['role'], ENT_QUOTES) : 'teacher' ?>"
                                                                        data-photo="<?= isset($photoUrl) ? htmlspecialchars($photoUrl, ENT_QUOTES) : '' ?>"
                                                                    >Edit</button>
                                                                    <a href="delete_staff.php?id=<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this staff member?');">Delete</a>
                                                                    <a href="view_finances.php?staff_id=<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>" class="btn btn-sm btn-outline-success ml-1">View Finances</a>
                                                                    <a href="assign_classes.php?staff_id=<?= isset($staff['id']) ? intval($staff['id']) : 0 ?>" class="btn btn-sm btn-outline-info ml-1">Assign Classes</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td class="text-center" colspan="1">No staff found.</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-center">Use the "Add Staff" button to create new staff records.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- Edit Staff Modal -->
                            <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="editStaffModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <form method="post" action="edit_staff.php" enctype="multipart/form-data">
                                            <input type="hidden" name="id" id="editStaffId" value="">
                                            <div class="modal-header staff-modal-header">
                                                <div class="d-flex align-items-center">
                                                    <img id="editPreviewAvatar" src="../../../../../public/assets/img/avatar.png" alt="avatar" class="staff-avatar-preview mr-3">
                                                    <div>
                                                        <h5 class="modal-title" id="editStaffModalLabel">Edit Staff</h5>
                                                        <div class="small">Update teacher details and optionally upload a new picture.</div>
                                                    </div>
                                                </div>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <div class="form-group">
                                                            <label for="editStaffName">Full Name</label>
                                                            <input id="editStaffName" name="name" type="text" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="editStaffEmail">Email</label>
                                                            <input id="editStaffEmail" name="email" type="email" class="form-control">
                                                        </div>
                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label for="editStaffPhone">Phone</label>
                                                                <input id="editStaffPhone" name="phone" type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="editStaffIdNo">NIC / Passport (optional)</label>
                                                                <input id="editStaffIdNo" name="id_no" type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="editStaffRole">Role</label>
                                                            <select id="editStaffRole" name="role" class="form-control">
                                                                <option value="teacher">Teacher</option>
                                                                <option value="accountant">Accountant</option>
                                                                <option value="admin">Admin</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group" id="editOtherRoleWrap" style="display:none;">
                                                            <label for="editStaffRoleOther">Specify role</label>
                                                            <input id="editStaffRoleOther" name="role_other" type="text" class="form-control" placeholder="Enter role name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label for="editStaffPhoto">Photo</label>
                                                            <input id="editStaffPhoto" name="photo" type="file" accept="image/*" class="form-control-file">
                                                            <div id="editPhotoName" class="small text-muted mt-2" style="display:none;"></div>
                                                        </div>
                                                        <div class="mt-3 text-muted small">Accepted formats: JPG, PNG. Max 2MB recommended.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-light">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <!-- Add Staff Modal (Creative) -->
                        <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form method="post" action="add_staff.php" enctype="multipart/form-data">
                                        <div class="modal-header staff-modal-header">
                                            <div class="d-flex align-items-center">
                                                <img id="previewAvatar" src="../../../../../public/assets/img/avatar.png" alt="avatar" class="staff-avatar-preview mr-3">
                                                <div>
                                                    <h5 class="modal-title" id="addStaffModalLabel">Add Teacher</h5>
                                                    <div class="small">Create a new teacher account — fill required fields and upload a picture.</div>
                                                </div>
                                            </div>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label for="staffName">Full Name</label>
                                                        <input id="staffName" name="name" type="text" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="staffEmail">Email</label>
                                                        <input id="staffEmail" name="email" type="email" class="form-control">
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label for="staffPhone">Phone</label>
                                                            <input id="staffPhone" name="phone" type="text" class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="staffIdNo">NIC / Passport (optional)</label>
                                                            <input id="staffIdNo" name="id_no" type="text" class="form-control">
                                                        </div>
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
                                                    <div class="form-group" id="otherRoleWrap" style="display:none;">
                                                        <label for="staffRoleOther">Specify role</label>
                                                        <input id="staffRoleOther" name="role_other" type="text" class="form-control" placeholder="Enter role name">
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="staffPhoto">Photo</label>
                                                        <input id="staffPhoto" name="photo" type="file" accept="image/*" class="form-control-file">
                                                        <div id="photoName" class="small text-muted mt-2" style="display:none;"></div>
                                                    </div>
                                                    <div class="mt-3 text-muted small">Accepted formats: JPG, PNG. Max 2MB recommended.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-light">Save Teacher</button>
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
    <!-- DataTables JS (CDN) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
        // Initialize DataTable and live search
        (function(){
            var tableEl = document.getElementById('staffTable');
            if (tableEl && window.jQuery && typeof jQuery.fn.dataTable === 'function') {
                var dt = jQuery(tableEl).DataTable({
                    pageLength: 10,
                    responsive: true,
                    destroy: true,
                    autoWidth: false,
                    columnDefs: [{ orderable: false, targets: [0,5] }]
                });

                var searchInput = document.getElementById('staffSearch');
                var clearBtn = document.getElementById('clearSearch');
                if (searchInput) {
                    searchInput.addEventListener('input', function(){ dt.search(this.value).draw(); });
                }
                if (clearBtn) {
                    clearBtn.addEventListener('click', function(){ if (searchInput) { searchInput.value=''; dt.search('').draw(); } });
                }
            }

            // toggle other role input
            var staffRole = document.getElementById('staffRole');
            var otherWrap = document.getElementById('otherRoleWrap');
            var staffRoleOther = document.getElementById('staffRoleOther');
            if (staffRole) {
                staffRole.addEventListener('change', function(){
                    if (this.value === 'other') { otherWrap.style.display = 'block'; } else { otherWrap.style.display = 'none'; staffRoleOther.value=''; }
                });
            }

            // Photo preview handling (simple file input)
            var photoInput = document.getElementById('staffPhoto');
            var preview = document.getElementById('previewAvatar');
            var photoNameEl = document.getElementById('photoName');
            var defaultAvatar = '../../../../../public/assets/img/avatar.png';

            function previewFile(file) {
                if (!file) {
                    if (preview) preview.src = defaultAvatar;
                    if (photoNameEl) photoNameEl.style.display = 'none';
                    return;
                }
                if (file.size && file.size > 2 * 1024 * 1024) {
                    alert('Selected file is larger than 2MB. Please choose a smaller image.');
                    return;
                }
                if (photoNameEl) {
                    photoNameEl.textContent = file.name;
                    photoNameEl.style.display = 'block';
                }
                if (preview) {
                    var reader = new FileReader();
                    reader.onload = function(ev){ preview.src = ev.target.result; };
                    reader.readAsDataURL(file);
                }
            }

            if (photoInput) {
                photoInput.addEventListener('change', function(e){
                    var f = e.target.files && e.target.files[0];
                    previewFile(f);
                });
            }
            // Edit button handler: populate edit modal
            function bindEditButtons() {
                var editButtons = document.querySelectorAll('.btn-edit');
                var modal = document.getElementById('editStaffModal');
                if (!editButtons || !modal) return;
                editButtons.forEach(function(btn){
                    btn.addEventListener('click', function(){
                        var id = this.getAttribute('data-id');
                        var name = this.getAttribute('data-name') || '';
                        var email = this.getAttribute('data-email') || '';
                        var phone = this.getAttribute('data-phone') || '';
                        var idno = this.getAttribute('data-idno') || '';
                        var role = this.getAttribute('data-role') || 'teacher';
                        var photo = this.getAttribute('data-photo') || '../../../../../public/assets/img/avatar.png';

                        document.getElementById('editStaffId').value = id;
                        document.getElementById('editStaffName').value = name;
                        document.getElementById('editStaffEmail').value = email;
                        document.getElementById('editStaffPhone').value = phone;
                        document.getElementById('editStaffIdNo').value = idno;
                        var roleSel = document.getElementById('editStaffRole');
                        if (roleSel) roleSel.value = role;
                        var otherWrap = document.getElementById('editOtherRoleWrap');
                        var roleOther = document.getElementById('editStaffRoleOther');
                        if (role === 'other') { otherWrap.style.display = 'block'; if (roleOther) roleOther.value = ''; } else { otherWrap.style.display = 'none'; if (roleOther) roleOther.value = ''; }

                        var preview = document.getElementById('editPreviewAvatar');
                        if (preview) preview.src = photo;
                        // reset file input label
                        var fileInput = document.getElementById('editStaffPhoto');
                        var photoNameEl = document.getElementById('editPhotoName');
                        if (fileInput) fileInput.value = null;
                        if (photoNameEl) { photoNameEl.style.display = 'none'; photoNameEl.textContent = ''; }

                        // show modal using jQuery/Bootstrap
                        if (window.jQuery) jQuery('#editStaffModal').modal('show');
                    });
                });
            }
            bindEditButtons();
        })();
    </script>
    
</body>

</html>
