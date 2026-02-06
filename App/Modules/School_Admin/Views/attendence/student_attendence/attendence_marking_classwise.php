<?php
/**
 * Student Attendance - Class Wise Marking
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../Controllers/StudentAttendanceController.php';
require_once __DIR__ . '/../../../Models/StudentAttendanceModel.php';

// Get parameters
$school_id = $_SESSION['school_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');
$selected_section = $_GET['section_id'] ?? null;

// Initialize variables
$controller = null;
$totalStudents = 0;
$attendanceStats = ['P' => 0, 'A' => 0, 'L' => 0, 'HD' => 0];
$sections = [];
$classInfo = null;
$attendanceRegister = [];

if ($school_id && $class_id) {
    $controller = new \App\Modules\School_Admin\Controllers\StudentAttendanceController((int)$school_id);
    
    // Get class info
    require_once __DIR__ . '/../../../../../Core/database.php';
    $db = \Database::connect();
    $stmt = $db->prepare("SELECT id, class_name FROM school_classes WHERE id = ? AND school_id = ? LIMIT 1");
    $stmt->execute([$class_id, $school_id]);
    $classInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get sections for this class
    $sections = $controller->getSectionsByClass((int)$class_id);
    
    // If section selected, get stats for that section
    if ($selected_section) {
        $month_year = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT se.student_id) as total,
                   SUM(CASE WHEN sa.status = 'P' THEN 1 ELSE 0 END) as present_count,
                   SUM(CASE WHEN sa.status = 'A' THEN 1 ELSE 0 END) as absent_count,
                   SUM(CASE WHEN sa.status = 'L' THEN 1 ELSE 0 END) as leave_count
            FROM school_student_enrollments se
            LEFT JOIN school_student_attendance sa ON sa.student_id = se.student_id 
                AND sa.class_id = ? 
                AND sa.section_id = ? 
                AND sa.school_id = ? 
                AND DATE_FORMAT(sa.attendance_date, '%Y-%m') = ?
            WHERE se.school_id = ? 
                AND se.class_id = ? 
                AND se.section_id = ? 
                AND se.status = 'active'
        ");
        $stmt->execute([
            $class_id,
            $selected_section,
            $school_id,
            $month_year,
            $school_id,
            $class_id,
            $selected_section
        ]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalStudents = (int)($stats['total'] ?? 0);
        $attendanceStats['P'] = (int)($stats['present_count'] ?? 0);
        $attendanceStats['A'] = (int)($stats['absent_count'] ?? 0);
        $attendanceStats['L'] = (int)($stats['leave_count'] ?? 0);
        
        // Get attendance register data
        $stmt = $db->prepare("
            SELECT se.student_id, se.admission_no, se.roll_no, ss.first_name, ss.last_name,
                   sa.attendance_date, sa.status, sa.remarks
            FROM school_student_enrollments se
            LEFT JOIN school_students ss ON ss.id = se.student_id
            LEFT JOIN school_student_attendance sa ON sa.student_id = se.student_id 
                AND sa.class_id = ? 
                AND sa.section_id = ? 
                AND sa.school_id = ? 
                AND DATE_FORMAT(sa.attendance_date, '%Y-%m') = ?
            WHERE se.school_id = ? 
                AND se.class_id = ? 
                AND se.section_id = ? 
                AND se.status = 'active'
            ORDER BY se.roll_no, ss.first_name
        ");
        $stmt->execute([
            $class_id,
            $selected_section,
            $school_id,
            $month_year,
            $school_id,
            $class_id,
            $selected_section
        ]);
        $attendanceRegister = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Attendance - Mark Attendance</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <style>
        .attendance-calendar { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .attendance-calendar th { background-color: #f8f9fa; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #dee2e6; }
        .attendance-calendar td { padding: 8px; text-align: center; border: 1px solid #dee2e6; }
        .attendance-calendar .student-name { text-align: left; font-weight: 500; }
        .attendance-cell { cursor: pointer; padding: 4px 6px; border-radius: 3px; }
        .attendance-cell.present { background-color: #28a745; color: white; }
        .attendance-cell.absent { background-color: #dc3545; color: white; }
        .attendance-cell.leave { background-color: #ffc107; color: black; }
        .attendance-cell.halfday { background-color: #17a2b8; color: white; }
        .filter-section { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="app">
    <div class="app-wrap">
        <div class="app-container">
            <div class="" id="main">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-11">
                            <h3 class="mb-3">Mark Student Attendance</h3>
                            <?php if ($classInfo): ?>
                                <p class="text-muted">Class: <strong><?php echo htmlspecialchars($classInfo['class_name']); ?></strong></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-1 mt-3">
                            <button onclick="window.history.back()" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1">Total Students</p>
                                            <h5><?php echo $totalStudents; ?></h5>
                                        </div>
                                        <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-left-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1">Present</p>
                                            <h5 class="text-success"><?php echo $attendanceStats['P']; ?></h5>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-left-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1">Absent</p>
                                            <h5 class="text-danger"><?php echo $attendanceStats['A']; ?></h5>
                                        </div>
                                        <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted mb-1">On Leave</p>
                                            <h5 class="text-warning"><?php echo $attendanceStats['L']; ?></h5>
                                        </div>
                                        <i class="fas fa-sun fa-2x text-warning opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Month</label>
                                <select id="monthFilter" class="form-control" onchange="applyFilter()">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo $selected_month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Year</label>
                                <select id="yearFilter" class="form-control" onchange="applyFilter()">
                                    <?php for ($y = 2020; $y <= 2030; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select id="sectionFilter" class="form-control" onchange="applyFilter()">
                                    <option value="">-- Select Section --</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo $section['id']; ?>" <?php echo $selected_section == $section['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($section['section_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100" onclick="openMarkAttendanceModal()">
                                    <i class="fas fa-plus"></i> Mark Attendance
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Calendar -->
                    <?php if ($selected_section && !empty($attendanceRegister)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5>Attendance Register - <?php echo htmlspecialchars($classInfo['class_name']); ?></h5>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="attendance-calendar">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Student Info</th>
                                            <?php
                                            // Generate calendar days
                                            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
                                            for ($day = 1; $day <= $daysInMonth; $day++):
                                                $date = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                                $dayName = date('D', strtotime($date));
                                            ?>
                                                <th style="width: calc(80% / <?php echo $daysInMonth; ?>);">
                                                    <small><?php echo $day; ?><br><?php echo $dayName; ?></small>
                                                </th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendanceRegister as $record): ?>
                                            <tr>
                                                <td class="student-name">
                                                    <small>
                                                        <strong><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></strong><br>
                                                        Roll: <?php echo $record['roll_no']; ?> | Adm: <?php echo $record['admission_no']; ?>
                                                    </small>
                                                </td>
                                                <?php
                                                for ($day = 1; $day <= $daysInMonth; $day++):
                                                    $date = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                                    $status = '';
                                                    foreach ($attendanceRegister as $r) {
                                                        if ($r['student_id'] == $record['student_id'] && $r['attendance_date'] == $date) {
                                                            $status = $r['status'];
                                                            break;
                                                        }
                                                    }
                                                    $statusClass = '';
                                                    $statusDisplay = '-';
                                                    if ($status == 'P') { $statusClass = 'present'; $statusDisplay = 'P'; }
                                                    elseif ($status == 'A') { $statusClass = 'absent'; $statusDisplay = 'A'; }
                                                    elseif ($status == 'L') { $statusClass = 'leave'; $statusDisplay = 'L'; }
                                                    elseif ($status == 'HD') { $statusClass = 'halfday'; $statusDisplay = 'HD'; }
                                                ?>
                                                    <td><span class="attendance-cell <?php echo $statusClass; ?>"><?php echo $statusDisplay; ?></span></td>
                                                <?php endfor; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php elseif (!$selected_section): ?>
                        <div class="alert alert-info">Please select a section to view and mark attendance</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Marking Attendance -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label">Select Section</label>
                    <select id="modalSectionFilter" class="form-control" onchange="loadStudentsForSection()">
                        <option value="">-- Select Section --</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['section_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Select Date</label>
                    <input type="date" id="modalAttendanceDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div id="studentsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveAttendance()">Save Attendance</button>
            </div>
        </div>
    </div>
</div>

<script src="../../../../../../public/assets/js/vendors.js"></script>
<script src="../../../../../../public/assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function applyFilter() {
        const month = document.getElementById('monthFilter').value;
        const year = document.getElementById('yearFilter').value;
        const section = document.getElementById('sectionFilter').value;
        const classId = '<?php echo $class_id; ?>';
        
        if (section) {
            window.location.href = `attendence_marking_classwise.php?class_id=${classId}&month=${month}&year=${year}&section_id=${section}`;
        }
    }

    function openMarkAttendanceModal() {
        const modal = new bootstrap.Modal(document.getElementById('markAttendanceModal'));
        modal.show();
    }

    function loadStudentsForSection() {
        const sectionId = document.getElementById('modalSectionFilter').value;
        const classId = '<?php echo $class_id; ?>';
        
        if (!sectionId) {
            document.getElementById('studentsList').innerHTML = '';
            return;
        }

        // Show loading message
        document.getElementById('studentsList').innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading students...</p>';

        // Build the URL
        const url = `get_students_for_section.php?class_id=${classId}&section_id=${sectionId}`;
        console.log('Fetching URL:', url);

        // Fetch students via AJAX
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text(); // Use text() first to debug
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.data.length > 0) {
                        let html = '<div class="form-group"><label class="form-label">Students</label><div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">';
                        
                        data.data.forEach((student, index) => {
                            const studentId = `student_${student.student_id}`;
                            html += `
                                <div class="mb-3 pb-3 border-bottom" style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong>${student.first_name} ${student.last_name}</strong><br>
                                        <small class="text-muted">Roll: ${student.roll_no} | Admission: ${student.admission_no}</small>
                                    </div>
                                    <div style="display: flex; gap: 5px;">
                                        <label class="form-check form-check-inline">
                                            <input type="radio" name="attendance_${student.student_id}" value="P" class="form-check-input">
                                            <span class="form-check-label" style="color: #28a745; font-weight: bold;">P</span>
                                        </label>
                                        <label class="form-check form-check-inline">
                                            <input type="radio" name="attendance_${student.student_id}" value="A" class="form-check-input">
                                            <span class="form-check-label" style="color: #dc3545; font-weight: bold;">A</span>
                                        </label>
                                        <label class="form-check form-check-inline">
                                            <input type="radio" name="attendance_${student.student_id}" value="L" class="form-check-input">
                                            <span class="form-check-label" style="color: #ffc107; font-weight: bold;">L</span>
                                        </label>
                                        <label class="form-check form-check-inline">
                                            <input type="radio" name="attendance_${student.student_id}" value="HD" class="form-check-input">
                                            <span class="form-check-label" style="color: #17a2b8; font-weight: bold;">HD</span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div></div>';
                        document.getElementById('studentsList').innerHTML = html;
                    } else if (data.success && data.data.length === 0) {
                        document.getElementById('studentsList').innerHTML = '<p class="text-warning">No students found in this section.</p>';
                    } else {
                        document.getElementById('studentsList').innerHTML = '<p class="text-danger">Error: ' + (data.error || 'Unknown error') + '</p>';
                    }
                } catch (parseError) {
                    console.error('JSON Parse error:', parseError);
                    document.getElementById('studentsList').innerHTML = '<p class="text-danger">Error parsing response. Check console.</p>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('studentsList').innerHTML = '<p class="text-danger">Error loading students. Please try again.</p>';
            });
    }

    function saveAttendance() {
        const sectionId = document.getElementById('modalSectionFilter').value;
        const attendanceDate = document.getElementById('modalAttendanceDate').value;
        const classId = '<?php echo $class_id; ?>';

        if (!sectionId) {
            alert('Please select a section');
            return;
        }

        if (!attendanceDate) {
            alert('Please select a date');
            return;
        }

        // Collect attendance data from radio buttons
        const attendanceRecords = {};
        const allInputs = document.querySelectorAll('input[name^="attendance_"]');
        
        allInputs.forEach(input => {
            if (input.checked) {
                const studentId = input.name.replace('attendance_', '');
                attendanceRecords[studentId] = input.value;
            }
        });

        if (Object.keys(attendanceRecords).length === 0) {
            alert('Please mark attendance for at least one student');
            return;
        }

        const saveBtn = document.querySelector('button[onclick="saveAttendance()"]');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Send data to server
        fetch('save_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                class_id: parseInt(classId),
                section_id: parseInt(sectionId),
                attendance_date: attendanceDate,
                attendance: attendanceRecords
            })
        })
        .then(response => response.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Attendance';

            if (data.success) {
                alert(data.message);
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('markAttendanceModal'));
                modal.hide();
                // Reset form
                document.getElementById('modalSectionFilter').value = '';
                document.getElementById('studentsList').innerHTML = '';
                // Reload page to show updated attendance
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Attendance';
            console.error('Error:', error);
            alert('Error saving attendance. Please try again.');
        });
    }
</script>
</body>
</html>
