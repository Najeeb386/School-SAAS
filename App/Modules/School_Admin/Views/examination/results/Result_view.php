<?php
/**
 * Results View Page - School Admin
 * Displays exam results by class with detailed student marks
 */
$appRoot = dirname(__DIR__, 4);
require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../../autoloader.php';
require_once __DIR__ . '/../../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;
$db = \Database::connect();

// Get exam_id from URL
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;

// Get all exams for dropdown
$exams = [];
try {
    $stmt = $db->prepare("
        SELECT e.id, e.exam_name, e.exam_type, e.start_date, e.end_date, s.name as session_name
        FROM school_exams e
        LEFT JOIN school_sessions s ON e.session_id = s.id
        WHERE e.school_id = ?
        ORDER BY e.start_date DESC
        LIMIT 50
    ");
    $stmt->execute([$school_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $exams = [];
}

// Get current exam details if exam_id is provided
$current_exam = null;
if ($exam_id) {
    try {
        $stmt = $db->prepare("
            SELECT e.*, s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.id = ? AND e.school_id = ?
        ");
        $stmt->execute([$exam_id, $school_id]);
        $current_exam = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $current_exam = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Exam Results - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="View exam results" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="../../../../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .result-container {
            padding: 20px;
        }
        
        .exam-header {
            background: linear-gradient(135deg, #0066cc 0%, #004299 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .exam-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .exam-header .exam-meta {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .class-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .class-card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .class-card-header h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .class-card-body {
            padding: 0;
        }
        
        .section-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px 20px;
            background: #fafafa;
            border-bottom: 1px solid #eee;
        }
        
        .section-tab {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .section-tab:hover, .section-tab.active {
            background: #0066cc;
            color: white;
            border-color: #0066cc;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .results-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        
        .results-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.875rem;
        }
        
        .results-table tr:hover {
            background: #f8f9fa;
        }
        
        .results-table .subject-col {
            min-width: 150px;
        }
        
        .results-table .marks-col {
            text-align: center;
            min-width: 80px;
        }
        
        .marks-obtained {
            font-weight: 600;
            color: #0066cc;
        }
        
        .marks-absent {
            color: #dc3545;
            font-style: italic;
        }
        
        .marks-not-uploaded {
            color: #6c757d;
            font-style: italic;
        }
        
        .student-info {
            display: flex;
            flex-direction: column;
        }
        
        .student-name {
            font-weight: 500;
            color: #333;
        }
        
        .student-admno {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #cce5ff; color: #004085; }
        .grade-C { background: #fff3cd; color: #856404; }
        .grade-D { background: #f8d7da; color: #721c24; }
        .grade-F { background: #f8d7da; color: #721c24; }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .filter-section select {
            max-width: 250px;
        }
        
        .btn-details {
            background: #0066cc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .btn-details:hover {
            background: #004299;
        }
        
        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .loading-spinner i {
            font-size: 32px;
            margin-bottom: 10px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        .subject-header {
            background: #f0f4f8 !important;
            font-weight: 600;
        }
        
        .total-row {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .expand-icon {
            transition: transform 0.2s;
        }
        
        .expand-icon.expanded {
            transform: rotate(90deg);
        }
        
        .subject-results-container {
            display: none;
            padding: 20px;
            background: #fafafa;
        }
        
        .subject-results-container.show {
            display: block;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stat-box .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0066cc;
        }
        
        .stat-box .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            opacity: 0.9;
        }
        
        .back-btn:hover {
            color: white;
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="../../../../../../public/assets/img/loader/loader.svg" alt="loader">
                    </div>
                </div>
            </div>
            
            <div class="app-container">
                <div class="" id="main">
                    <div class="container-fluid">
                        <!-- Page Header -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-2"><i class="fas fa-chart-bar mr-2"></i>Exam Results</h3>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb p-0 bg-transparent">
                                                <li class="breadcrumb-item"><a href="../../dashboard/index.php">Overview</a></li>
                                                <li class="breadcrumb-item"><a href="../examination.php">Examination</a></li>
                                                <li class="breadcrumb-item"><a href="upload_marks.php">Marks Upload</a></li>
                                                <li class="breadcrumb-item active" aria-current="page">Results</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <a href="upload_marks.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-left mr-1"></i>Back to Marks Upload
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Exam Filter -->
                        <div class="filter-section">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="examSelect" class="form-label">Select Exam</label>
                                    <select class="form-control" id="examSelect" onchange="loadExamResults()">
                                        <option value="">-- Select Exam --</option>
                                        <?php foreach ($exams as $exam): ?>
                                            <option value="<?php echo $exam['id']; ?>" <?php echo ($exam_id == $exam['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($exam['exam_name']); ?> 
                                                (<?php echo htmlspecialchars($exam['exam_type']); ?>)
                                                - <?php echo htmlspecialchars($exam['session_name'] ?? 'N/A'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary" onclick="loadExamResults()">
                                        <i class="fas fa-search mr-1"></i>Load Results
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Results Container -->
                        <div id="resultsContainer">
                            <?php if ($current_exam): ?>
                                <!-- Exam Header -->
                                <div class="exam-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h3><i class="fas fa-file-alt mr-2"></i><?php echo htmlspecialchars($current_exam['exam_name']); ?></h3>
                                            <div class="exam-meta">
                                                <span class="mr-3"><i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($current_exam['exam_type']); ?></span>
                                                <span class="mr-3"><i class="fas fa-calendar mr-1"></i><?php echo date('M d, Y', strtotime($current_exam['start_date'])); ?> - <?php echo date('M d, Y', strtotime($current_exam['end_date'])); ?></span>
                                                <span><i class="fas fa-graduation-cap mr-1"></i><?php echo htmlspecialchars($current_exam['session_name'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                        <a href="upload_marks.php?exam_id=<?php echo $exam_id; ?>" class="back-btn">
                                            <i class="fas fa-edit"></i> Upload Marks
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Summary Stats -->
                                <div class="summary-stats" id="summaryStats">
                                    <div class="stat-box">
                                        <div class="stat-number" id="totalClasses">0</div>
                                        <div class="stat-label">Classes</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-number" id="totalSections">0</div>
                                        <div class="stat-label">Sections</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-number" id="totalStudents">0</div>
                                        <div class="stat-label">Students</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-number" id="avgMarks">0%</div>
                                        <div class="stat-label">Avg Marks</div>
                                    </div>
                                </div>
                                
                                <!-- Classes List -->
                                <div id="classesList">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner"></i>
                                        <p>Loading classes...</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="fas fa-search"></i>
                                    <p>Please select an exam to view results</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="../../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../../public/assets/js/app.js"></script>
    
    <script>
        // Configure jQuery AJAX
        $.ajaxSetup({
            xhrFields: {
                withCredentials: true
            }
        });
        
        $(function() {
            // Load results if exam is selected
            const examId = <?php echo $exam_id ? $exam_id : 'null'; ?>;
            if (examId) {
                loadExamResults();
            }
        });
        
        function loadExamResults() {
            const examId = $('#examSelect').val();
            if (!examId) {
                $('#resultsContainer').html(`
                    <div class="no-data">
                        <i class="fas fa-search"></i>
                        <p>Please select an exam to view results</p>
                    </div>
                `);
                return;
            }
            
            // Update URL without reload
            const newUrl = window.location.pathname + '?exam_id=' + examId;
            window.history.pushState({}, '', newUrl);
            
            // Show loading
            $('#resultsContainer').html(`
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                    <p>Loading results...</p>
                </div>
            `);
            
            // Load exam details first
            $.get('get_exams.php', { exam_id: examId }, function(examResponse) {
                if (examResponse.success && examResponse.data && examResponse.data.length > 0) {
                    const exam = examResponse.data[0];
                    renderExamHeader(exam);
                    loadClassesByExam(examId);
                } else {
                    $('#resultsContainer').html(`
                        <div class="no-data">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Exam not found</p>
                        </div>
                    `);
                }
            }).fail(function() {
                $('#resultsContainer').html(`
                    <div class="no-data">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading exam details</p>
                    </div>
                `);
            });
        }
        
        function renderExamHeader(exam) {
            const startDate = exam.start_date ? new Date(exam.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
            const endDate = exam.end_date ? new Date(exam.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
            
            let headerHtml = `
                <div class="exam-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><i class="fas fa-file-alt mr-2"></i>${exam.exam_name}</h3>
                            <div class="exam-meta">
                                <span class="mr-3"><i class="fas fa-tag mr-1"></i>${exam.exam_type}</span>
                                <span class="mr-3"><i class="fas fa-calendar mr-1"></i>${startDate} - ${endDate}</span>
                            </div>
                        </div>
                        <a href="upload_marks.php?exam_id=${exam.id}" class="back-btn">
                            <i class="fas fa-edit"></i> Upload Marks
                        </a>
                    </div>
                </div>
                <div class="summary-stats" id="summaryStats">
                    <div class="stat-box">
                        <div class="stat-number" id="totalClasses">0</div>
                        <div class="stat-label">Classes</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="totalSections">0</div>
                        <div class="stat-label">Sections</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="totalStudents">0</div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="avgMarks">0%</div>
                        <div class="stat-label">Avg Marks</div>
                    </div>
                </div>
                <div id="classesList">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner"></i>
                        <p>Loading classes...</p>
                    </div>
                </div>
            `;
            
            // Keep the filter section
            const filterSection = $('.filter-section').html();
            $('#resultsContainer').html(headerHtml);
            $('.filter-section').html(filterSection);
            $('#examSelect').val(exam.id);
        }
        
        function loadClassesByExam(examId) {
            $.get('get_classes_by_exam.php', { exam_id: examId }, function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    renderClassesList(response.data, examId);
                    updateSummaryStats(response.data);
                } else {
                    $('#classesList').html(`
                        <div class="no-data">
                            <i class="fas fa-school"></i>
                            <p>No classes assigned to this exam</p>
                        </div>
                    `);
                }
            }).fail(function() {
                $('#classesList').html(`
                    <div class="no-data">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading classes</p>
                    </div>
                `);
            });
        }
        
        function updateSummaryStats(classes) {
            $('#totalClasses').text(classes.length);
            // These will be updated when details are loaded
            $('#totalSections').text('0');
            $('#totalStudents').text('0');
            $('#avgMarks').text('0%');
        }
        
        function renderClassesList(classes, examId) {
            let html = '';
            classes.forEach((cls, index) => {
                html += `
                    <div class="class-card">
                        <div class="class-card-header">
                            <h5><i class="fas fa-school mr-2"></i>${cls.class_name}</h5>
                            <button class="btn-details" onclick="loadClassResults(${examId}, ${cls.id}, '${cls.class_name}', ${index})">
                                <i class="fas fa-eye mr-1"></i> Details
                            </button>
                        </div>
                        <div class="class-card-body">
                            <div id="classResults_${index}" class="subject-results-container">
                                <div class="loading-spinner">
                                    <i class="fas fa-spinner"></i>
                                    <p>Loading results...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#classesList').html(html);
        }
        
        function loadClassResults(examId, classId, className, index) {
            const container = $(`#classResults_${index}`);
            const isVisible = container.hasClass('show');
            
            // Toggle visibility
            if (isVisible) {
                container.removeClass('show');
                return;
            }
            
            container.addClass('show');
            container.html(`
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                    <p>Loading results for ${className}...</p>
                </div>
            `);
            
            // Get sections for this class and exam
            $.get('get_sections.php', { class_id: classId }, function(sectionsResponse) {
                if (sectionsResponse.success && sectionsResponse.data && sectionsResponse.data.length > 0) {
                    // Load results for all sections
                    loadSectionsResults(examId, classId, sectionsResponse.data, container, index);
                } else {
                    container.html(`
                        <div class="no-data">
                            <i class="fas fa-users"></i>
                            <p>No sections found for this class</p>
                        </div>
                    `);
                }
            }).fail(function() {
                container.html(`
                    <div class="no-data">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading sections</p>
                    </div>
                `);
            });
        }
        
        function loadSectionsResults(examId, classId, sections, container, classIndex) {
            let allResults = [];
            let loadedCount = 0;
            
            // Get all subjects for this exam and class
            $.get('get_exam_subjects.php', { exam_id: examId, class_id: classId }, function(subjectsResponse) {
                const subjects = subjectsResponse.success ? (subjectsResponse.data || []) : [];
                
                if (sections.length === 0) {
                    container.html(`
                        <div class="no-data">
                            <i class="fas fa-users"></i>
                            <p>No sections found</p>
                        </div>
                    `);
                    return;
                }
                
                // Load results for each section
                let totalStudents = 0;
                let totalMarks = 0;
                let marksCount = 0;
                
                sections.forEach((section, sectionIndex) => {
                    $.get('get_exam_results.php', { exam_id: examId }, function(resultsResponse) {
                        loadedCount++;
                        
                        console.log('Results response:', resultsResponse);
                        
                        if (resultsResponse.success && resultsResponse.data) {
                            // Filter results for this class and section
                            const sectionResults = resultsResponse.data.filter(r => {
                                return r.class_id == classId && r.section_id == section.id;
                            });
                            
                            allResults.push({
                                section: section,
                                results: sectionResults
                            });
                            
                            // Update stats
                            sectionResults.forEach(r => {
                                if (r.obtained_marks !== null && r.obtained_marks !== undefined) {
                                    totalStudents++;
                                    totalMarks += parseFloat(r.obtained_marks);
                                    marksCount++;
                                }
                            });
                        } else {
                            console.log('Error loading results:', resultsResponse.message);
                            allResults.push({
                                section: section,
                                results: [],
                                error: resultsResponse.message
                            });
                        }
                        
                        // Check if all sections loaded
                        if (loadedCount === sections.length) {
                            renderAllSectionsResults(allResults, subjects, sections, container, classIndex);
                            
                            // Update global stats
                            const avg = marksCount > 0 ? Math.round((totalMarks / marksCount) * 100) / 100 : 0;
                            $('#totalSections').text(sections.length);
                            $('#totalStudents').text(totalStudents);
                            $('#avgMarks').text(avg + '%');
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        loadedCount++;
                        console.log('Error:', textStatus, errorThrown);
                        allResults.push({
                            section: section,
                            results: [],
                            error: 'Failed to load: ' + textStatus
                        });
                        if (loadedCount === sections.length) {
                            renderAllSectionsResults(allResults, subjects, sections, container, classIndex);
                        }
                    });
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                container.html(`
                    <div class="no-data">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading subjects: ${textStatus}</p>
                    </div>
                `);
            });
        }
        
        function renderAllSectionsResults(allResults, subjects, sections, container, classIndex) {
            if (allResults.length === 0 || allResults.every(r => r.results.length === 0)) {
                container.html(`
                    <div class="no-data">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No results found for this class</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            
            // Create section tabs
            html += '<div class="section-tabs">';
            sections.forEach((section, idx) => {
                const isActive = idx === 0 ? 'active' : '';
                html += `<div class="section-tab ${isActive}" onclick="switchSection(${classIndex}, ${idx})" data-section="${idx}">${section.section_name}</div>`;
            });
            html += '</div>';
            
            // Create results tables for each section
            allResults.forEach((sectionData, idx) => {
                const display = idx === 0 ? 'block' : 'none';
                html += `<div id="sectionTable_${classIndex}_${idx}" style="display: ${display};">`;
                
                if (sectionData.results.length > 0) {
                    // Group results by student
                    const studentResults = {};
                    sectionData.results.forEach(r => {
                        const studentId = r.student_id;
                        if (!studentResults[studentId]) {
                            studentResults[studentId] = {
                                first_name: r.first_name,
                                last_name: r.last_name,
                                admission_no: r.admission_no,
                                marks: {}
                            };
                        }
                        studentResults[studentId].marks[r.subject_id] = {
                            obtained: r.obtained_marks,
                            total: r.total_marks,
                            is_absent: r.is_absent
                        };
                    });
                    
                    // Get unique subjects
                    const uniqueSubjects = [...new Set(sectionData.results.map(r => r.subject_id))];
                    
                    // Build table
                    html += '<table class="results-table">';
                    html += '<thead><tr>';
                    html += '<th>Student Name</th>';
                    html += '<th>Adm. No.</th>';
                    
                    // Add subject columns
                    uniqueSubjects.forEach(subjectId => {
                        const subject = sectionData.results.find(r => r.subject_id === subjectId);
                        html += `<th class="marks-col">${subject ? subject.subject_name : 'Subject'}</th>`;
                    });
                    html += '<th class="marks-col">Total</th>';
                    html += '<th class="marks-col">%</th>';
                    html += '<th>Grade</th>';
                    html += '</tr></thead>';
                    
                    html += '<tbody>';
                    
                    // Add student rows
                    Object.values(studentResults).forEach(student => {
                        html += '<tr>';
                        html += `<td><div class="student-info"><span class="student-name">${student.first_name} ${student.last_name}</span></div></td>`;
                        html += `<td><div class="student-info"><span class="student-admno">${student.admission_no || '-'}</span></div></td>`;
                        
                        let studentTotal = 0;
                        let studentMax = 0;
                        
                        uniqueSubjects.forEach(subjectId => {
                            const markData = student.marks[subjectId];
                            if (markData) {
                                if (markData.is_absent) {
                                    html += '<td><span class="marks-absent">AB</span></td>';
                                } else if (markData.obtained !== null && markData.obtained !== undefined) {
                                    html += `<td><span class="marks-obtained">${markData.obtained}</span>/${markData.total || '-'}</td>`;
                                    studentTotal += parseFloat(markData.obtained);
                                    studentMax += parseFloat(markData.total || markData.obtained);
                                } else {
                                    html += '<td><span class="marks-not-uploaded">-</span></td>';
                                }
                            } else {
                                html += '<td><span class="marks-not-uploaded">-</span></td>';
                            }
                        });
                        
                        const percentage = studentMax > 0 ? Math.round((studentTotal / studentMax) * 100) : 0;
                        const grade = getGrade(percentage);
                        
                        html += `<td><strong>${studentTotal}</strong></td>`;
                        html += `<td><strong>${percentage}%</strong></td>`;
                        html += `<td><span class="grade-badge grade-${grade}">${grade}</span></td>`;
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                } else {
                    html += `
                        <div class="no-data">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No results found for ${sectionData.section.section_name}</p>
                        </div>
                    `;
                }
                
                html += '</div>';
            });
            
            container.html(html);
        }
        
        function switchSection(classIndex, sectionIndex) {
            // Update tabs
            $(`#classResults_${classIndex} .section-tab`).removeClass('active');
            $(`#classResults_${classIndex} .section-tab[data-section="${sectionIndex}"]`).addClass('active');
            
            // Show/hide tables
            $(`#classResults_${classIndex} > div[id^="sectionTable_"]`).each(function() {
                $(this).hide();
            });
            $(`#sectionTable_${classIndex}_${sectionIndex}`).show();
        }
        
        function getGrade(percentage) {
            if (percentage >= 90) return 'A';
            if (percentage >= 80) return 'B';
            if (percentage >= 70) return 'C';
            if (percentage >= 60) return 'D';
            return 'F';
        }
        
        // Hide loader on page load
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
