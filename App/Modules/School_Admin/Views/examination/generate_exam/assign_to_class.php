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

                        <!-- Filter Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="filterClass" class="form-label fw-bold" style="color: #000;">Class</label>
                                                <select class="form-select" id="filterClass" onchange="applyFilters()" style="color: #000;">
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
        <div class="modal-dialog modal-xl" role="document">
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
                                    <div class="col-md-6 mb-3">
                                        <label for="examSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Exam <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="examSelect" name="exam_id" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Exam --</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="classSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Class <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="classSelect" name="class_id" onchange="loadSections()" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Class --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="sectionSelect" class="form-label fw-bold" style="color: #000; font-size: 15px;">Select Section <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-lg" id="sectionSelect" name="section_id" required style="border: 2px solid #e9ecef; color: #000;">
                                            <option value="">-- Choose Section --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="examStartDate" class="form-label fw-bold" style="color: #000; font-size: 15px;">Exam Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg" id="examStartDate" name="start_date" required style="border: 2px solid #e9ecef; color: #000;">
                                        <small class="text-muted">When the exam period starts for this class</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="examEndDate" class="form-label fw-bold" style="color: #000; font-size: 15px;">Exam End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg" id="examEndDate" name="end_date" required style="border: 2px solid #e9ecef; color: #000;">
                                        <small class="text-muted">When the exam period ends for this class</small>
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

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadClasses();
            loadExams();
            loadAssignments();
            
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
            // TODO: Replace with actual API call to fetch classes
            // Placeholder data - will be replaced with real API
            allClasses = [
                { id: 1, name: 'Class 9' },
                { id: 2, name: 'Class 10' },
                { id: 3, name: 'Class 11' },
                { id: 4, name: 'Class 12' }
            ];
            
            populateClassDropdowns();
        }

        /**
         * Populate class dropdowns
         */
        function populateClassDropdowns() {
            const filterSelect = document.getElementById('filterClass');
            const modalSelect = document.getElementById('classSelect');

            // Clear options
            filterSelect.innerHTML = '<option value="">All Classes</option>';
            modalSelect.innerHTML = '<option value="">-- Choose Class --</option>';

            allClasses.forEach(cls => {
                const option1 = new Option(cls.name, cls.id);
                const option2 = new Option(cls.name, cls.id);
                filterSelect.add(option1);
                modalSelect.add(option2);
            });
        }

        /**
         * Load sections based on selected class
         */
        function loadSections() {
            const classId = document.getElementById('classSelect').value;
            
            if (!classId) {
                document.getElementById('sectionSelect').innerHTML = '<option value="">-- Choose Section --</option>';
                return;
            }

            // TODO: Replace with actual API call
            const sectionsByClass = {
                1: [
                    { id: 1, name: 'Section A' },
                    { id: 2, name: 'Section B' },
                    { id: 3, name: 'Section C' }
                ],
                2: [
                    { id: 4, name: 'Section A' },
                    { id: 5, name: 'Section B' }
                ],
                3: [
                    { id: 6, name: 'Section A' },
                    { id: 7, name: 'Section B' },
                    { id: 8, name: 'Section C' }
                ],
                4: [
                    { id: 9, name: 'Section A' },
                    { id: 10, name: 'Section B' }
                ]
            };

            const sections = sectionsByClass[classId] || [];
            const sectionSelect = document.getElementById('sectionSelect');
            
            sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';
            sections.forEach(section => {
                const option = new Option(section.name, section.id);
                sectionSelect.add(option);
            });
        }

        /**
         * Load exams for dropdown
         */
        function loadExams() {
            // TODO: Replace with actual API call to fetch exams from manage_exams.php
            // Placeholder data
            allExams = [
                { id: 1, exam_name: 'Mid Term Exam', exam_type: 'midterm', start_date: '2025-02-17', end_date: '2025-02-21', status: 'published' },
                { id: 2, exam_name: 'Final Exam', exam_type: 'final', start_date: '2025-04-15', end_date: '2025-04-25', status: 'published' }
            ];

            populateExamDropdowns();
        }

        /**
         * Populate exam dropdowns
         */
        function populateExamDropdowns() {
            const filterSelect = document.getElementById('filterExam');
            const modalSelect = document.getElementById('examSelect');

            filterSelect.innerHTML = '<option value="">All Exams</option>';
            modalSelect.innerHTML = '<option value="">-- Choose Exam --</option>';

            allExams.forEach(exam => {
                const option1 = new Option(exam.exam_name, exam.id);
                const option2 = new Option(exam.exam_name, exam.id);
                filterSelect.add(option1);
                modalSelect.add(option2);
            });
        }

        /**
         * Load assignments
         */
        function loadAssignments() {
            // TODO: Replace with actual API call
            // Placeholder data
            allAssignments = [
                {
                    id: 1,
                    class_name: 'Class 9',
                    section_name: 'Section A',
                    subject_name: 'Mathematics',
                    exam_name: 'Mid Term Exam',
                    exam_date: '2025-02-17',
                    total_marks: 100,
                    passing_marks: 33,
                    status: 'active'
                },
                {
                    id: 2,
                    class_name: 'Class 9',
                    section_name: 'Section A',
                    subject_name: 'English',
                    exam_name: 'Mid Term Exam',
                    exam_date: '2025-02-18',
                    total_marks: 100,
                    passing_marks: 33,
                    status: 'active'
                }
            ];

            displayAssignments(allAssignments);
        }

        /**
         * Display assignments in table
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

            assignments.forEach(assignment => {
                const row = document.createElement('tr');
                const statusBadge = assignment.status === 'active' ? 
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
                        <button class="btn btn-sm btn-warning" onclick="editAssignment(${assignment.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAssignment(${assignment.id})" title="Delete">
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
            document.getElementById('assignModalLabel').innerHTML = 
                '<i class="fa fa-tasks"></i> Assign Exam to Class & Add Subjects';
            
            // Load all subjects for the modal
            loadAllSubjects();
            
            // Clear subject rows
            currentSubjects = [];
            subjectRowCounter = 0;
            document.getElementById('subjectsBody').innerHTML = '';
            document.getElementById('noSubjectsMsg').style.display = 'block';
            
            const modal = new bootstrap.Modal(document.getElementById('assignModal'));
            modal.show();
        }

        /**
         * Edit existing assignment
         */
        function editAssignment(id) {
            const assignment = allAssignments.find(a => a.id == id);
            if (assignment) {
                document.getElementById('examClassId').value = id;
                // TODO: Load assignment data and populate form
                
                document.getElementById('assignModalLabel').innerHTML = 
                    '<i class="fa fa-tasks"></i> Edit Exam Assignment';
                
                const modal = new bootstrap.Modal(document.getElementById('assignModal'));
                modal.show();
            }
        }

        /**
         * Load all subjects for the modal
         */
        function loadAllSubjects() {
            // TODO: Replace with actual API call
            allSubjects = [
                { id: 1, name: 'Mathematics' },
                { id: 2, name: 'English' },
                { id: 3, name: 'Science' },
                { id: 4, name: 'Social Studies' },
                { id: 5, name: 'Physics' },
                { id: 6, name: 'Chemistry' },
                { id: 7, name: 'Biology' }
            ];
        }

        /**
         * Add subject row in the modal
         */
        function addSubjectRow() {
            if (currentSubjects.length >= allSubjects.length) {
                alert('All subjects already added');
                return;
            }

            const rowId = 'subject_row_' + (++subjectRowCounter);
            const tbody = document.getElementById('subjectsBody');
            const row = document.createElement('tr');
            row.id = rowId;

            // Get available subjects (exclude already added ones)
            const addedSubjectIds = currentSubjects.map(s => s.subject_id);
            const availableSubjects = allSubjects.filter(s => !addedSubjectIds.includes(s.id));

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
                    <input type="number" class="form-control form-control-sm" name="total_marks[]" placeholder="e.g. 100" min="1" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="passing_marks[]" placeholder="e.g. 33" min="0" onchange="updateCurrentSubjects()" style="color: #000;">
                </td>
                <td>
                    <select class="form-select form-select-sm" name="status[]" onchange="updateCurrentSubjects()" style="color: #000;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
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
                const totalMarks = row.querySelector('input[name="total_marks[]"]')?.value;
                const passingMarks = row.querySelector('input[name="passing_marks[]"]')?.value;
                const status = row.querySelector('select[name="status[]"]')?.value;

                if (subjectId && examDate && totalMarks && passingMarks) {
                    currentSubjects.push({
                        subject_id: subjectId,
                        exam_date: examDate,
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

            if (currentSubjects.length === 0) {
                alert('Please add at least one subject');
                return;
            }

            const examClassData = {
                exam_id: document.getElementById('examSelect').value,
                class_id: document.getElementById('classSelect').value,
                section_id: document.getElementById('sectionSelect').value,
                start_date: document.getElementById('examStartDate').value,
                end_date: document.getElementById('examEndDate').value,
                subjects: currentSubjects
            };

            // TODO: Replace with actual API call to save exam_class and subjects
            console.log('Assignment data to save:', examClassData);
            alert('Assignment with ' + currentSubjects.length + ' subject(s) saved successfully');
            
            // Close modal
            const modalElement = document.getElementById('assignModal');
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
            
            loadAssignments();
        }

        /**
         * Delete assignment
         */
        function deleteAssignment(id) {
            if (confirm('Are you sure you want to delete this assignment?')) {
                // TODO: Replace with actual API call
                alert('Assignment deleted successfully');
                loadAssignments();
            }
        }

        /**
         * Apply filters
         */
        function applyFilters() {
            const classId = document.getElementById('filterClass').value;
            const sectionId = document.getElementById('filterSection').value;
            const examId = document.getElementById('filterExam').value;

            let filtered = allAssignments;

            if (classId) {
                // TODO: Filter by class
            }
            if (sectionId) {
                // TODO: Filter by section
            }
            if (examId) {
                // TODO: Filter by exam
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
            loadAssignments();
        }

        /**
         * View complete schedule
         */
        function viewSchedule() {
            alert('View Schedule - Feature coming soon');
            // TODO: Implement schedule view
        }

        /**
         * Download datesheet
         */
        function downloadDatesheet() {
            alert('Download Datesheet - Feature coming soon');
            // TODO: Implement datesheet download
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
