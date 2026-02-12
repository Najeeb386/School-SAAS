<?php
/**
 * Marks Upload Management - School Admin
 * User must be logged in as School Admin to access this page
 */
$appRoot = dirname(__DIR__, 5); // Navigate to App folder
require_once $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
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
                                                        <th style="width: 15%;">Exam Name</th>
                                                        <th style="width: 12%;">Type</th>
                                                        <th style="width: 15%;">Start Date</th>
                                                        <th style="width: 15%;">End Date</th>
                                                        <th style="width: 10%;">Status</th>
                                                        <th style="width: 12%;">Progress</th>
                                                        <th style="width: 16%;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="currentExamsTbody">
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-5">
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
                                                        <th style="width: 15%;">Exam Name</th>
                                                        <th style="width: 12%;">Type</th>
                                                        <th style="width: 15%;">Start Date</th>
                                                        <th style="width: 15%;">End Date</th>
                                                        <th style="width: 10%;">Status</th>
                                                        <th style="width: 12%;">Progress</th>
                                                        <th style="width: 16%;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="allExamsTbody">
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-5">
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

    

    <!-- Upload Marks Modal (Individual) -->
    <div class="modal fade" id="uploadMarksModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
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
                    <input type="hidden" id="modal_exam_id">
                    
                    <!-- Exam Info -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Exam Name</h6>
                                    <p class="font-weight-bold" id="modal_exam_name_display">-</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Total Marks</h6>
                                    <p class="font-weight-bold" id="modal_exam_marks_display">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Class & Section Selection -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="modal_class_id" class="font-weight-600">Select Class</label>
                                <select id="modal_class_id" class="form-control form-control-sm" required>
                                    <option value="">-- Choose Class --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="modal_section_id" class="font-weight-600">Select Section</label>
                                <select id="modal_section_id" class="form-control form-control-sm" required disabled>
                                    <option value="">-- Choose Section --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="modal_subject_id" class="font-weight-600">Select Subject</label>
                                <select id="modal_subject_id" class="form-control form-control-sm" required disabled>
                                    <option value="">-- Choose Subject --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Students List -->
                    <div id="studentsContainer" style="display: none;">
                        <h6 class="font-weight-600 mb-3">
                            <i class="fas fa-users mr-2"></i>Students
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Student Name</th>
                                        <th style="width: 15%;">Roll No</th>
                                        <th style="width: 15%;">Total Marks</th>
                                        <th style="width: 25%;">Obtained Marks</th>
                                        <th style="width: 20%;">Grade</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            <p>Select class and section to view students</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Loading Message -->
                    <div id="loadingMessage" class="text-center text-muted py-4">
                        <p>Select a class and section to display students</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="btnUploadMarks" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i>Save Marks
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
        // Configure jQuery AJAX to send credentials (cookies) with all requests
        $.ajaxSetup({
            xhrFields: {
                withCredentials: true
            }
        });

        $(function() {
            // Initialize - Load filter options
            loadSessions();
            loadExamTypes();
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
                }            });

            // Upload marks button
            $('#btnUploadMarks').on('click', function() {
                // TODO: Implement individual upload logic
                alert('Mark saving feature coming soon!');
            });

            // Modal class selection change
            $('#modal_class_id').on('change', function() {
                var classId = $(this).val();
                var examId = $('#modal_exam_id').val();
                
                $('#modal_section_id').val('').prop('disabled', !classId);
                $('#modal_subject_id').val('').prop('disabled', true);
                $('#studentsContainer').hide();
                $('#loadingMessage').show().text('Select a section and subject to display students');
                
                if (classId) {
                    loadModalSectionsByClass(classId);
                    loadModalSubjectsByExam(examId, classId);
                }
            });

            // Modal section selection change
            $('#modal_section_id').on('change', function() {
                var classId = $('#modal_class_id').val();
                var sectionId = $(this).val();
                var subjectId = $('#modal_subject_id').val();
                
                // Only load students if all three are selected
                if (classId && sectionId && subjectId) {
                    loadStudentsByClassSection(classId, sectionId);
                } else {
                    $('#studentsContainer').hide();
                    $('#loadingMessage').show().text('Please select class, section, and subject');
                }
            });

            // Modal subject selection change
            $('#modal_subject_id').on('change', function() {
                var classId = $('#modal_class_id').val();
                var sectionId = $('#modal_section_id').val();
                var subjectId = $(this).val();
                
                // Only load students if all three are selected
                if (classId && sectionId && subjectId) {
                    var subjectOption = $('#modal_subject_id').find('option:selected');
                    var totalMarks = subjectOption.data('total-marks') || 0;
                    $('#modal_exam_marks_display').text(totalMarks + ' Marks');
                    loadStudentsByClassSection(classId, sectionId);
                } else {
                    $('#studentsContainer').hide();
                    $('#loadingMessage').show().text('Please select all fields to display students');
                }
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
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load sessions:', textStatus, errorThrown, jqXHR.responseText);
            });
        }

        // Load Exam Types
        function loadExamTypes() {
            $.get('get_exam_types.php', function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    var html = '<option value="">All Types</option>';
                    response.data.forEach(function(type) {
                        // Capitalize first letter
                        var displayType = type.charAt(0).toUpperCase() + type.slice(1).replace(/_/g, ' ');
                        html += '<option value="' + type + '">' + displayType + '</option>';
                    });
                    $('#filter_exam_type').html(html);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load exam types:', textStatus, errorThrown, jqXHR.responseText);
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
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load classes:', textStatus, errorThrown, jqXHR.responseText);
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
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load sections:', textStatus, errorThrown, jqXHR.responseText);
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
                        '<tr><td colspan="8" class="text-center text-muted py-5">' +
                        '<div class="no-data-message"><i class="fas fa-inbox"></i><p>No current exams found.</p></div>' +
                        '</td></tr>'
                    );
                }
            }).fail(function() {
                $('#currentExamsTbody').html(
                    '<tr><td colspan="8" class="text-center text-danger py-5">Error loading exams</td></tr>'
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
                        '<tr><td colspan="8" class="text-center text-muted py-5">' +
                        '<div class="no-data-message"><i class="fas fa-inbox"></i><p>No exams found.</p></div>' +
                        '</td></tr>'
                    );
                }
            }).fail(function() {
                $('#allExamsTbody').html(
                    '<tr><td colspan="8" class="text-center text-danger py-5">Error loading exams</td></tr>'
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
                    '<button class="btn btn-sm btn-outline-primary" onclick="openUploadModal(' + exam.id + ', \'' + exam.exam_name + '\')" title="Upload Marks">' +
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
        function openUploadModal(examId, examName) {
            $('#modal_exam_id').val(examId);
            $('#modal_exam_name_display').text(examName);
            $('#modal_exam_marks_display').text('-'); // TODO: Fetch from exam details
            
            // Reset form
            $('#modal_class_id').html('<option value="">-- Choose Class --</option>').prop('disabled', false);
            $('#modal_section_id').html('<option value="">-- Choose Section --</option>').prop('disabled', true);
            $('#modal_subject_id').html('<option value="">-- Choose Subject --</option>').prop('disabled', true);
            $('#studentsContainer').hide();
            $('#loadingMessage').show().text('Select a class, section, and subject to display students');
            
            // Load classes for this exam
            loadModalClassesByExam(examId);
            
            $('#uploadMarksModal').modal('show');
        }

        // Load Classes for Modal (Exam-Specific)
        function loadModalClassesByExam(examId) {
            $.get('get_classes_by_exam.php', { exam_id: examId }, function(response) {
                if (response.success && response.data) {
                    var html = '<option value="">-- Choose Class --</option>';
                    response.data.forEach(function(cls) {
                        html += '<option value="' + cls.id + '">' + cls.class_name + '</option>';
                    });
                    $('#modal_class_id').html(html);
                } else {
                    $('#modal_class_id').html('<option value="">No classes assigned to this exam</option>');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load classes:', textStatus, errorThrown);
                $('#modal_class_id').html('<option value="">Error loading classes</option>');
            });
        }

        // Load Sections for Modal by Class
        function loadModalSectionsByClass(classId) {
            $.get('get_sections.php', { class_id: classId }, function(response) {
                if (response.success && response.data) {
                    var html = '<option value="">-- Choose Section --</option>';
                    response.data.forEach(function(section) {
                        html += '<option value="' + section.id + '">' + section.section_name + '</option>';
                    });
                    $('#modal_section_id').html(html).prop('disabled', false);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load sections:', textStatus, errorThrown);
            });
        }

        // Load Subjects for Modal by Exam and Class
        function loadModalSubjectsByExam(examId, classId) {
            if (!examId || !classId) {
                $('#modal_subject_id').html('<option value="">-- Choose Subject --</option>').prop('disabled', true);
                return;
            }

            $.get('get_exam_subjects.php', { exam_id: examId, class_id: classId }, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    var html = '<option value="">-- Choose Subject --</option>';
                    response.data.forEach(function(subject) {
                        html += '<option value="' + subject.id + '" data-total-marks="' + (subject.total_marks || 0) + '">' + 
                                subject.subject_name + ' (' + (subject.total_marks || 0) + ' Marks)' + 
                                '</option>';
                    });
                    $('#modal_subject_id').html(html).prop('disabled', false);
                } else {
                    $('#modal_subject_id').html('<option value="">No subjects assigned</option>').prop('disabled', true);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to load subjects:', textStatus, errorThrown);
                $('#modal_subject_id').html('<option value="">Error loading subjects</option>').prop('disabled', true);
            });
        }

        // Load Students by Class and Section
        function loadStudentsByClassSection(classId, sectionId) {
            $('#loadingMessage').show().text('Loading students...');
            $('#studentsContainer').hide();
            
            $.get('get_students_by_class.php', { class_id: classId, section_id: sectionId }, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    renderStudentsTable(response.data);
                    $('#studentsContainer').show();
                    $('#loadingMessage').hide();
                } else {
                    $('#loadingMessage').show().text('No students found in this class and section');
                    $('#studentsContainer').hide();
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $('#loadingMessage').show().text('Error loading students: ' + textStatus);
                $('#studentsContainer').hide();
                console.error('Failed to load students:', textStatus, errorThrown, jqXHR.responseText);
            });
        }

        // Render Students Table
        function renderStudentsTable(students) {
            var html = '';
            students.forEach(function(student, index) {
                html += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + student.student_name + '</td>' +
                    '<td>' + (student.actual_roll_no || student.roll_no || '-') + '</td>' +
                    '<td><input type="number" class="form-control form-control-sm" placeholder="0" readonly></td>' +
                    '<td><input type="number" class="form-control form-control-sm marks-input" placeholder="Enter marks" data-student-id="' + student.id + '" style="max-width: 100%;"></td>' +
                    '<td><span class="badge badge-secondary">-</span></td>' +
                    '</tr>';
            });
            $('#studentsTableBody').html(html);
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
