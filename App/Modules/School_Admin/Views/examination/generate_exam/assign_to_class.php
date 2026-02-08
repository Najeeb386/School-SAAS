<?php
/**
 * School Admin Dashboard - Protected Page
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
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
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
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
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->
            <!-- begin app-header -->
            
            <!-- begin app-header -->
            <header class="app-header top-bar">
                <!-- begin navbar -->
                <!-- end navbar -->
            </header>
            <!-- end app-header -->
            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-navbar -->
                <!-- end app-navbar -->
                <!-- begin app-main -->
                <div class="" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="mb-0" style="color: #000; font-weight: 700;">Assign Exams to Classes</h3>
                                    <button class="btn btn-primary" onclick="openAssignModal()">
                                        <i class="fa fa-plus"></i> Assign Exam
                                    </button>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item"><a href="./generate_exam.php">Generate Exam</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Assign to Classes</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <!-- Schedule/Datesheet Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fa fa-calendar"></i> Exam Schedule / Datesheet
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-primary btn-lg" onclick="viewSchedule()">
                                            <i class="fa fa-calendar"></i> View Complete Schedule
                                        </button>
                                        <button class="btn btn-success btn-lg" onclick="downloadDatesheet()">
                                            <i class="fa fa-download"></i> Download Datesheet
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Filter Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="filterClass" class="form-label fw-bold" style="color: #000;">Class</label>
                                                <select class="form-select" id="filterClass" onchange="loadFilterSections(); applyFilters();" style="color: #000;">
                                                    <option value="">All Classes</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label for="filterSection" class="form-label fw-bold" style="color: #000;">Section</label>
                                                <select class="form-select" id="filterSection" onchange="applyFilters()" style="color: #000;">
                                                    <option value="">All Sections</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label for="filterExam" class="form-label fw-bold" style="color: #000;">Exam</label>
                                                <select class="form-select" id="filterExam" onchange="applyFilters()" style="color: #000;">
                                                    <option value="">All Exams</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                                <button class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                                                    <i class="fa fa-refresh"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignments Table -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3" style="color: #000; font-weight: 700;">Exam Assignments with Subjects</h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered" id="assignmentsTable">
                                                <thead class="table-light">
                                                    <tr style="color: #000;">
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>Subject</th>
                                                        <th>Exam Name</th>
                                                        <th>Exam Date</th>
                                                        <th>Total Marks</th>
                                                        <th>Passing Marks</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="assignmentsBody">
                                                    <!-- Will be populated by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="text-muted text-center mt-3" id="noDataMsg" style="color: #666;">Loading assignments...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                    <!-- end container-fluid -->
                </div>
                <!-- end app-main -->
            </div>
            <!-- end app-container -->
            
        </div>
        <!-- end app-wrap -->
    </div>
    <!-- end app -->

    <!-- Assign Exam Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 90%; height: 90vh; overflow-y: auto;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="assignModalLabel">
                        <i class="fa fa-tasks"></i> Assign Exam to Class & Add Subjects
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignForm" class="needs-validation">
                        <input type="hidden" id="examClassId" name="exam_class_id">

                        <!-- Exam class assignment section -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-light">
                                <h6 class="mb-0" style="color: #000; font-weight: 700;">
                                    <i class="fa fa-graduation-cap"></i> Class Assignment Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="examSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Exam <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="examSelect" name="exam_name" onchange="updateExamId()" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Exam --</option>
                                        </select>
                                        <input type="hidden" id="examSelectId" name="exam_id" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="classSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Class <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="classSelect" name="class_id" onchange="loadSections()" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Class --</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="sectionSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Section <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="sectionSelect" name="section_id" onchange="loadSubjectsByClassAndSection(document.getElementById('classSelect').value, this.value); checkApplyAllStatus();" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Section --</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Apply to All Sections Checkbox -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="applyToAllSections" name="apply_to_all_sections" onchange="toggleApplyToAllSections()">
                                            <label class="form-check-label fw-bold" style="color: #000; cursor: pointer;" for="applyToAllSections">
                                                <i class="fa fa-check-square"></i> Apply to All Sections of this Class
                                            </label>
                                            <small class="d-block text-muted mt-2">
                                                <i class="fa fa-info-circle"></i> Check this to apply the subjects to all sections in the same class automatically. The section selection will be disabled.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subjects assignment section -->
                        <div class="card border-success">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0" style="color: #000; font-weight: 700;">
                                        <i class="fa fa-book"></i> Subject Details
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addSubjectRow()">
                                        <i class="fa fa-plus"></i> Add Subject
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="subjectsTable">
                                        <thead class="table-light">
                                            <tr style="color: #000;">
                                                <th>Subject</th>
                                                <th>Exam Date</th>
                                                <th>Exam Time</th>
                                                <th>Total Marks</th>
                                                <th>Passing Marks</th>
                                                <th>Status</th>
                                                <th width="80">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subjectsBody">
                                            <!-- Subject rows will be added here -->
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-muted text-center mt-3" id="noSubjectsMsg">
                                    Click "Add Subject" button to add subjects for this exam assignment
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAssignment()">
                        <i class="fa fa-save"></i> Assign & Save All Subjects
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Exam Assignment -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 600px;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fa fa-edit"></i> Edit Subject Assignment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" class="needs-validation">
                        <input type="hidden" id="editExamClassId" name="exam_class_id">
                        <input type="hidden" id="editSubjectId" name="subject_id">

                        <!-- Display class/section/exam info (read-only) -->
                        <div class="card mb-4 border-info bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold" style="color: #000; font-size: 12px;">CLASS</label>
                                        <p id="editClassName" style="color: #000; font-size: 16px; font-weight: 600; margin: 0;"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold" style="color: #000; font-size: 12px;">SECTION</label>
                                        <p id="editSectionName" style="color: #000; font-size: 16px; font-weight: 600; margin: 0;"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold" style="color: #000; font-size: 12px;">EXAM</label>
                                        <p id="editExamName" style="color: #000; font-size: 16px; font-weight: 600; margin: 0;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editable subject details -->
                        <div class="card border-success">
                            <div class="card-header bg-light">
                                <h6 class="mb-0" style="color: #000; font-weight: 700;">
                                    <i class="fa fa-book"></i> Subject: <span id="editSubjectName" style="font-style: italic;"></span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="editExamDate" class="form-label fw-bold" style="color: #000;">Exam Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg" id="editExamDate" name="exam_date" required style="border: 2px solid #e9ecef; color: #000;">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editExamTime" class="form-label fw-bold" style="color: #000;">Exam Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control form-control-lg" id="editExamTime" name="exam_time" required style="border: 2px solid #e9ecef; color: #000;">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="editTotalMarks" class="form-label fw-bold" style="color: #000;">Total Marks <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-lg" id="editTotalMarks" name="total_marks" placeholder="e.g. 100" min="1" required style="border: 2px solid #e9ecef; color: #000;">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editPassingMarks" class="form-label fw-bold" style="color: #000;">Passing Marks <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-lg" id="editPassingMarks" name="passing_marks" placeholder="e.g. 33" min="0" required style="border: 2px solid #e9ecef; color: #000;">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="editStatus" class="form-label fw-bold" style="color: #000;">Status <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-lg" id="editStatus" name="status" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="updateSingleSubject()">
                        <i class="fa fa-save"></i> Update Subject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- plugins -->
    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../../public/assets/js/app.js"></script>

    <style>
        #assignmentsTable {
            color: #000;
        }

        #assignmentsTable thead {
            background-color: #f8f9fa;
            color: #000;
            font-weight: 600;
        }

        #assignmentsTable thead th {
            color: #000;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6;
        }

        #assignmentsTable tbody td {
            color: #000;
            font-weight: 500;
            vertical-align: middle;
        }

        .badge-status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        #assignmentsTable tbody tr:hover {
            background-color: #e8f4f8;
        }

        .form-label {
            color: #000;
            font-weight: 700;
        }

        .card-title {
            color: #000;
            font-weight: 700;
        }

        .text-muted {
            color: #666 !important;
        }
    </style>

    <script>
        let allAssignments = [];
        let allClasses = [];
        let allSections = [];
        let allExams = [];
        let allSubjects = [];
        let subjectRowCounter = 0;
        let currentSubjects = []; // Temporary array for modal
        let currentExamIdForSchedule = null; // Store current exam ID for schedule view

        // Get exam ID from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const examIdFromUrl = urlParams.get('exam_id');

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadClasses();
            loadExams();
            // Subjects will be loaded dynamically when class and section are selected
            
            // Load assignments for specific exam if exam_id is in URL, otherwise load all
            if (examIdFromUrl) {
                currentExamIdForSchedule = examIdFromUrl; // Store exam ID for schedule view
                loadAssignmentsByExam(examIdFromUrl);
            } else {
                loadAssignments();
            }
            
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 500);
        });

        /**
         * Load classes for dropdown
         */
        function loadClasses() {
            fetch('./manage_exam_assignments.php?action=get_classes')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        allClasses = data.data.map(cls => ({
                            id: cls.id,
                            name: cls.class_name
                        }));
                        populateClassDropdowns();
                    } else {
                        allClasses = [];
                        populateClassDropdowns();
                    }
                })
                .catch(error => {
                    console.error('Error loading classes:', error.message);
                    allClasses = [];
                    populateClassDropdowns();
                });
        }

        /**
         * Populate class dropdowns
         */
        function populateClassDropdowns() {
            const filterSelect = document.getElementById('filterClass');
            const modalSelect = document.getElementById('classSelect');
            const editModalSelect = document.getElementById('editClassSelect');

            // Clear options
            filterSelect.innerHTML = '<option value="">All Classes</option>';
            modalSelect.innerHTML = '<option value="">-- Choose Class --</option>';
            if (editModalSelect) {
                editModalSelect.innerHTML = '<option value="">-- Choose Class --</option>';
            }

            allClasses.forEach(cls => {
                const option1 = new Option(cls.name, cls.id);
                const option2 = new Option(cls.name, cls.id);
                filterSelect.add(option1);
                modalSelect.add(option2);
                if (editModalSelect) {
                    const option3 = new Option(cls.name, cls.id);
                    editModalSelect.add(option3);
                }
            });
        }

        /**
         * Load sections based on selected class
         */
        function loadSections() {
            const classId = document.getElementById('classSelect').value;
            const sectionSelect = document.getElementById('sectionSelect');
            
            if (!classId) {
                sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
                return;
            }

            fetch('./manage_exam_assignments.php?action=get_sections&class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sections = data.data.map(sec => ({
                            id: sec.id,
                            name: sec.name
                        }));
                        
                        sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
                        sections.forEach(section => {
                            const option = new Option(section.name, section.id);
                            sectionSelect.add(option);
                        });
                    } else {
                        sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                    sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
                });
        }

        /**
         * Load exams for dropdown
         */
        function loadExams() {
            fetch('./manage_exams.php?action=get')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && Array.isArray(data)) {
                        allExams = data;
                    } else if (data.data && Array.isArray(data.data)) {
                        allExams = data.data;
                    } else {
                        allExams = [];
                    }
                    populateExamDropdowns();
                })
                .catch(error => {
                    console.error('Error loading exams:', error.message);
                    allExams = [];
                    populateExamDropdowns();
                });
        }

        /**
         * Populate exam dropdowns
         */
        function populateExamDropdowns() {
            const filterSelect = document.getElementById('filterExam');
            const modalSelect = document.getElementById('examSelect');
            const editModalSelect = document.getElementById('editExamSelect');

            filterSelect.innerHTML = '<option value="">All Exams</option>';
            modalSelect.innerHTML = '<option value="">-- Choose Exam --</option>';
            if (editModalSelect) {
                editModalSelect.innerHTML = '<option value="">-- Choose Exam --</option>';
            }

            allExams.forEach(exam => {
                const option1 = new Option(exam.exam_name, exam.id);
                const option2 = new Option(exam.exam_name, exam.id);
                filterSelect.add(option1);
                modalSelect.add(option2);
                if (editModalSelect) {
                    const option3 = new Option(exam.exam_name, exam.id);
                    editModalSelect.add(option3);
                }
            });
        }

        /**
         * Update exam ID when exam is selected
         */
        function updateExamId() {
            const selectElement = document.getElementById('examSelect');
            document.getElementById('examSelectId').value = selectElement.value;
        }

        /**
         * Load assignments
         */
        function loadAssignments() {
            fetch('./manage_exam_assignments.php?action=get_assignments')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        allAssignments = data.data;
                        displayAssignments(allAssignments);
                    } else {
                        allAssignments = [];
                        displayAssignments([]);
                    }
                })
                .catch(error => {
                    console.error('Error loading assignments:', error.message);
                    allAssignments = [];
                    displayAssignments([]);
                });
        }
        
        /**
         * Load assignments by exam ID
         */
        function loadAssignmentsByExam(exam_id) {
            fetch('./manage_exam_assignments.php?action=get_assignments&exam_id=' + exam_id)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        allAssignments = data.data;
                        displayAssignments(allAssignments);
                    } else {
                        allAssignments = [];
                        displayAssignments([]);
                    }
                })
                .catch(error => {
                    console.error('Error loading assignments:', error.message);
                    allAssignments = [];
                    displayAssignments([]);
                });
        }

        /**
         * Display assignments in table - Show ALL subjects for each class
         */
        function displayAssignments(assignments) {
            const tbody = document.getElementById('assignmentsBody');
            const noDataMsg = document.getElementById('noDataMsg');

            if (assignments.length === 0) {
                tbody.innerHTML = '';
                noDataMsg.style.display = 'block';
                noDataMsg.innerHTML = 'No assignments found. <a href="#" onclick="openAssignModal(); return false;">Create one now</a>';
                return;
            }

            noDataMsg.style.display = 'none';
            tbody.innerHTML = '';

            // Display ALL assignments (one row per subject)
            assignments.forEach(assignment => {
                const row = document.createElement('tr');
                const statusBadge = assignment.status === 1 ? 
                    '<span class="badge badge-status-active">Active</span>' : 
                    '<span class="badge badge-status-inactive">Inactive</span>';

                row.innerHTML = `
                    <td><strong>${escapeHtml(assignment.class_name)}</strong></td>
                    <td>${escapeHtml(assignment.section_name)}</td>
                    <td>${escapeHtml(assignment.subject_name)}</td>
                    <td>${escapeHtml(assignment.exam_name)}</td>
                    <td>${formatDate(assignment.exam_date)}</td>
                    <td>${assignment.total_marks}</td>
                    <td>${assignment.passing_marks}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editAssignment(${assignment.exam_class_id}, ${assignment.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAssignmentSubject(${assignment.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        /**
         * Open modal for adding new assignment
         */
        function openAssignModal() {
            document.getElementById('examClassId').value = '';
            document.getElementById('assignForm').reset();
            document.getElementById('applyToAllSections').checked = false; // Reset checkbox
            document.getElementById('sectionSelect').disabled = false; // Enable section select
            document.getElementById('assignModalLabel').innerHTML = 
                '<i class="fa fa-tasks"></i> Assign Exam to Class & Add Subjects';
            
            // Auto-select first exam if available
            if (allExams.length > 0) {
                const selectedExam = allExams[0];
                document.getElementById('examSelect').value = selectedExam.id;
                document.getElementById('examSelectId').value = selectedExam.id;
            }
            
            // Clear subject rows
            currentSubjects = [];
            subjectRowCounter = 0;
            document.getElementById('subjectsBody').innerHTML = '';
            document.getElementById('noSubjectsMsg').style.display = 'block';
            
            const modal = new bootstrap.Modal(document.getElementById('assignModal'));
            modal.show();
        }

        /**
         * Edit single subject assignment
         */
        function editAssignment(exam_class_id, subject_id) {
            // Find the assignment with this exam_class_id and subject_id
            const assignment = allAssignments.find(a => a.exam_class_id == exam_class_id && a.id == subject_id);
            
            if (!assignment) {
                alert('Assignment subject not found');
                return;
            }

            // Set hidden IDs
            document.getElementById('editExamClassId').value = exam_class_id;
            document.getElementById('editSubjectId').value = subject_id;

            // Set read-only display fields
            document.getElementById('editClassName').textContent = assignment.class_name;
            document.getElementById('editSectionName').textContent = assignment.section_name;
            document.getElementById('editExamName').textContent = assignment.exam_name;
            document.getElementById('editSubjectName').textContent = assignment.subject_name;

            // Set editable fields
            document.getElementById('editExamDate').value = assignment.exam_date;
            document.getElementById('editExamTime').value = assignment.exam_time;
            document.getElementById('editTotalMarks').value = assignment.total_marks;
            document.getElementById('editPassingMarks').value = assignment.passing_marks;
            document.getElementById('editStatus').value = assignment.status;

            // Show edit modal
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        /**
         * Update single subject details only
         */
        function updateSingleSubject() {
            const form = document.getElementById('editForm');
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                alert('Please fill in all required fields');
                return;
            }

            const subjectData = {
                subject_id: document.getElementById('editSubjectId').value,
                exam_class_id: document.getElementById('editExamClassId').value,
                exam_date: document.getElementById('editExamDate').value,
                exam_time: document.getElementById('editExamTime').value,
                total_marks: document.getElementById('editTotalMarks').value,
                passing_marks: document.getElementById('editPassingMarks').value,
                status: document.getElementById('editStatus').value
            };

            // Call API to update subject
            fetch('./manage_exam_assignments.php?action=update_subject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subjectData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Subject updated successfully');
                    
                    // Close modal
                    const modalElement = document.getElementById('editModal');
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    
                    // Reload page after 500ms
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error updating subject: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating subject: ' + error.message);
            });
        }

        /**
         * Delete assignment subject
         */
        function deleteAssignmentSubject(subject_id) {
            if (confirm('Are you sure you want to delete this subject assignment?')) {
                fetch('./manage_exam_assignments.php?action=delete_subject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ subject_id: subject_id })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Subject deleted successfully');
                        // Reload page after 500ms
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        alert('Error deleting subject: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting subject: ' + error.message);
                });
            }
        }

        /**
         * Load all subjects for the modal
         */
        function loadAllSubjects() {
            fetch('./manage_exam_assignments.php?action=get_subjects')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Remove duplicates by subject ID
                        const uniqueSubjects = [];
                        const seenIds = new Set();
                        data.data.forEach(subject => {
                            if (!seenIds.has(subject.id)) {
                                uniqueSubjects.push(subject);
                                seenIds.add(subject.id);
                            }
                        });
                        allSubjects = uniqueSubjects;
                    } else {
                        allSubjects = [];
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects:', error.message);
                    allSubjects = [];
                });
        }

        /**
         * Load subjects assigned to specific class and section
         */
        function loadSubjectsByClassAndSection(classId, sectionId) {
            if (!classId || !sectionId) {
                allSubjects = [];
                return;
            }

            fetch(`./manage_exam_assignments.php?action=get_subjects_by_class_section&class_id=${classId}&section_id=${sectionId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        allSubjects = data.data;
                    } else {
                        allSubjects = [];
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects by class/section:', error.message);
                    allSubjects = [];
                });
        }

        /**
         * Add subject row in the modal
         */
        function addSubjectRow() {
            // Check if subjects loaded properly
            if (!allSubjects || allSubjects.length === 0) {
                alert('Please load subjects first. Subjects may not be available.');
                return;
            }

            // Get already added subjects
            const addedSubjectIds = currentSubjects.map(s => s.subject_id).map(Number);
            const availableSubjects = allSubjects.filter(s => !addedSubjectIds.includes(Number(s.id)));
            
            // Check if all subjects have been added
            if (availableSubjects.length === 0) {
                alert('All available subjects have been added');
                return;
            }

            const rowId = 'subject_row_' + (++subjectRowCounter);
            const tbody = document.getElementById('subjectsBody');
            const row = document.createElement('tr');
            row.id = rowId;

            let subjectOptions = '<option value="">-- Choose Subject --</option>';
            availableSubjects.forEach(subject => {
                subjectOptions += `<option value="${subject.id}">${subject.name}</option>`;
            });

            row.innerHTML = `
                <td>
                    <select class="form-select form-select-sm" name="subject_id[]" onchange="updateCurrentSubjects()" style="color: #000;">
                        ${subjectOptions}
                    </select>
                </td>
                <td>
                    <input type="date" class="form-control form-control-sm" name="exam_date[]" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <input type="time" class="form-control form-control-sm" name="exam_time[]" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="total_marks[]" placeholder="e.g. 100" min="1" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="passing_marks[]" placeholder="e.g. 33" min="0" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <select class="form-select form-select-sm" name="status[]" onchange="updateCurrentSubjects()" style="color: #000;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSubjectRow('${rowId}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            document.getElementById('noSubjectsMsg').style.display = 'none';
        }

        /**
         * Remove subject row
         */
        function removeSubjectRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                updateCurrentSubjects();
                
                // Show empty message if no subjects
                if (document.getElementById('subjectsBody').children.length === 0) {
                    document.getElementById('noSubjectsMsg').style.display = 'block';
                }
            }
        }

        /**
         * Update current subjects array from form
         */
        function updateCurrentSubjects() {
            currentSubjects = [];
            const rows = document.getElementById('subjectsBody').children;
            
            Array.from(rows).forEach(row => {
                const subjectId = row.querySelector('select[name="subject_id[]"]')?.value;
                const examDate = row.querySelector('input[name="exam_date[]"]')?.value;
                const examTime = row.querySelector('input[name="exam_time[]"]')?.value;
                const totalMarks = row.querySelector('input[name="total_marks[]"]')?.value;
                const passingMarks = row.querySelector('input[name="passing_marks[]"]')?.value;
                const status = row.querySelector('select[name="status[]"]')?.value;

                if (subjectId && examDate && examTime && totalMarks && passingMarks) {
                    currentSubjects.push({
                        subject_id: subjectId,
                        exam_date: examDate,
                        exam_time: examTime,
                        total_marks: totalMarks,
                        passing_marks: passingMarks,
                        status: status
                    });
                }
            });
        }

        /**
         * Save assignment with all subjects
         */
        function saveAssignment() {
            const form = document.getElementById('assignForm');
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                alert('Please fill in all required class details');
                return;
            }

            // Update subjects from current form
            updateCurrentSubjects();
            
            if (currentSubjects.length === 0) {
                alert('Please add at least one subject');
                return;
            }

            const examId = document.getElementById('examSelectId').value;
            const classId = document.getElementById('classSelect').value;
            const sectionId = document.getElementById('sectionSelect').value;
            const applyToAll = document.getElementById('applyToAllSections').checked;
            
            // If apply to all is not checked, section must be selected
            if (!applyToAll && !sectionId) {
                alert('Please select a section or check "Apply to All Sections"');
                return;
            }
            
            const examClassData = {
                exam_id: examId,
                class_id: classId,
                section_id: applyToAll ? null : sectionId, // null means apply to all
                subjects: currentSubjects,
                apply_to_all_sections: applyToAll
            };

            // Call API to save assignment
            fetch('./manage_exam_assignments.php?action=save_assignment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(examClassData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const errorData = JSON.parse(text);
                            throw new Error(`HTTP ${response.status}: ${errorData.message || text}`);
                        } catch (e) {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const message = applyToAll 
                        ? `Assignment with ${currentSubjects.length} subject(s) saved successfully for all sections of the class`
                        : `Assignment with ${currentSubjects.length} subject(s) saved successfully`;
                    alert(message);
                    
                    // Close modal
                    const modalElement = document.getElementById('assignModal');
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    
                    // Reload page after 500ms
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error saving assignment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Error saving assignment: ' + error.message);
            });
        }

        /**
         * Apply filters
         */
        function applyFilters() {
            const classId = document.getElementById('filterClass').value;
            const sectionId = document.getElementById('filterSection').value;
            const examId = document.getElementById('filterExam').value;

            // Update the global exam ID for schedule view
            if (examId) {
                currentExamIdForSchedule = examId;
            } else {
                currentExamIdForSchedule = examIdFromUrl || null;
            }

            let filtered = allAssignments;

            // Filter by class
            if (classId) {
                filtered = filtered.filter(assignment => {
                    const assignmentClass = allClasses.find(c => c.id == classId);
                    return assignmentClass && assignment.class_name === assignmentClass.name;
                });
            }
            
            // Filter by section
            if (sectionId) {
                filtered = filtered.filter(assignment => {
                    const sectionOption = Array.from(document.querySelectorAll('#filterSection option')).find(opt => opt.value === sectionId);
                    const sectionName = sectionOption ? sectionOption.textContent : '';
                    return assignment.section_name === sectionName;
                });
            }
            
            // Filter by exam
            if (examId) {
                filtered = filtered.filter(assignment => {
                    const assignmentExam = allExams.find(e => e.id == examId);
                    return assignmentExam && assignment.exam_name === assignmentExam.exam_name;
                });
            }

            displayAssignments(filtered);
        }

        /**
         * Reset filters
         */
        function resetFilters() {
            document.getElementById('filterClass').value = '';
            document.getElementById('filterSection').value = '';
            document.getElementById('filterExam').value = '';
            document.getElementById('filterSection').innerHTML = '<option value="">All Sections</option>';
            
            // Reset exam ID for schedule view to URL parameter (if any)
            currentExamIdForSchedule = examIdFromUrl || null;
            
            loadAssignments();
        }

        /**
         * Load sections for class filter dropdown
         */
        function loadFilterSections() {
            const classId = document.getElementById('filterClass').value;
            const sectionSelect = document.getElementById('filterSection');
            
            sectionSelect.innerHTML = '<option value="">All Sections</option>';
            
            if (!classId) {
                return;
            }

            fetch('./manage_exam_assignments.php?action=get_sections&class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(section => {
                            const option = new Option(section.name, section.id);
                            sectionSelect.add(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                });
        }

        /**
         * View complete schedule
         */
        function viewSchedule() {
            // Use the current exam ID from filter or URL
            let examId = currentExamIdForSchedule;

            if (!examId) {
                alert('Please select an exam from the filter first');
                return;
            }

            // Navigate to schedule page with exam_id parameter
            window.location.href = './schedule.php?exam_id=' + examId;
        }

        /**
         * Download datesheet
         */
        function downloadDatesheet() {
            // Use the current exam ID from filter or URL
            let examId = currentExamIdForSchedule;

            if (!examId) {
                alert('Please select an exam from the filter first');
                return;
            }

            // Navigate to schedule page to view before downloading
            window.location.href = './schedule.php?exam_id=' + examId;
        }

        /**
         * Format date
         */
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        /**
         * Toggle apply to all sections checkbox
         */
        function toggleApplyToAllSections() {
            const checkbox = document.getElementById('applyToAllSections');
            const sectionSelect = document.getElementById('sectionSelect');
            
            if (checkbox.checked) {
                sectionSelect.disabled = true;
                sectionSelect.value = '';
                // Load all subjects for the selected class (without specific section)
                loadAllSubjectsByClass(document.getElementById('classSelect').value);
            } else {
                sectionSelect.disabled = false;
                sectionSelect.value = '';
                // Reset subjects
                allSubjects = [];
                loadSubjectsByClassAndSection(document.getElementById('classSelect').value, '');
            }
        }

        /**
         * Check if apply all sections checkbox should be enabled
         */
        function checkApplyAllStatus() {
            const classId = document.getElementById('classSelect').value;
            const checkbox = document.getElementById('applyToAllSections');
            
            if (!classId) {
                checkbox.disabled = true;
                checkbox.checked = false;
            } else {
                checkbox.disabled = false;
            }
        }

        /**
         * Load all subjects by class (regardless of section)
         */
        function loadAllSubjectsByClass(classId) {
            if (!classId) {
                allSubjects = [];
                return;
            }

            fetch(`./manage_exam_assignments.php?action=get_subjects_by_class&class_id=${classId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        allSubjects = data.data;
                    } else {
                        allSubjects = [];
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects by class:', error.message);
                    allSubjects = [];
                });
        }

        /**
         * Escape HTML special characters
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>

</html>
