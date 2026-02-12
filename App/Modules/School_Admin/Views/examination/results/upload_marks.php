<?php
/**
 * Marks Upload Management - School Admin
 * User must be logged in as School Admin to access this page
 */
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Core/database.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Marks Upload - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Upload exam marks and manage results" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- app favicon -->
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <!-- plugin stylesheets -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css" />
    <!-- app style -->
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css" />
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .filters-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 20px;
        }
        .filters-row .form-group {
            margin-bottom: 0;
            min-width: 180px;
        }
        .filters-row .form-group label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }
        .filters-row .form-control {
            font-size: 0.875rem;
            border-radius: 4px;
        }
        .filters-row .btn {
            gap: 6px;
            min-width: 110px;
        }
        
        .tabs-wrapper {
            margin-bottom: 24px;
            border-bottom: 2px solid #e9ecef;
        }
        .nav-tabs {
            border: none;
            gap: 30px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 0;
            position: relative;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link:hover {
            color: #495057;
            border-bottom-color: #0066cc;
        }
        .nav-tabs .nav-link.active {
            color: #0066cc;
            border-bottom-color: #0066cc;
            background: none;
        }

        .exam-status-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-draft {
            background-color: #f0f0f0;
            color: #666;
        }
        .badge-published {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .upload-progress {
            font-size: 0.875rem;
            margin: 8px 0;
        }
        .progress {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
        }
        .progress-bar {
            border-radius: 3px;
            background: linear-gradient(90deg, #0066cc, #004299);
        }

        .table-actions .btn {
            margin-right: 6px;
            font-size: 0.875rem;
            padding: 6px 12px;
        }
        .table-actions {
            white-space: nowrap;
        }

        .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            border-radius: 6px;
        }

        .no-data-message {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .no-data-message i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .no-data-message p {
            font-size: 0.95rem;
        }

        .filter-button-group {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }

        table.table tbody tr {
            transition: background-color 0.2s ease;
        }
        table.table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .date-range-group {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .date-range-group .form-group {
            min-width: 160px;
        }

        .marks-upload-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            padding: 16px;
            border-radius: 6px;
            border-left: 4px solid #0066cc;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .stat-card h6 {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0066cc;
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
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            <!-- end pre-loader -->
            

            <!-- begin app-container -->
            <div class="app-container">
                <!-- begin app-navbar -->
         
                <!-- end app-navbar -->

                <!-- begin app-main -->
                <div class="" id="main">
                    <!-- begin container-fluid -->
                    <div class="container-fluid">
                        <!-- Page Header -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-3"><i class="fas fa-file-upload mr-2"></i>Marks Upload</h3>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb p-0 bg-transparent">
                                                <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                                <li class="breadcrumb-item"><a href="../examination.php">Examination</a></li>
                                                <li class="breadcrumb-item active" aria-current="page">Marks Upload</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#bulkUploadModal">
                                        <i class="fas fa-cloud-upload-alt mr-2"></i>Bulk Upload
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="marks-upload-stats">
                            <div class="stat-card">
                                <h6>Total Exams</h6>
                                <div class="stat-value" id="totalExams">0</div>
                            </div>
                            <div class="stat-card" style="border-left-color: #28a745;">
                                <h6>Marks Uploaded</h6>
                                <div class="stat-value" style="color: #28a745;" id="marksUploaded">0</div>
                            </div>
                            <div class="stat-card" style="border-left-color: #ffc107;">
                                <h6>Pending Upload</h6>
                                <div class="stat-value" style="color: #ffc107;" id="pendingUpload">0</div>
                            </div>
                            <div class="stat-card" style="border-left-color: #17a2b8;">
                                <h6>Completion Rate</h6>
                                <div class="stat-value" style="color: #17a2b8;" id="completionRate">0%</div>
                            </div>
                        </div>

                        <!-- Filters Card -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="mb-3" style="font-weight: 600; color: #333;">
                                    <i class="fas fa-filter mr-2"></i>Filters & Search
                                </h6>
                                <div class="filters-row">
                                    <div class="form-group">
                                        <label>Session</label>
                                        <select id="filter_session" class="form-control form-control-sm">
                                            <option value="">All Sessions</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Exam Type</label>
                                        <select id="filter_exam_type" class="form-control form-control-sm">
                                            <option value="">All Types</option>
                                            <option value="midterm">Midterm</option>
                                            <option value="final">Final</option>
                                            <option value="annual">Annual</option>
                                            <option value="board_prep">Board Prep</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Class</label>
                                        <select id="filter_class" class="form-control form-control-sm">
                                            <option value="">All Classes</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Section</label>
                                        <select id="filter_section" class="form-control form-control-sm">
                                            <option value="">All Sections</option>
                                        </select>
                                    </div>
                                    <div class="date-range-group">
                                        <div class="form-group">
                                            <label>From Date</label>
                                            <input type="date" id="filter_date_from" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group">
                                            <label>To Date</label>
                                            <input type="date" id="filter_date_to" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="filter-button-group">
                                        <button id="btnFilter" class="btn btn-sm btn-primary">
                                            <i class="fas fa-search mr-1"></i>Apply Filter
                                        </button>
                                        <button id="btnReset" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-redo mr-1"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="tabs-wrapper">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#currentExamsTab" role="tab">
                                        <i class="fas fa-calendar-check mr-2"></i>Current Exams
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#allExamsTab" role="tab">
                                        <i class="fas fa-list-ul mr-2"></i>All Exams
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Current Exams Tab -->
                            <div id="currentExamsTab" class="tab-pane fade show active">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover" id="currentExamsTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 5%;">#</th>
                                                        <th style="width: 12%;">Exam Name</th>
                                                        <th style="width: 10%;">Type</th>
                                                        <th style="width: 12%;">Class/Section</th>
                                                        <th style="width: 10%;">Start Date</th>
                                                        <th style="width: 10%;">End Date</th>
                                                        <th style="width: 8%;">Status</th>
                                                        <th style="width: 10%;">Progress</th>
                                                        <th style="width: 23%;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="currentExamsTbody">
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-5">
                                                            <div class="no-data-message">
                                                                <i class="fas fa-inbox"></i>
                                                                <p>No current exams found. Please adjust your filters.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Exams Tab -->
                            <div id="allExamsTab" class="tab-pane fade">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover" id="allExamsTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 5%;">#</th>
                                                        <th style="width: 12%;">Exam Name</th>
                                                        <th style="width: 10%;">Type</th>
                                                        <th style="width: 12%;">Class/Section</th>
                                                        <th style="width: 10%;">Start Date</th>
                                                        <th style="width: 10%;">End Date</th>
                                                        <th style="width: 8%;">Status</th>
                                                        <th style="width: 10%;">Progress</th>
                                                        <th style="width: 23%;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="allExamsTbody">
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-5">
                                                            <div class="no-data-message">
                                                                <i class="fas fa-inbox"></i>
                                                                <p>No exams found. Please adjust your filters.</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
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

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Bulk Upload Marks
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Bulk Upload Format:</strong> Use CSV or Excel file with columns: Student ID, Student Name, Subject, Marks, Grade
                    </div>
                    <form id="bulkUploadForm">
                        <div class="form-group">
                            <label>Select Exam</label>
                            <select id="bulk_exam_id" class="form-control" required>
                                <option value="">-- Choose Exam --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Class</label>
                            <select id="bulk_class_id" class="form-control" required>
                                <option value="">-- Choose Class --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Section</label>
                            <select id="bulk_section_id" class="form-control" required>
                                <option value="">-- Choose Section --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Upload File (.csv or .xlsx)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="bulkFile" accept=".csv,.xlsx" required>
                                <label class="custom-file-label" for="bulkFile">Choose file...</label>
                            </div>
                            <small class="form-text text-muted">Maximum file size: 5MB</small>
                        </div>
                        <div id="uploadProgressContainer" style="display: none;">
                            <label>Upload Progress</label>
                            <div class="progress">
                                <div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="uploadStatus" class="text-muted">Uploading...</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="btnBulkUpload" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i>Upload Marks
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Marks Modal (Individual) -->
    <div class="modal fade" id="uploadMarksModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-upload mr-2"></i>Upload Marks
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="uploadMarksContent">
                        <form id="uploadMarksForm">
                            <div class="form-group">
                                <label>Exam</label>
                                <input type="text" class="form-control" id="modal_exam_name" readonly>
                                <input type="hidden" id="modal_exam_id">
                            </div>
                            <div class="form-group">
                                <label>Class / Section</label>
                                <input type="text" class="form-control" id="modal_class_section" readonly>
                            </div>
                            <div class="form-group">
                                <label>Upload File (.csv or .xlsx)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="marksFile" accept=".csv,.xlsx" required>
                                    <label class="custom-file-label" for="marksFile">Choose file...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <strong>Download Template:</strong> 
                                    <a href="#" class="template-download">CSV Template</a> | 
                                    <a href="#" class="template-download">Excel Template</a>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="btnUploadMarks" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Results Modal -->
    <div class="modal fade" id="viewResultsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-bar mr-2"></i>Exam Results
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="resultsContent" style="text-align: center; padding: 40px;">
                        <p class="text-muted">Loading results...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="btnDownloadResults">
                        <i class="fas fa-download mr-2"></i>Download Results
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- plugins -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="../../../../../../public/assets/js/vendors.js"></script>

    <!-- custom app -->
    <script src="../../../../../../public/assets/js/app.js"></script>

    <!-- Page Scripts -->
    <script>
        $(function() {
            // Initialize - Load filter options
            loadSessions();
            loadClasses();
            loadCurrentExams();

            // Event Listeners
            $('#btnFilter').on('click', function() {
                loadCurrentExams();
                loadAllExams();
            });

            $('#btnReset').on('click', function() {
                $('#filter_session').val('');
                $('#filter_exam_type').val('');
                $('#filter_class').val('');
                $('#filter_section').val('');
                $('#filter_date_from').val('');
                $('#filter_date_to').val('');
                loadCurrentExams();
                loadAllExams();
            });

            $('#filter_class').on('change', function() {
                loadSectionsByClass($(this).val());
            });

            // Tab change event
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                if ($(e.target).attr('href') === '#allExamsTab') {
                    loadAllExams();
                }
            });

            // File input label update
            $('#bulkFile, #marksFile').on('change', function() {
                $(this).next('label').html(this.files[0]?.name || 'Choose file...');
            });

            // Bulk upload button
            $('#btnBulkUpload').on('click', function() {
                // TODO: Implement bulk upload logic
                alert('Bulk upload feature coming soon!');
            });

            // Upload marks button
            $('#btnUploadMarks').on('click', function() {
                // TODO: Implement individual upload logic
                alert('Upload marks feature coming soon!');
            });
        });

        // Load Sessions
        function loadSessions() {
            $.get('get_sessions.php', function(response) {
                if (response.success && response.data) {
                    var html = '<option value="">All Sessions</option>';
                    response.data.forEach(function(session) {
                        html += '<option value="' + session.id + '">' + session.name + '</option>';
                    });
                    $('#filter_session').html(html);
                }
            }).fail(function() {
                console.error('Failed to load sessions');
            });
        }

        // Load Classes
        function loadClasses() {
            $.get('get_classes.php', function(response) {
                if (response.success && response.data) {
                    var html = '<option value="">All Classes</option>';
                    response.data.forEach(function(cls) {
                        html += '<option value="' + cls.id + '">' + cls.class_name + '</option>';
                    });
                    $('#filter_class').html(html);
                }
            }).fail(function() {
                console.error('Failed to load classes');
            });
        }

        // Load Sections by Class
        function loadSectionsByClass(classId) {
            if (!classId) {
                $('#filter_section').html('<option value="">All Sections</option>');
                return;
            }
            $.get('get_sections.php', { class_id: classId }, function(response) {
                if (response.success && response.data) {
                    var html = '<option value="">All Sections</option>';
                    response.data.forEach(function(section) {
                        html += '<option value="' + section.id + '">' + section.section_name + '</option>';
                    });
                    $('#filter_section').html(html);
                }
            }).fail(function() {
                console.error('Failed to load sections');
            });
        }

        // Load Current Exams
        function loadCurrentExams() {
            var filters = getActiveFilters();
            filters.current = true;

            $.get('get_exams.php', filters, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    renderExamsTable('#currentExamsTbody', response.data);
                    updateStats(response.stats);
                } else {
                    $('#currentExamsTbody').html(
                        '<tr><td colspan="9" class="text-center text-muted py-5">' +
                        '<div class="no-data-message"><i class="fas fa-inbox"></i><p>No current exams found.</p></div>' +
                        '</td></tr>'
                    );
                }
            }).fail(function() {
                $('#currentExamsTbody').html(
                    '<tr><td colspan="9" class="text-center text-danger py-5">Error loading exams</td></tr>'
                );
            });
        }

        // Load All Exams
        function loadAllExams() {
            var filters = getActiveFilters();

            $.get('get_exams.php', filters, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    renderExamsTable('#allExamsTbody', response.data);
                } else {
                    $('#allExamsTbody').html(
                        '<tr><td colspan="9" class="text-center text-muted py-5">' +
                        '<div class="no-data-message"><i class="fas fa-inbox"></i><p>No exams found.</p></div>' +
                        '</td></tr>'
                    );
                }
            }).fail(function() {
                $('#allExamsTbody').html(
                    '<tr><td colspan="9" class="text-center text-danger py-5">Error loading exams</td></tr>'
                );
            });
        }

        // Get Active Filters
        function getActiveFilters() {
            return {
                session_id: $('#filter_session').val(),
                exam_type: $('#filter_exam_type').val(),
                class_id: $('#filter_class').val(),
                section_id: $('#filter_section').val(),
                date_from: $('#filter_date_from').val(),
                date_to: $('#filter_date_to').val()
            };
        }

        // Render Exams Table
        function renderExamsTable(tableBodySelector, exams) {
            var html = '';
            exams.forEach(function(exam, index) {
                var statusBadge = '<span class="exam-status-badge badge-' + exam.status + '">' + 
                    exam.status.toUpperCase() + '</span>';
                var progressPercent = exam.marks_uploaded_percent || 0;
                var progressBar = '<div class="upload-progress">' +
                    '<small>' + progressPercent + '%</small>' +
                    '<div class="progress"><div class="progress-bar" style="width: ' + progressPercent + '%"></div></div>' +
                    '</div>';

                var actions = '<div class="table-actions">' +
                    '<button class="btn btn-sm btn-outline-primary" onclick="openUploadModal(' + exam.id + ', \'' + exam.exam_name + '\', \'' + exam.class_section + '\')" title="Upload Marks">' +
                    '<i class="fas fa-cloud-upload-alt"></i> Upload' +
                    '</button> ' +
                    '<button class="btn btn-sm btn-outline-info" onclick="openViewResultsModal(' + exam.id + ')" title="View Results">' +
                    '<i class="fas fa-eye"></i> View' +
                    '</button> ' +
                    '<button class="btn btn-sm btn-outline-success" onclick="downloadResults(' + exam.id + ')" title="Download">' +
                    '<i class="fas fa-download"></i>' +
                    '</button>' +
                    '</div>';

                html += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td><strong>' + exam.exam_name + '</strong></td>' +
                    '<td><span class="badge badge-light">' + exam.exam_type + '</span></td>' +
                    '<td>' + exam.class_section + '</td>' +
                    '<td>' + formatDate(exam.start_date) + '</td>' +
                    '<td>' + formatDate(exam.end_date) + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' + progressBar + '</td>' +
                    '<td>' + actions + '</td>' +
                    '</tr>';
            });
            $(tableBodySelector).html(html);
        }

        // Open Upload Modal
        function openUploadModal(examId, examName, classSection) {
            $('#modal_exam_id').val(examId);
            $('#modal_exam_name').val(examName);
            $('#modal_class_section').val(classSection);
            $('#uploadMarksModal').modal('show');
        }

        // Open View Results Modal
        function openViewResultsModal(examId) {
            $('#resultsContent').html('<p class="text-muted">Loading results...</p>');
            $.get('get_exam_results.php', { exam_id: examId }, function(response) {
                if (response.success) {
                    // TODO: Render results table
                    $('#resultsContent').html('<p>Results loaded successfully</p>');
                }
            }).fail(function() {
                $('#resultsContent').html('<p class="text-danger">Error loading results</p>');
            });
            $('#viewResultsModal').modal('show');
        }

        // Download Results
        function downloadResults(examId) {
            window.location.href = 'download_results.php?exam_id=' + examId;
        }

        // Format Date
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            var date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Update Statistics
        function updateStats(stats) {
            if (stats) {
                $('#totalExams').text(stats.total || 0);
                $('#marksUploaded').text(stats.uploaded || 0);
                $('#pendingUpload').text(stats.pending || 0);
                $('#completionRate').text((stats.completion_rate || 0) + '%');
            }
        }
    </script>

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
        
        window.addEventListener('load', function() {
            var loader = document.querySelector('.loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
    </script>
</body>

</html>
