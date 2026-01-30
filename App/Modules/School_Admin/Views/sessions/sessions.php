<?php
/**
 * Sessions management view — wraps the sessions UI in the standard admin layout
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Config/connection.php';
require_once __DIR__ . '/../../Controllers/SessionController.php';
require_once __DIR__ . '/../../Models/SessionModel.php';

use App\Modules\School_Admin\Controllers\SessionController;

$school_id = isset($school_id) ? $school_id : (isset($school) && isset($school->id) ? $school->id : '');

$sessCtrl = new SessionController($DB_con);
$sessions = $sessCtrl->list();

$editing = false;
$editRecord = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    if ($eid > 0) {
        $editRecord = $sessCtrl->get($eid);
        if ($editRecord) $editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Academic Sessions - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Manage academic sessions" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>

            <header class="app-header top-bar">
                <?php include_once __DIR__ . '/../../include/navbar.php'; ?>
            </header>

            <div class="app-container">
                <?php include_once __DIR__ . '/../../include/sidebar.php'; ?>

                <div class="app-main" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Academic Sessions</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Sessions</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <form method="post" id="sessionForm" action="save_session.php">
                                            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
                                            <?php if ($editing && !empty($editRecord['id'])): ?>
                                                <input type="hidden" name="id" value="<?php echo intval($editRecord['id']); ?>">
                                            <?php endif; ?>

                                            <div class="form-group">
                                                <label for="name">Session Name</label>
                                                <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. 2025/2026" value="<?php echo $editing ? htmlspecialchars($editRecord['name']) : ''; ?>">
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="start_date">Start Date</label>
                                                    <input type="date" name="start_date" id="start_date" class="form-control" required value="<?php echo $editing ? htmlspecialchars($editRecord['start_date']) : ''; ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="end_date">End Date</label>
                                                    <input type="date" name="end_date" id="end_date" class="form-control" required value="<?php echo $editing ? htmlspecialchars($editRecord['end_date']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group form-check">
                                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?php echo ($editing && !empty($editRecord['is_active'])) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_active">Set as active</label>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update Session' : 'Save Session'; ?></button>
                                                <button type="reset" class="btn btn-light">Reset</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Existing Sessions</h5>
                                        <?php if (!empty($sessions) && is_array($sessions)) : ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Start</th>
                                                            <th>End</th>
                                                            <th>Active</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $i = 1; foreach ($sessions as $sess) : ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo htmlspecialchars(isset($sess->name) ? $sess->name : (is_array($sess) && isset($sess['name']) ? $sess['name'] : '')); ?></td>
                                                                <td><?php echo htmlspecialchars(isset($sess->start_date) ? $sess->start_date : (is_array($sess) && isset($sess['start_date']) ? $sess['start_date'] : '')); ?></td>
                                                                <td><?php echo htmlspecialchars(isset($sess->end_date) ? $sess->end_date : (is_array($sess) && isset($sess['end_date']) ? $sess['end_date'] : '')); ?></td>
                                                                <td><?php echo (!empty($sess->is_active) || (!is_object($sess) && !empty($sess['is_active']))) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'; ?></td>
                                                                <td>
                                                                    <?php $sid = isset($sess->id) ? $sess->id : $sess['id'];
                                                                          $sname = isset($sess->name) ? $sess->name : (is_array($sess) && isset($sess['name']) ? $sess['name'] : '');
                                                                          $sstart = isset($sess->start_date) ? $sess->start_date : (is_array($sess) && isset($sess['start_date']) ? $sess['start_date'] : '');
                                                                          $send = isset($sess->end_date) ? $sess->end_date : (is_array($sess) && isset($sess['end_date']) ? $sess['end_date'] : '');
                                                                          $sactive = (!empty($sess->is_active) || (!is_object($sess) && !empty($sess['is_active']))) ? 1 : 0;
                                                                    ?>
                                                                    <button type="button" class="btn btn-sm btn-primary btn-edit-session"
                                                                        data-id="<?php echo $sid; ?>"
                                                                        data-name="<?php echo htmlspecialchars($sname, ENT_QUOTES); ?>"
                                                                        data-start="<?php echo $sstart; ?>"
                                                                        data-end="<?php echo $send; ?>"
                                                                        data-active="<?php echo $sactive; ?>">
                                                                        Edit
                                                                    </button>
                                                                    <a href="delete_session.php?id=<?php echo $sid; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete session?')">Delete</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else : ?>
                                            <p class="mb-0">No sessions yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
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

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
        <!-- Edit session modal -->
        <div class="modal fade" id="sessionEditModal" tabindex="-1" role="dialog" aria-labelledby="sessionEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sessionEditModalLabel">Edit Session</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="post" action="save_session.php" id="sessionEditForm">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="modal_session_id">
                            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">

                            <div class="form-group">
                                <label for="modal_name">Session Name</label>
                                <input type="text" class="form-control" id="modal_name" name="name" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="modal_start">Start Date</label>
                                    <input type="date" class="form-control" id="modal_start" name="start_date" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="modal_end">End Date</label>
                                    <input type="date" class="form-control" id="modal_end" name="end_date" required>
                                </div>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="modal_active" name="is_active" value="1">
                                <label class="form-check-label" for="modal_active">Set as active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
                (function(){
                        // when edit buttons are clicked, populate modal and show it
                        function showModal() {
                                var btns = document.querySelectorAll('.btn-edit-session');
                                if (!btns) return;
                                btns.forEach(function(b){
                                        b.addEventListener('click', function(e){
                                                var id = this.getAttribute('data-id');
                                                var name = this.getAttribute('data-name');
                                                var start = this.getAttribute('data-start');
                                                var end = this.getAttribute('data-end');
                                                var active = this.getAttribute('data-active');

                                                document.getElementById('modal_session_id').value = id;
                                                document.getElementById('modal_name').value = name;
                                                document.getElementById('modal_start').value = start;
                                                document.getElementById('modal_end').value = end;
                                                document.getElementById('modal_active').checked = (active == '1');

                                                // show bootstrap modal (support jQuery or bootstrap 5 APIs)
                                                try {
                                                        if (window.jQuery && typeof jQuery('#sessionEditModal').modal === 'function') {
                                                                jQuery('#sessionEditModal').modal('show');
                                                        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                                                var m = new bootstrap.Modal(document.getElementById('sessionEditModal'));
                                                                m.show();
                                                        } else {
                                                                // fallback: display block
                                                                document.getElementById('sessionEditModal').style.display = 'block';
                                                        }
                                                } catch(err) { console.error(err); }
                                        });
                                });
                        }

                        document.addEventListener('DOMContentLoaded', function(){
                                showModal();
                        });
                })();
        </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
            }, 300);
        });
    </script>
</body>

</html>
