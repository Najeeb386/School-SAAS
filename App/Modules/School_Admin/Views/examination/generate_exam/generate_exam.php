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
                                    <h3 class="mb-0">Generate Exam</h3>
                                    <button class="btn btn-primary" onclick="openExamModal()">
                                        <i class="fa fa-plus"></i> Create New Exam
                                    </button>
                                </div>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb p-0 bg-transparent">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Overview</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Generate Exam</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3" style="color: #000; font-weight: 700;">Filter Exams</h5>
                                        <div class="row">
                                            <div class="col-md-2 mb-3">
                                                <label for="filterExamType" class="form-label fw-bold" style="color: #000;">Exam Type</label>
                                                <select class="form-select" id="filterExamType" onchange="applyFilters()" style="color: #000;">
                                                    <option value="">All Types</option>
                                                    <option value="midterm">Midterm</option>
                                                    <option value="final">Final</option>
                                                    <option value="annual">Annual</option>
                                                    <option value="board_prep">Board Prep</option>
                                                    <option value="monthly">Monthly</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label for="filterSession" class="form-label fw-bold" style="color: #000;">Session</label>
                                                <select class="form-select" id="filterSession" onchange="applyFilters()" style="color: #000;">
                                                    <option value="">All Sessions</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label for="filterStatus" class="form-label fw-bold" style="color: #000;">Status</label>
                                                <select class="form-select" id="filterStatus" onchange="applyFilters()" style="color: #000;">
                                                    <option value="">All Status</option>
                                                    <option value="draft">Draft</option>
                                                    <option value="published">Published</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label for="filterStartDate" class="form-label fw-bold" style="color: #000;">Start Date</label>
                                                <input type="date" class="form-control" id="filterStartDate" onchange="applyFilters()" style="color: #000;">
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label for="filterEndDate" class="form-label fw-bold" style="color: #000;">End Date</label>
                                                <input type="date" class="form-control" id="filterEndDate" onchange="applyFilters()" style="color: #000;">
                                            </div>

                                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                                <button class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                                                    <i class="fa fa-refresh"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exams Table -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3" style="color: #000; font-weight: 700;">Exams Schedule</h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered" id="examsTable">
                                                <thead class="table-light">
                                                    <tr style="color: #000;">
                                                        <th>Exam Name</th>
                                                        <th>Type</th>
                                                        <th>Session</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="examsBody">
                                                    <!-- Will be populated by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="text-muted text-center mt-3" id="noDataMsg" style="color: #666;">Loading exams...</p>
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

    <!-- Create/Edit Exam Modal -->
    <div class="modal fade" id="examModal" tabindex="-1" role="dialog" aria-labelledby="examModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="examModalLabel">
                        <i class="fa fa-file-text"></i> Create/Edit Exam
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="examForm" class="needs-validation">
                        <input type="hidden" id="examId" name="id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="examName" class="form-label fw-bold" style="color: #000; font-size: 15px;">Exam Name</label>
                                <input type="text" class="form-control form-control-lg" id="examName" name="exam_name" 
                                       placeholder="e.g., Mathematics - 2026" required style="border: 2px solid #e9ecef; color: #000;">
                                <small class="text-muted" style="color: #666;">Enter a unique exam name</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="examType" class="form-label fw-bold" style="color: #000; font-size: 15px;">Exam Type</label>
                                <select class="form-select form-control-lg" id="examType" name="exam_type" required style="border: 2px solid #e9ecef; color: #000;">
                                    <option value="">Select Exam Type</option>
                                    <option value="midterm">Midterm</option>
                                    <option value="final">Final</option>
                                    <option value="annual">Annual</option>
                                    <option value="board_prep">Board Prep</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sessionId" class="form-label fw-bold" style="color: #000; font-size: 15px;">Session</label>
                                <select class="form-select form-control-lg" id="sessionId" name="session_id" required style="border: 2px solid #e9ecef; color: #000;">
                                    <option value="">Select Session</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold" style="color: #000; font-size: 15px;">Status</label>
                                <select class="form-select form-control-lg" id="status" name="status" required style="border: 2px solid #e9ecef; color: #000;">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="startDate" class="form-label fw-bold" style="color: #000; font-size: 15px;">Start Date</label>
                                <input type="date" class="form-control form-control-lg" id="startDate" name="start_date" 
                                       required style="border: 2px solid #e9ecef; color: #000;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label fw-bold" style="color: #000; font-size: 15px;">End Date</label>
                                <input type="date" class="form-control form-control-lg" id="endDate" name="end_date" 
                                       required style="border: 2px solid #e9ecef; color: #000;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold" style="color: #000; font-size: 15px;">Description <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control form-control-lg" id="description" name="description" rows="3" 
                                      placeholder="Add any additional notes about this exam" style="border: 2px solid #e9ecef; color: #000;"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveExam()">
                        <i class="fa fa-save"></i> Save Exam
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
        #examsTable {
            color: #000;
        }

        #examsTable thead {
            background-color: #f8f9fa;
            color: #000;
            font-weight: 600;
        }

        #examsTable thead th {
            color: #000;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6;
        }

        #examsTable tbody td {
            color: #000;
            font-weight: 500;
            vertical-align: middle;
        }

        #examsTable tbody strong {
            color: #000;
            font-weight: 700;
        }

        .exam-type-midterm {
            background-color: #fff3cd;
            color: #664d03;
        }

        .exam-type-final {
            background-color: #f8d7da;
            color: #721c24;
        }

        .exam-type-annual {
            background-color: #d4edda;
            color: #155724;
        }

        .exam-type-board_prep {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .exam-type-monthly {
            background-color: #e7f3ff;
            color: #0056b3;
        }

        .status-draft {
            background-color: #e9ecef;
            color: #495057;
        }

        .status-published {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        #examsTable tbody tr:hover {
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
        let currentActiveSession = null;

        // Load exams and sessions on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSessions();
            loadExams();
            
            setTimeout(function() {
                var loader = document.querySelector('.loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }, 500);
        });

        /**
         * Load sessions for dropdown
         */
        function loadSessions() {
            fetch('./manage_exams.php?action=sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Find current active session (usually the first one or marked as active)
                        const sessions = data.data;
                        if (sessions.length > 0) {
                            currentActiveSession = sessions[0];
                        }
                        populateSessionDropdowns(sessions);
                    }
                })
                .catch(error => console.error('Error loading sessions:', error));
        }

        /**
         * Populate session dropdowns
         */
        function populateSessionDropdowns(sessions) {
            const filterSelect = document.getElementById('filterSession');
            const formSelect = document.getElementById('sessionId');

            // Clear existing options (keep first one)
            while (filterSelect.options.length > 1) {
                filterSelect.remove(1);
            }
            while (formSelect.options.length > 1) {
                formSelect.remove(1);
            }

            sessions.forEach(session => {
                const option1 = new Option(session.name, session.id);
                const option2 = new Option(session.name, session.id);
                filterSelect.add(option1);
                formSelect.add(option2);
            });
        }

        /**
         * Load all exams
         */
        function loadExams() {
            fetch('./manage_exams.php?action=get')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayExams(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error loading exams:', error);
                    document.getElementById('noDataMsg').innerHTML = 'Error loading exams. Please refresh the page.';
                });
        }

        /**
         * Display exams in table
         */
        function displayExams(exams) {
            const tbody = document.getElementById('examsBody');
            const noDataMsg = document.getElementById('noDataMsg');

            if (exams.length === 0) {
                tbody.innerHTML = '';
                noDataMsg.style.display = 'block';
                noDataMsg.innerHTML = 'No exams found. <a href="#" onclick="openExamModal(); return false;">Create one now</a>';
                return;
            }

            noDataMsg.style.display = 'none';
            tbody.innerHTML = '';

            exams.forEach(exam => {
                const row = document.createElement('tr');
                const typeClass = 'exam-type-' + exam.exam_type;
                const statusClass = 'status-' + exam.status;
                const statusBadge = `<span class="badge ${statusClass}" style="padding: 6px 10px; font-weight: 600;">${capitalize(exam.status)}</span>`;
                
                const typeDisplay = exam.exam_type.replace(/_/g, ' ').toUpperCase();

                row.innerHTML = `
                    <td><strong>${escapeHtml(exam.exam_name)}</strong></td>
                    <td><span class="badge ${typeClass}" style="padding: 6px 10px; font-weight: 600;">${typeDisplay}</span></td>
                    <td>${escapeHtml(exam.session_name || '-')}</td>
                    <td>${formatDate(exam.start_date)}</td>
                    <td>${formatDate(exam.end_date)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editExam(${exam.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteExam(${exam.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        /**
         * Apply filters
         */
        function applyFilters() {
            const examType = document.getElementById('filterExamType').value;
            const sessionId = document.getElementById('filterSession').value;
            const status = document.getElementById('filterStatus').value;
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;

            const filterData = {};
            if (examType) filterData.exam_type = examType;
            if (sessionId) filterData.session_id = parseInt(sessionId);
            if (status) filterData.status = status;
            if (startDate) filterData.start_date = startDate;
            if (endDate) filterData.end_date = endDate;

            fetch('./manage_exams.php?action=filter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filterData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayExams(data.data);
                    }
                })
                .catch(error => console.error('Error filtering exams:', error));
        }

        /**
         * Reset filters
         */
        function resetFilters() {
            document.getElementById('filterExamType').value = '';
            document.getElementById('filterSession').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            loadExams();
        }

        /**
         * Open modal for adding new exam
         */
        function openExamModal() {
            document.getElementById('examId').value = '';
            document.getElementById('examForm').reset();
            document.getElementById('examModalLabel').innerHTML = 
                '<i class="fa fa-file-text"></i> Create New Exam';
            document.getElementById('status').value = 'draft';
            
            // Auto-select current active session
            if (currentActiveSession) {
                document.getElementById('sessionId').value = currentActiveSession.id;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('examModal'));
            modal.show();
        }

        /**
         * Edit existing exam
         */
        function editExam(id) {
            fetch('./manage_exams.php?action=get')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const exam = data.data.find(item => item.id == id);
                        if (exam) {
                            document.getElementById('examId').value = exam.id;
                            document.getElementById('examName').value = exam.exam_name;
                            document.getElementById('examType').value = exam.exam_type;
                            document.getElementById('sessionId').value = exam.session_id;
                            document.getElementById('startDate').value = exam.start_date;
                            document.getElementById('endDate').value = exam.end_date;
                            document.getElementById('description').value = exam.description || '';
                            document.getElementById('status').value = exam.status || 'draft';
                            
                            document.getElementById('examModalLabel').innerHTML = 
                                '<i class="fa fa-file-text"></i> Edit Exam';
                            
                            const modal = new bootstrap.Modal(document.getElementById('examModal'));
                            modal.show();
                        }
                    }
                })
                .catch(error => {
                    alert('Error loading exam details');
                    console.error('Error:', error);
                });
        }

        /**
         * Save exam (both add and update)
         */
        function saveExam() {
            const form = document.getElementById('examForm');
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                alert('Please fill in all required fields correctly');
                return;
            }

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Console log for debugging
            console.log('Form data to submit:', data);

            const examId = document.getElementById('examId').value;
            const action = examId ? 'update' : 'add';
            const url = './manage_exams.php?action=' + action;

            console.log('Submitting to:', url);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(result => {
                    console.log('Result:', result);
                    if (result.success) {
                        alert(result.message);
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('examModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Reload exams
                        setTimeout(() => {
                            loadExams();
                        }, 500);
                    } else {
                        alert('Error: ' + (result.message || 'Unable to save exam'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error saving exam: ' + error.message);
                });
        }

        /**
         * Delete exam with confirmation
         */
        function deleteExam(id) {
            if (confirm('Are you sure you want to delete this exam? This action cannot be undone.')) {
                const data = { id: id };

                fetch('./manage_exams.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert(result.message);
                            loadExams();
                        } else {
                            alert('Error: ' + (result.message || 'Unable to delete exam'));
                        }
                    })
                    .catch(error => {
                        alert('Error deleting exam: ' + error);
                        console.error('Error:', error);
                    });
            }
        }

        /**
         * Format date to readable format
         */
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        /**
         * Capitalize first letter
         */
        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
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
