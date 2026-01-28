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
    <style>
        /* Fullscreen modal styles and dark text for readability */
        .modal.fullscreen .modal-dialog {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
        }
        .modal.fullscreen .modal-content {
            height: 100% !important;
            border-radius: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }
        .modal.fullscreen .modal-body {
            overflow: visible !important;
            color: #000 !important;
        }
        .modal.fullscreen .modal-header,
        .modal.fullscreen .modal-footer {
            background: #fff !important;
            color: #000 !important;
            border-color: #e9ecef;
        }
        /* Ensure labels and table text inside modal are readable */
        .modal.fullscreen label,
        .modal.fullscreen th,
        .modal.fullscreen td,
        .modal.fullscreen .custom-control-label {
            color: #000 !important;
        }
        /* Remove inner scrollbars and ensure table cells align */
        .modal.fullscreen .table-responsive { overflow-x: visible !important; overflow-y: visible !important; }
        .modal.fullscreen .table th, .modal.fullscreen .table td { vertical-align: middle !important; }
        .modal.fullscreen .table td.module-cell { text-align: left !important; }
        .modal.fullscreen .custom-control { display: inline-block; margin: 0; }
        .modal.fullscreen .custom-control-input { position: relative; top: 0; }
        /* Increase row height and center checkboxes cleanly */
        .modal.fullscreen .table tbody tr { height: 64px; }
        .modal.fullscreen .table td, .modal.fullscreen .table th { padding-top: 0.9rem; padding-bottom: 0.9rem; }
        .modal.fullscreen .table td .custom-control { height: 100%; display: flex; align-items: center; justify-content: center; }
        .modal.fullscreen .table td .custom-control-input { position: static; margin: 0; transform: none; }
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
                                            <h4 class="mb-0">Roles & Access Control</h4>
                                            <div>
                                                <button class="btn btn-primary" id="addRoleBtn" data-toggle="modal" data-target="#roleModal">Define New Role</button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover" id="rolesTable">
                                                <thead>
                                                    <tr>
                                                        <th>Role</th>
                                                        <th>Permissions</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                                                        <?php foreach ($roles as $r): ?>
                                                            <?php
                                                                $roleName = isset($r['name']) ? htmlspecialchars($r['name']) : 'Unnamed';
                                                                $roleId = isset($r['id']) ? intval($r['id']) : 0;
                                                                $permsRaw = isset($r['permissions']) ? $r['permissions'] : '';
                                                                $perms = [];
                                                                if (is_array($permsRaw)) {
                                                                    $perms = $permsRaw;
                                                                } elseif (is_string($permsRaw)) {
                                                                    $tryJson = json_decode($permsRaw, true);
                                                                    if (is_array($tryJson)) {
                                                                        $perms = $tryJson;
                                                                    } else {
                                                                        $perms = array_filter(array_map('trim', explode(',', $permsRaw)));
                                                                    }
                                                                }
                                                                $permsAttr = htmlspecialchars(json_encode(array_values($perms)), ENT_QUOTES, 'UTF-8');
                                                            ?>
                                                            <tr>
                                                                <td class="align-middle"><?= $roleName ?></td>
                                                                <td>
                                                                    <?php if (count($perms) > 0): ?>
                                                                        <?php foreach ($perms as $p): ?>
                                                                            <span class="badge badge-info mr-1 text-capitalize"><?= htmlspecialchars($p) ?></span>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No permissions</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-outline-primary edit-role" data-id="<?= $roleId ?>" data-name="<?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>" data-perms="<?= $permsAttr ?>">Edit</button>
                                                                    <a href="delete_role.php?id=<?= $roleId ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this role and its assignments?');">Delete</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No roles defined yet. Click "Define New Role" to create one.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Modal -->
                        <div class="modal fade fullscreen" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form id="roleForm" method="post" action="save_role.php">
                                        <input type="hidden" name="id" id="roleId" value="0">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="roleModalLabel">Define Role</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="roleName">Role Name</label>
                                                <input id="roleName" name="name" type="text" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Permissions</label>
                                                <div class="table-responsive">
                                                    <?php
                                                        $modules = [
                                                            'students' => [
                                                                'label' => 'Students',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'add_edit_terminate' => 'Add/Edit/Terminate'
                                                                ]
                                                            ],
                                                            'staff' => [
                                                                'label' => 'Staff',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'view_pay' => 'View-Pay',
                                                                    'pay' => 'Pay',
                                                                    'add_edit_delete' => 'Add/Edit/Delete'
                                                                ]
                                                            ],
                                                            'classes' => [
                                                                'label' => 'Classes',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'add_edit' => 'Add/Edit'
                                                                ]
                                                            ],
                                                            'attendance' => [
                                                                'label' => 'Attendance',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'print' => 'Print',
                                                                    'add' => 'Add',
                                                                    'edit' => 'Edit'
                                                                ]
                                                            ],
                                                            'ledger' => [
                                                                'label' => 'Ledger',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'add' => 'Add'
                                                                ]
                                                            ],
                                                            'diary' => [
                                                                'label' => 'Diary',
                                                                'actions' => [
                                                                    'full_control' => 'Full-Control',
                                                                    'view' => 'View',
                                                                    'add_edit' => 'Add/Edit'
                                                                ]
                                                            ]
                                                        ];

                                                        // build a table header with the widest action set across modules
                                                        $allActions = [];
                                                        foreach ($modules as $m) {
                                                            foreach (array_keys($m['actions']) as $a) { $allActions[$a] = true; }
                                                        }
                                                        $allActions = array_keys($allActions);
                                                    ?>
                                                    <table class="table table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th style="min-width:200px;">Module</th>
                                                                <?php foreach ($allActions as $aKey): ?>
                                                                    <th class="text-center text-nowrap"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $aKey))) ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($modules as $mKey => $m): ?>
                                                                <tr>
                                                                    <td class="module-cell align-middle"><?= htmlspecialchars($m['label']) ?></td>
                                                                    <?php foreach ($allActions as $aKey):
                                                                        $has = isset($m['actions'][$aKey]);
                                                                        $inputId = 'perm_' . $mKey . '_' . $aKey;
                                                                        $value = $mKey . ':' . $aKey;
                                                                    ?>
                                                                        <td class="text-center align-middle">
                                                                            <?php if ($has): ?>
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox" class="custom-control-input perm-checkbox" id="<?= $inputId ?>" name="permissions[]" value="<?= $value ?>">
                                                                                    <label class="custom-control-label" for="<?= $inputId ?>"></label>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Role</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            var addBtn = document.getElementById('addRoleBtn');
            var roleModalLabel = document.getElementById('roleModalLabel');
            var roleIdInput = document.getElementById('roleId');
            var roleNameInput = document.getElementById('roleName');
            var permCheckboxes = document.querySelectorAll('.perm-checkbox');

            function clearForm() {
                roleIdInput.value = 0;
                roleNameInput.value = '';
                permCheckboxes.forEach(function(cb) { cb.checked = false; });
                if (roleModalLabel) roleModalLabel.textContent = 'Define Role';
            }

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    clearForm();
                });
            }

            var editButtons = document.querySelectorAll('.edit-role');
            editButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = btn.getAttribute('data-id') || 0;
                    var name = btn.getAttribute('data-name') || '';
                    var perms = btn.getAttribute('data-perms') || '[]';
                    try { perms = JSON.parse(perms); } catch(e) { perms = []; }

                    roleIdInput.value = id;
                    roleNameInput.value = name;
                    permCheckboxes.forEach(function(cb) { cb.checked = perms.indexOf(cb.value) !== -1; });
                    if (roleModalLabel) roleModalLabel.textContent = 'Edit Role';

                    // show modal (use jQuery/Bootstrap if available)
                    if (window.jQuery && typeof jQuery('#roleModal').modal === 'function') {
                        jQuery('#roleModal').modal('show');
                    } else {
                        // fallback: set attribute to open via Bootstrap data API
                        var modal = document.getElementById('roleModal');
                        if (modal) modal.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>

</html>
