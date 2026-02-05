<?php
/**
 * School Admin - Classes Management
 */
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
// fetch active session for this school (readonly in UI)
$active_session_name = '';
$active_session_id = '';
try {
    require_once __DIR__ . '/../../../../Core/database.php';
    $db = \Database::connect();
    $school_id = $_SESSION['school_id'] ?? null;
    if ($school_id) {
        $stmt = $db->prepare("SELECT id, name FROM school_sessions WHERE school_id = :sid AND is_active = 1 AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['sid' => $school_id]);
        $sess = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sess) {
            $active_session_name = htmlspecialchars($sess['name']);
            $active_session_id = $sess['id'];
        }
    }
} catch (Exception $e) {
    // ignore - UI will show empty session
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classes - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Classes Management" />
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
        /* Page Content Styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .page-title-section h5 {
            color: #999;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .page-title-section h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }

        .page-actions {
            display: flex;
            gap: 10px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card-icon.blue {
            background: #e3f2fd;
            color: #2196f3;
        }

        .stat-card-icon.green {
            background: #e8f5e9;
            color: #4caf50;
        }

        .stat-card-icon.orange {
            background: #fff3e0;
            color: #ff9800;
        }

        .stat-card-icon.purple {
            background: #f3e5f5;
            color: #9c27b0;
        }

        .stat-card h6 {
            color: #999;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* Filters & Search */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: border-color 0.3s;
            background: white;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f5f7fa;
            border-bottom: 2px solid #e5e7eb;
        }

        table th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
        }

        table td {
            padding: 14px 16px;
            border-bottom: 1px solid #ecf0f1;
            color: #1a1a1a;
            font-size: 13px;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Action Buttons in Table */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-edit {
            background: #e3f2fd;
            color: #2196f3;
        }

        .btn-edit:hover {
            background: #2196f3;
            color: white;
        }

        .btn-delete {
            background: #ffebee;
            color: #f44336;
        }

        .btn-delete:hover {
            background: #f44336;
            color: white;
        }

        .btn-view {
            background: #e8f5e9;
            color: #4caf50;
        }

        .btn-view:hover {
            background: #4caf50;
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .modal-header h2 {
            color: #1a1a1a;
            font-size: 22px;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .close-btn:hover {
            color: #1a1a1a;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-actions {
                width: 100%;
                margin-top: 15px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .filters-section {
                flex-direction: column;
            }

            .search-box {
                min-width: auto;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
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

                        <!-- Page Header -->
                        <div class="page-header">
                            <div class="page-title-section">
                                <h5>Academic</h5>
                                <h2>Classes Management</h2>
                            </div>
                            <div class="page-actions">
                                <button class="btn btn-primary" onclick="startAddClass()">+ Add New Class</button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <?php
                        // Compute summary statistics for the cards
                        try {
                            // Total classes
                            $totStmt = $db->prepare("SELECT COUNT(*) FROM school_classes WHERE school_id = :sid AND session_id = :sess");
                            $totStmt->execute([':sid' => $school_id, ':sess' => $active_session_id]);
                            $total_classes = (int) $totStmt->fetchColumn();

                            // Total students (sum of current_enrollment from sections)
                            $studentsStmt = $db->prepare("SELECT COALESCE(SUM(s.current_enrollment),0) FROM school_class_sections s JOIN school_classes c ON s.class_id = c.id WHERE c.school_id = :sid AND c.session_id = :sess");
                            $studentsStmt->execute([':sid' => $school_id, ':sess' => $active_session_id]);
                            $total_students = (int) $studentsStmt->fetchColumn();

                            // Active teachers (count from teachers table where status = 1)
                            $teachersStmt = $db->prepare("SELECT COUNT(*) FROM school_teachers WHERE school_id = :sid AND status = 1");
                            $teachersStmt->execute([':sid' => $school_id]);
                            $active_teachers = (int) $teachersStmt->fetchColumn();

                            $avg_class_size = $total_classes > 0 ? round($total_students / max(1, $total_classes)) : 0;
                        } catch (Exception $e) {
                            $total_classes = $total_students = $active_teachers = $avg_class_size = 0;
                        }
                        ?>

                        <div class="stats-row">
                            <div class="stat-card">
                                <div class="stat-card-icon blue">📚</div>
                                <h6>Total Classes</h6>
                                <div class="value"><?php echo $total_classes; ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon green">👨‍🎓</div>
                                <h6>Total Students</h6>
                                <div class="value"><?php echo $total_students; ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon orange">👨‍🏫</div>
                                <h6>Active Teachers</h6>
                                <div class="value"><?php echo $active_teachers; ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon purple">📊</div>
                                <h6>Avg Class Size</h6>
                                <div class="value"><?php echo $avg_class_size; ?></div>
                            </div>
                        </div>

                        <!-- Filters Section (single search only) -->
                        <div class="filters-section">
                            <div class="search-box">
                                <input id="classSearch" type="text" placeholder="🔍 Search classes by name, code, section, or teacher...">
                            </div>
                        </div>

                        <!-- Classes Table -->
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Class Name</th>
                                        <th>Code</th>
                                        <th>Sections</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch classes (one row per class) and compute section counts for current school + session
                                    try {
                                        $sql = "SELECT c.id AS class_id, c.class_name, c.class_code, c.status AS class_status,
                                            COUNT(sc.id) AS sections_count,
                                            COALESCE(SUM(sc.current_enrollment),0) AS students_count,
                                            (SELECT t2.name FROM school_class_sections sc2 JOIN school_teachers t2 ON sc2.class_teacher_id = t2.id WHERE sc2.class_id = c.id AND sc2.school_id = :sid1 AND sc2.class_teacher_id IS NOT NULL LIMIT 1) AS teacher_name
                                            FROM school_classes c
                                            LEFT JOIN school_class_sections sc ON sc.class_id = c.id AND sc.school_id = :sid2
                                            LEFT JOIN school_teachers t ON sc.class_teacher_id = t.id
                                            WHERE c.school_id = :sid3 AND c.session_id = :sess
                                            GROUP BY c.id
                                            ORDER BY c.id ASC";
                                        $stmt = $db->prepare($sql);
                                        $stmt->execute(['sid1' => $school_id, 'sid2' => $school_id, 'sid3' => $school_id, 'sess' => $active_session_id]);
                                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if (empty($rows)) {
                                            echo '<tr><td colspan="8" class="text-center">No classes found.</td></tr>';
                                        } else {
                                            $counter = 1;
                                            foreach ($rows as $r) {
                                                $className = htmlspecialchars($r['class_name'] ?? '');
                                                $code = htmlspecialchars($r['class_code'] ?? '-');
                                                $sectionsCount = intval($r['sections_count'] ?? 0);
                                                $status = ($r['class_status'] ?? 'active');
                                                $classId = intval($r['class_id'] ?? 0);

                                                echo '<tr>';
                                                echo '<td>' . $counter++ . '</td>';
                                                echo '<td><strong>' . $className . '</strong></td>';
                                                echo '<td>' . $code . '</td>';
                                                echo '<td>' . $sectionsCount . '</td>';
                                                echo '<td><span class="badge badge-' . ($status === 'active' ? 'success' : 'secondary') . '">' . ucfirst($status) . '</span></td>';
                                                echo '<td>';
                                                echo '<div class="action-btns">';
                                                echo '<button class="btn-sm btn-view" onclick="viewClass(' . $classId . ')">View</button>';
                                                echo '<button class="btn-sm btn-edit" onclick="editClass(' . $classId . ')">Edit</button>';
                                                echo '<button class="btn-sm btn-delete" onclick="deleteClass(' . $classId . ')">Delete</button>';
                                                echo '<button class="btn-sm" style="background:#6c757d;color:#fff;padding:6px 8px;border-radius:4px;" onclick="openAddSectionModal(' . $classId . ')">Add Section</button>';
                                                echo '</div>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                    } catch (Exception $e) {
                                        echo '<tr><td colspan="8" class="text-danger">Error loading classes: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
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
                        <p>&copy; Copyright 2024. All rights reserved.</p>
                    </div>
                    <div class="col col-sm-6 ml-sm-auto text-center text-sm-right">
                        <p><a target="_blank" href="#">School Management System</a></p>
                    </div>
                </div>
            </footer>
            <!-- end footer -->
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- Add/Edit Class Modal -->
    <div id="classModal" class="modal">
        <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h2>Add New Class</h2>
                <button class="close-btn" onclick="closeClassModal()">×</button>
            </div>
            <form id="classForm">
                <!-- CLASS INFORMATION SECTION -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <h5 style="color: #667eea; font-weight: 600; margin-top: 0; margin-bottom: 15px;">📚 Class Information</h5>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Class Name *</label>
                            <input type="text" id="className" placeholder="e.g., Class I, Class II">
                        </div>
                        <div class="form-group">
                            <label>Class Code <small style="color: #999;">(Optional - Auto ID if empty)</small></label>
                            <input type="text" id="classCode" placeholder="e.g., CL-01, CL-02">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label>Grade / Level</label>
                            <input type="text" id="gradeLevel" placeholder="Auto - same as class name" readonly>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Session</label>
                            <input type="text" id="sessionName" value="<?php echo $active_session_name; ?>" readonly>
                            <input type="hidden" id="sessionId" value="<?php echo $active_session_id; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" placeholder="Class description and notes..." rows="2"></textarea>
                    </div>
                </div>

                <!-- SECTIONS SECTION -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h5 style="color: #667eea; font-weight: 600; margin: 0;">📋 Class Sections</h5>
                        <button type="button" class="btn btn-primary" onclick="addSectionRow()" style="padding: 6px 12px; font-size: 12px;">+ Add Section</button>
                    </div>

                    <div id="sectionsContainer">
                        <!-- Sections will be added here dynamically -->
                    </div>

                    <button type="button" class="btn btn-primary" onclick="addSectionRow()" style="margin-top: 10px; padding: 8px 16px; font-size: 12px; width: 100%;">+ Add Another Section</button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeClassModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Class & Sections</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Row Template (hidden) -->
    <template id="sectionTemplate">
        <div class="section-row" style="background: white; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong style="color: #1a1a1a;">Section</strong>
                <button type="button" class="btn btn-delete btn-sm" onclick="removeSectionRow(this)" style="padding: 4px 8px; font-size: 10px;">Remove</button>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" class="section-name" placeholder="e.g., A, B, C" maxlength="50">
                </div>
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" class="section-room" placeholder="e.g., Room 101, A-305">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" class="section-capacity" placeholder="e.g., 40" min="1">
                </div>
            </div>
        </div>
    </template>

    <!-- Add Section Modal -->
    <div id="addSectionModal" class="modal">
        <div class="modal-content" style="max-width:500px;">
            <div class="modal-header">
                <h2>Add Section</h2>
                <button class="close-btn" onclick="closeAddSectionModal()">×</button>
            </div>
            <div style="padding:10px 0 0 0;">
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" id="newSectionName" placeholder="e.g., A, B">
                </div>
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" id="newSectionRoom" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" id="newSectionCapacity" min="1" placeholder="Optional">
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px;">
                    <button class="btn btn-secondary" onclick="closeAddSectionModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="submitAddSection()">Save Section</button>
                </div>
            </div>
        </div>
    </div>

    <!-- plugins -->
    <script type="text/javascript" src="../../../../../public/assets/js/vendors.js"></script>
    <!-- custom scripts -->
    <script type="text/javascript" src="../../../../../public/assets/js/app.js"></script>

    <script>
        let sectionCount = 0;
        let editingClassId = null;

        function openAddClassModal() {
            sectionCount = 0;
            document.getElementById('classForm').reset();
            document.getElementById('sectionsContainer').innerHTML = '';
            addSectionRow(); // Add one empty section row by default
            // ensure grade reflects class name
            const nameEl = document.getElementById('className');
            const gradeEl = document.getElementById('gradeLevel');
            if (nameEl && gradeEl) {
                gradeEl.value = nameEl.value || '';
                nameEl.addEventListener('input', function() {
                    gradeEl.value = this.value;
                });
            }
            document.getElementById('classModal').classList.add('show');
        }

        function startAddClass() {
            editingClassId = null;
            document.querySelector('.modal-header h2').textContent = 'Add New Class';
            openAddClassModal();
        }

        function closeClassModal() {
            document.getElementById('classModal').classList.remove('show');
        }

        function addSectionRow(data = null) {
            const template = document.getElementById('sectionTemplate');
            const container = document.getElementById('sectionsContainer');
            const clone = template.content.cloneNode(true);
            
            // Add unique IDs
            const sectionRow = clone.querySelector('.section-row');
            sectionRow.id = 'section-' + sectionCount++;

            // populate if data provided
            if (data) {
                const nameEl = sectionRow.querySelector('.section-name');
                const roomEl = sectionRow.querySelector('.section-room');
                const capEl = sectionRow.querySelector('.section-capacity');
                if (nameEl) nameEl.value = data.section_name || data.sectionName || '';
                if (roomEl) roomEl.value = data.room_number || data.room || '';
                if (capEl) capEl.value = data.capacity || '';
            }
            
            container.appendChild(clone);
        }

        function removeSectionRow(button) {
            const row = button.closest('.section-row');
            const container = document.getElementById('sectionsContainer');
            
            // Prevent removing the last section
            if (container.querySelectorAll('.section-row').length > 1) {
                row.remove();
            } else {
                alert('You must have at least one section!');
            }
        }

        function viewClass(id) {
            // navigate to detailed class view
            window.location.href = '/School-SAAS/App/Modules/School_Admin/Views/classes/viewclass.php?id=' + encodeURIComponent(id);
        }

        async function editClass(id) {
            editingClassId = id;
            document.querySelector('.modal-header h2').textContent = 'Edit Class';
            // open modal and populate
            openAddClassModal();
            // fetch class data
            try {
                const resp = await fetch('/School-SAAS/App/Modules/School_Admin/Views/classes/get_class.php?id=' + encodeURIComponent(id), { credentials: 'same-origin' });
                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    alert('Failed to load class: ' + (data.message || resp.statusText));
                    return;
                }
                const cls = data.class;
                const secs = data.sections || [];

                document.getElementById('className').value = cls.class_name || '';
                document.getElementById('classCode').value = cls.class_code || '';
                document.getElementById('gradeLevel').value = cls.grade_level || '';
                document.getElementById('description').value = cls.description || '';

                // clear existing section rows
                const container = document.getElementById('sectionsContainer');
                container.innerHTML = '';
                sectionCount = 0;
                if (secs.length === 0) {
                    addSectionRow();
                } else {
                    secs.forEach(s => addSectionRow(s));
                }
            } catch (err) {
                console.error(err);
                alert('Error loading class: ' + err.message);
            }
        }

        function deleteClass(id) {
            if (confirm('Are you sure you want to delete this class?')) {
                alert('Class deleted');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('classModal');
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        }

        // Add Section modal functions
        let addSectionClassId = null;
        function openAddSectionModal(classId) {
            addSectionClassId = classId;
            document.getElementById('newSectionName').value = '';
            document.getElementById('newSectionRoom').value = '';
            document.getElementById('newSectionCapacity').value = '';
            document.getElementById('addSectionModal').classList.add('show');
        }

        function closeAddSectionModal() {
            document.getElementById('addSectionModal').classList.remove('show');
            addSectionClassId = null;
        }

        async function submitAddSection() {
            const name = document.getElementById('newSectionName').value.trim();
            const room = document.getElementById('newSectionRoom').value.trim();
            const capacityVal = document.getElementById('newSectionCapacity').value;
            const capacity = capacityVal === '' ? null : parseInt(capacityVal, 10);

            if (!addSectionClassId) {
                alert('Class not specified');
                return;
            }
            if (!name) {
                alert('Section name is required');
                return;
            }

            try {
                const payload = { class_id: addSectionClassId, name: name, room: room, capacity: capacity, session_id: (document.getElementById('sessionId') ? document.getElementById('sessionId').value : '') };
                const resp = await fetch('/School-SAAS/App/Modules/School_Admin/Views/classes/add_section.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    alert('Error adding section: ' + (data.message || resp.statusText));
                    return;
                }
                alert('Section added');
                closeAddSectionModal();
                // reload to show new section and update counts
                setTimeout(() => location.reload(), 300);
            } catch (err) {
                console.error(err);
                alert('Unexpected error: ' + err.message);
            }
        }

        // Form submission
        document.getElementById('classForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const classNameVal = document.getElementById('className').value || '';
            const classData = {
                name: classNameVal,
                code: document.getElementById('classCode').value,
                grade: (document.getElementById('gradeLevel').value || classNameVal),
                session: document.getElementById('sessionId') ? document.getElementById('sessionId').value : '',
                description: document.getElementById('description').value,
                sections: []
            };

            if (editingClassId) {
                classData.id = editingClassId;
            }

            // Collect section data
            document.querySelectorAll('.section-row').forEach(row => {
                const nameEl = row.querySelector('.section-name');
                const roomEl = row.querySelector('.section-room');
                const capEl = row.querySelector('.section-capacity');
                const section = {
                    name: nameEl ? nameEl.value : '',
                    room: roomEl ? roomEl.value : '',
                    capacity: capEl ? capEl.value : ''
                };

                if (section.name && section.name.trim()) {
                    classData.sections.push(section);
                }
            });

            // Validate
            if (!classData.name.trim()) {
                alert('Class name is required!');
                return;
            }
            if (!classData.grade) {
                alert('Grade level is required!');
                return;
            }
            if (!classData.session) {
                alert('Session is required!');
                return;
            }
            if (classData.sections.length === 0) {
                alert('Add at least one section!');
                return;
            }

            // Send to backend
            console.log('Saving class', classData);
            
            // Build absolute path for API endpoint
            const apiPath = '/School-SAAS/App/Modules/School_Admin/Views/classes/save_class.php';
            
            fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(classData)
            })
            .then(resp => {
                // Check response content type
                const contentType = resp.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    return resp.text().then(txt => {
                        throw new Error('Invalid response: expected JSON but got: ' + txt.substring(0, 200));
                    });
                }

                // Parse JSON body then decide based on status
                return resp.json().then(data => {
                    if (!resp.ok) {
                        const serverMsg = data && data.message ? data.message : ('HTTP ' + resp.status);
                        throw new Error(serverMsg);
                    }
                    return data;
                });
            })
            .then(data => {
                console.log('Server response:', data);
                if (data && data.success) {
                    alert('Class saved successfully!');
                    closeClassModal();
                    // Refresh page to show new class
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    alert('Save failed: ' + (data && data.message ? data.message : 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                alert('Error saving class: ' + err.message);
            });
        });

        // Hide loader on page load
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
        
        // Simple client-side search/filter for classes table
        document.getElementById('classSearch')?.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.table-container tbody tr');
            rows.forEach(r => {
                // if this row is the 'no classes found' message, leave display handling to match search
                const text = r.textContent.toLowerCase();
                if (!q || text.indexOf(q) !== -1) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
