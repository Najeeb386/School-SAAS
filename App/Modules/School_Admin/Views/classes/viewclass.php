<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';

$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$school_id = $_SESSION['school_id'] ?? null;
$active_session_id = $_SESSION['active_session_id'] ?? null;
if (!$class_id || !$school_id) {
    echo 'Invalid request';
    exit;
}

try {
    $db = \Database::connect();
    // Fetch class
    $stmt = $db->prepare('SELECT * FROM school_classes WHERE id = :id AND school_id = :sid LIMIT 1');
    $stmt->execute([':id' => $class_id, ':sid' => $school_id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$class) {
        echo 'Class not found';
        exit;
    }

    // Fetch sections
    $secStmt = $db->prepare('SELECT * FROM school_class_sections WHERE class_id = :cid AND school_id = :sid');
    $secStmt->execute([':cid' => $class_id, ':sid' => $school_id]);
    $sections = $secStmt->fetchAll(PDO::FETCH_ASSOC);

    // Try to fetch subjects (best-effort)
    $subjects = [];
    try {
        // Only load subjects that are assigned to this class (via school_subject_assignments)
        $subSql = 'SELECT DISTINCT s.*, t.name AS teacher_name FROM school_subjects s '
            . 'INNER JOIN school_subject_assignments a ON a.subject_id = s.id AND a.class_id = :cid '
            . 'LEFT JOIN school_teachers t ON s.teacher_id = t.id '
            . 'WHERE s.school_id = :sid';
        $subStmt = $db->prepare($subSql);
        $subStmt->execute([':sid' => $school_id, ':cid' => $class_id]);
        $subjects = $subStmt->fetchAll(PDO::FETCH_ASSOC);
        // fetch assignments for this class so we can show which sections each subject is assigned to
        $assignSql = 'SELECT a.*, sec.section_name, t.name AS assign_teacher_name FROM school_subject_assignments a LEFT JOIN school_class_sections sec ON a.section_id = sec.id LEFT JOIN school_teachers t ON a.teacher_id = t.id WHERE a.school_id = :sid AND a.class_id = :cid';
        $assignStmt = $db->prepare($assignSql);
        $assignStmt->execute([':sid' => $school_id, ':cid' => $class_id]);
        $assignRows = $assignStmt->fetchAll(PDO::FETCH_ASSOC);
        $subjectAssignments = [];
        foreach ($assignRows as $ar) {
            $sid = (int)($ar['subject_id'] ?? 0);
            if (!isset($subjectAssignments[$sid])) $subjectAssignments[$sid] = ['sections' => [], 'teachers' => []];
            if (empty($ar['section_id'])) {
                // null section means assigned to all sections
                $subjectAssignments[$sid]['sections'][] = 'All sections';
            } else {
                $subjectAssignments[$sid]['sections'][] = $ar['section_name'] ?? ('#'.$ar['section_id']);
            }
            if (!empty($ar['assign_teacher_name'])) $subjectAssignments[$sid]['teachers'][] = $ar['assign_teacher_name'];
        }
    } catch (Throwable $e) {
        // table may not exist - ignore
        $subjects = null;
    }

} catch (Throwable $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit;
}
?><!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Class</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background:#f6f7fb; }
        .card-hero { border-radius:10px; }
        .muted-small { color:#6c757d; font-size:0.95rem }
    </style>
</head>
<body>
<div class="container-fluid my-4 px-4">
    <input type="hidden" id="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
    <input type="hidden" id="class_id" value="<?php echo htmlspecialchars($class_id); ?>">
    <input type="hidden" id="session_id" value="<?php echo htmlspecialchars($active_session_id); ?>">
    <div class="card card-hero mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h2>
                    <div class="muted-small">Code: <strong><?php echo htmlspecialchars($class['class_code'] ?? '-'); ?></strong> &nbsp;•&nbsp; Grade: <strong><?php echo htmlspecialchars($class['grade_level'] ?? '-'); ?></strong></div>
                    <?php if (!empty($class['description'])): ?><p class="mt-2 mb-0 text-muted"><?php echo htmlspecialchars($class['description']); ?></p><?php endif; ?>
                </div>
                <div class="text-right">
                    <a href="classes.php" class="btn btn-outline-secondary"><i class="fas fa-chevron-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Sections</strong>
                    <span class="badge badge-primary"><?php echo count($sections); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($sections)): ?>
                        <div class="p-4 text-center text-muted">No sections found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="thead-light"><tr><th>#</th><th>Name</th><th>Code</th><th>Room</th><th>Capacity</th><th>Students</th><th>Teacher</th></tr></thead>
                                <tbody>
                                <?php $i=1; foreach($sections as $s): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($s['section_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($s['section_code'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($s['room_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($s['capacity'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($s['current_enrollment'] ?? '0'); ?></td>
                                        <td><?php
                                            if (!empty($s['class_teacher_id'])) {
                                                try {
                                                    $t = $db->prepare('SELECT name FROM school_teachers WHERE id = :id LIMIT 1');
                                                    $t->execute([':id' => $s['class_teacher_id']]);
                                                    $tr = $t->fetch(PDO::FETCH_ASSOC);
                                                    echo htmlspecialchars($tr['name'] ?? '-');
                                                } catch (Throwable $e) { echo '-'; }
                                            } else echo '-';
                                        ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Subjects card -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Subjects</strong>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addSubjectModal"><i class="fas fa-plus"></i> Add Subject</button>
                </div>
                <div class="card-body">
                    <?php
                    // prepare teachers list for selects
                    $teachers = [];
                    try {
                        $tq = $db->prepare('SELECT id, name FROM school_teachers WHERE school_id = :sid AND status = 1 ORDER BY name ASC');
                        $tq->execute([':sid' => $school_id]);
                        $teachers = $tq->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) { /* ignore */ }
                    ?>

                    <div class="table-responsive mb-3">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light"><tr><th>#</th><th>Subject</th><th>Assigned Sections</th><th>Assigned Teachers</th><th>Teacher</th><th>Status</th><th>Created</th></tr></thead>
                            <tbody>
                            <?php if ($subjects === null): ?>
                                <tr><td colspan="7" class="text-muted">No subjects table found in database.</td></tr>
                            <?php elseif (empty($subjects)): ?>
                                <tr><td colspan="7" class="text-muted">No subjects configured.</td></tr>
                            <?php else: $i=1; foreach($subjects as $s): 
                                    $sid = (int)($s['id'] ?? 0);
                                    $as = $subjectAssignments[$sid] ?? ['sections'=>[], 'teachers'=>[]];
                                    $sectionsDisplay = !empty($as['sections']) ? htmlspecialchars(implode(', ', array_unique($as['sections']))) : '-';
                                    $assignTeachers = !empty($as['teachers']) ? htmlspecialchars(implode(', ', array_unique($as['teachers']))) : '-';
                            ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($s['name'] ?? $s['subject_name'] ?? ''); ?></td>
                                    <td><?php echo $sectionsDisplay; ?></td>
                                    <td><?php echo $assignTeachers; ?></td>
                                    <td><?php echo htmlspecialchars($s['teacher_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($s['status'] ?? ($s['status'] ?? 'active')); ?></td>
                                    <td><?php echo htmlspecialchars($s['created_at'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">Subject assignment is handled via the Add Subject modal — select sections and choose a teacher there.</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" role="dialog" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add Subject</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addSubjectModalForm" onsubmit="return false;">
                        <input type="hidden" id="modal_school_id" value="<?php echo htmlspecialchars($school_id); ?>">
                        <div class="form-group">
                                <label for="modal_subject_name">Subject Name</label>
                                <input type="text" id="modal_subject_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                                <label for="modal_subject_code">Code (optional)</label>
                                <input type="text" id="modal_subject_code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="modal_subject_teacher">Assign Teacher</label>
                            <select id="modal_subject_teacher" class="form-control">
                                <option value="">-- none --</option>
                                <?php if (!empty($teachers)) { foreach($teachers as $tt) {
                                    echo '<option value="'.htmlspecialchars($tt['id']).'">'.htmlspecialchars($tt['name']).'</option>';
                                }} ?>
                            </select>
                            <small class="form-text text-muted">Choose a teacher to assign (optional).</small>
                        </div>
                        <div class="form-group">
                                <label for="modal_subject_status">Status</label>
                                <select id="modal_subject_status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                </select>
                        </div>
                    <div class="form-group">
                        <label>Assign Section(s)</label>
                        <div style="max-height:160px;overflow:auto;padding:6px;border:1px solid #e9ecef;border-radius:4px;">
                            <?php foreach($sections as $sec) {
                                $sid = htmlspecialchars($sec['id']);
                                $sname = htmlspecialchars($sec['section_name']);
                                echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="'.$sid.'" id="sec_'.$sid.'" name="modal_section[]">';
                                echo '<label class="form-check-label" for="sec_'.$sid.'">'.$sname.'</label></div>';
                            } ?>
                        </div>
                        <small class="form-text text-muted">Select one or more sections (leave empty to assign to all sections of this class).</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSubjectBtn">Save Subject</button>
            </div>
        </div>
    </div>
</div>

<script>
// Save subject from modal
document.getElementById('saveSubjectBtn').addEventListener('click', function(e){
    e.preventDefault();
    const name = document.getElementById('modal_subject_name').value.trim();
    const code = document.getElementById('modal_subject_code').value.trim();
    const teacherVal = document.getElementById('modal_subject_teacher').value || '';
    const sectionChecks = Array.from(document.querySelectorAll('input[name="modal_section[]"]:checked')).map(i => i.value);
    const sections = sectionChecks.filter(Boolean);
    const status = document.getElementById('modal_subject_status').value || 'active';
    const school_id = document.getElementById('modal_school_id').value;
    if (!name) return alert('Enter subject name');
    const fd = new FormData();
    fd.append('school_id', school_id);
    fd.append('name', name);
    if (code) fd.append('code', code);
    if (teacherVal) fd.append('teacher_id', teacherVal);
    fd.append('status', status);
    fetch('save_subject.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => {
            if (j.success) {
                const subjectId = j.id;
                // If sections or teachers selected, call assign endpoint with arrays
                if (sections.length || teacherVal) {
                    const afd = new FormData();
                    afd.append('school_id', school_id);
                    afd.append('subject_id', subjectId);
                    afd.append('class_id', document.getElementById('class_id').value);
                    const session = document.getElementById('session_id') ? document.getElementById('session_id').value : '';
                    if (session) afd.append('session_id', session);
                    // append sections and teachers (may be empty)
                    sections.forEach(s => afd.append('section_id[]', s));
                    if (teacherVal) afd.append('teacher_id', teacherVal);
                    fetch('assign_subject.php', { method: 'POST', body: afd })
                        .then(r2 => r2.json())
                        .then(j2 => {
                            $('#addSubjectModal').modal('hide');
                            if (j2.success) location.reload();
                            else alert(j2.message || 'Saved but assignment failed');
                        }).catch(err => { $('#addSubjectModal').modal('hide'); location.reload(); });
                } else {
                    $('#addSubjectModal').modal('hide');
                    location.reload();
                }
            } else {
                alert(j.message || 'Failed to save');
            }
        })
        .catch(err => alert('Request failed'));
});

</script>
</html>
