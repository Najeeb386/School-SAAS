<?php
/**
 * Employee management (renamed from roles)
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';

// Load employees from DB if not provided by controller
if (!isset($employees)) {
    require_once __DIR__ . '/../../../../Config/connection.php';
    try {
        $stmt = $DB_con->query('SELECT id, name, email, role_id, permissions FROM employees ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $employees = [];
        foreach ($rows as $r) {
            $perms = [];
            if (!empty($r['permissions'])) {
                $tmp = json_decode($r['permissions'], true);
                if (is_array($tmp)) $perms = $tmp;
            }
            $employees[] = ['id' => $r['id'], 'name' => $r['name'], 'email' => $r['email'], 'role_id' => $r['role_id'], 'permissions' => $perms];
        }
    } catch (Exception $e) {
        $employees = [];
    }
}

// Load roles for the role select
require_once __DIR__ . '/../../../../Config/connection.php';
$rolesList = [];
try {
    $stmt = $DB_con->query('SELECT id, name FROM roles ORDER BY name ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) $rolesList[intval($r['id'])] = $r['name'];
} catch (Exception $e) { $rolesList = []; }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Employees</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <style>
        /* Wide centered modal (not fullscreen) and dark text for readability */
        .modal.fullscreen .modal-dialog {
            width: 95% !important;
            max-width: 1200px !important;
            height: auto !important;
            margin: 30px auto !important;
            padding: 0 !important;
        }
        .modal.fullscreen .modal-content {
            height: auto !important;
            border-radius: 4px !important;
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
        /* Increase row height and center checkboxes */
        .modal.fullscreen .table tbody tr { height: 64px; }
        .modal.fullscreen .table td, .modal.fullscreen .table th { padding-top: 0.9rem; padding-bottom: 0.9rem; }
        .modal.fullscreen .table td .custom-control { height: 100%; display: flex; align-items: center; justify-content: center; }
        .modal.fullscreen .table td .custom-control-input { position: static; margin: 0; transform: none; }
        /* Visual styling for actions not available for a module */
        .modal.fullscreen .not-applicable { opacity: 0.45; pointer-events: none; }
        .modal.fullscreen .not-applicable + .custom-control-label { cursor: not-allowed; }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>
            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>
                <div class="app-main" id="main">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h4 class="mb-0">Employees</h4>
                                            <div>
                                                <button class="btn btn-primary" id="addEmployeeBtn" data-toggle="modal" data-target="#employeeModal">Add Employee</button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover" id="employeesTable">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Permissions</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($employees) && is_array($employees) && count($employees) > 0): ?>
                                                        <?php foreach ($employees as $e): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($e['name']) ?></td>
                                                                <td><?= htmlspecialchars($e['email']) ?></td>
                                                                <td><?= isset($rolesList[intval($e['role_id'])]) ? htmlspecialchars($rolesList[intval($e['role_id'])]) : '-' ?></td>
                                                                <td>
                                                                    <?php if (!empty($e['permissions'])): foreach ($e['permissions'] as $p): ?>
                                                                        <span class="badge badge-info mr-1 text-capitalize"><?= htmlspecialchars($p) ?></span>
                                                                    <?php endforeach; else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-outline-primary edit-employee" data-id="<?= $e['id'] ?>" data-name="<?= htmlspecialchars($e['name'], ENT_QUOTES,'UTF-8') ?>" data-email="<?= htmlspecialchars($e['email'], ENT_QUOTES,'UTF-8') ?>" data-role="<?= intval($e['role_id']) ?>" data-perms="<?= htmlspecialchars(json_encode($e['permissions']), ENT_QUOTES,'UTF-8') ?>">Edit</button>
                                                                    <a href="delete_employee.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this employee?');">Delete</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="5" class="text-center">No employees found.</td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Modal -->
                        <div class="modal fade fullscreen" id="employeeModal" tabindex="-1" role="dialog" aria-labelledby="employeeModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form id="employeeForm" method="post" action="save_employee.php">
                                        <input type="hidden" name="id" id="employeeId" value="0">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-row no-gutters" style="align-items:center;">
                                                <div class="form-group col-md-3 pr-2">
                                                    <label for="empName">Name</label>
                                                    <input id="empName" name="name" type="text" class="form-control" required>
                                                </div>
                                                <div class="form-group col-md-3 px-2">
                                                    <label for="empEmail">Email</label>
                                                    <input id="empEmail" name="email" type="email" class="form-control">
                                                </div>
                                                <div class="form-group col-md-3 px-2">
                                                    <label for="empPassword">Password</label>
                                                    <input id="empPassword" name="password" type="password" class="form-control" placeholder="Leave blank to keep existing">
                                                </div>
                                                <div class="form-group col-md-3 pl-2">
                                                    <label for="empRole">Role</label>
                                                    <select id="empRole" name="role_id" class="form-control">
                                                        <option value="0">-- Select Role --</option>
                                                        <?php foreach ($rolesList as $rid => $rname): ?>
                                                            <option value="<?= $rid ?>"><?= htmlspecialchars($rname) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <hr>
                                            <label>Permissions</label>
                                            <div class="table-responsive">
                                                <?php
                                                    // Define modules and their actions as requested
                                                    $modules = [
                                                        'students' => ['label'=>'Students','actions'=>['full_control'=>'Full-Control','view'=>'View','add_edit_terminate'=>'Add/Edit/Terminate']],
                                                        'staff' => ['label'=>'Staff','actions'=>['full_control'=>'Full-Control','view'=>'View','view_pay'=>'View-Pay','pay'=>'Pay','add_edit_delete'=>'Add/Edit/Delete']],
                                                        'classes' => ['label'=>'Classes','actions'=>['full_control'=>'Full-Control','view'=>'View','add_edit'=>'Add/Edit']],
                                                        'attendance' => ['label'=>'Attendance','actions'=>['full_control'=>'Full-Control','view'=>'View','print'=>'Print','add'=>'Add','edit'=>'Edit']],
                                                        'finance' => ['label'=>'Finance','actions'=>['full_control'=>'Full-Control','view'=>'View','add'=>'Add','edit'=>'Edit','pay'=>'Pay']],
                                                        'fees' => ['label'=>'Fees','actions'=>['full_control'=>'Full-Control','view'=>'View','add'=>'Add','edit'=>'Edit','pay'=>'Pay']],
                                                        'reports' => ['label'=>'Reports','actions'=>['full_control'=>'Full-Control','view'=>'View','export'=>'Export']],
                                                        'exams' => ['label'=>'Exams','actions'=>['full_control'=>'Full-Control','view'=>'View','add_edit'=>'Add/Edit']],
                                                        'announcements' => ['label'=>'Announcements','actions'=>['full_control'=>'Full-Control','view'=>'View','add_edit_delete'=>'Add/Edit/Delete']],
                                                    ];
                                                    $allActions = [];
                                                    foreach ($modules as $m) foreach (array_keys($m['actions']) as $a) $allActions[$a]=true;
                                                    $allActions = array_keys($allActions);
                                                ?>
                                                <table class="table table-bordered table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:200px;">Module</th>
                                                            <?php foreach ($allActions as $aKey): ?>
                                                                <th class="text-center text-nowrap"><?= htmlspecialchars(ucwords(str_replace('_',' ',$aKey))) ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($modules as $mKey => $m): ?>
                                                            <tr>
                                                                <td class="module-cell align-middle"><?= htmlspecialchars($m['label']) ?></td>
                                                                <?php foreach ($allActions as $aKey):
                                                                    $has = isset($m['actions'][$aKey]);
                                                                    $inputId = 'empperm_' . $mKey . '_' . $aKey;
                                                                    $value = $mKey . ':' . $aKey;
                                                                ?>
                                                                    <td class="text-center align-middle">
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input emp-perm-checkbox <?= $has ? '' : 'not-applicable' ?>" id="<?= $inputId ?>" name="permissions[]" value="<?= $value ?>" <?= $has ? '' : 'disabled' ?> <?= $has ? '' : 'title="Not applicable for this module"' ?>>
                                                                            <label class="custom-control-label" for="<?= $inputId ?>"></label>
                                                                        </div>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Employee</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-left"><p>&copy; Copyright 2019. All rights reserved.</p></div>
                    <div class="col col-sm-6 ml-sm-auto text-center text-sm-right"><p><a target="_blank" href="https://www.templateshub.net">Templates Hub</a></p></div>
                </div>
            </footer>
        </div>
    </div>

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var addBtn = document.getElementById('addEmployeeBtn');
        var empModalLabel = document.getElementById('employeeModalLabel');
        var empId = document.getElementById('employeeId');
        var empName = document.getElementById('empName');
        var empEmail = document.getElementById('empEmail');
        var empRole = document.getElementById('empRole');
        var permCheckboxes = document.querySelectorAll('.emp-perm-checkbox');

        function clearForm(){ empId.value=0; empName.value=''; empEmail.value=''; empRole.value=0; permCheckboxes.forEach(cb=>cb.checked=false); if(empModalLabel) empModalLabel.textContent='Add Employee'; }
        if(addBtn) addBtn.addEventListener('click', clearForm);

        var editBtns = document.querySelectorAll('.edit-employee');
        editBtns.forEach(function(btn){ btn.addEventListener('click', function(){ var id=btn.getAttribute('data-id'); var name=btn.getAttribute('data-name'); var email=btn.getAttribute('data-email'); var role=btn.getAttribute('data-role')||0; var perms=btn.getAttribute('data-perms')||'[]'; try{perms=JSON.parse(perms);}catch(e){perms=[];} empId.value=id; empName.value=name; empEmail.value=email; empRole.value=role; permCheckboxes.forEach(cb=>{cb.checked = perms.indexOf(cb.value)!==-1}); if(empModalLabel) empModalLabel.textContent='Edit Employee'; if(window.jQuery && typeof jQuery('#employeeModal').modal==='function') jQuery('#employeeModal').modal('show'); }); });
    });
    </script>
</body>
</html>
