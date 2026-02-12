<?php
/**
 * API Endpoint: Download Exam Results
 * Downloads exam results as CSV or PDF format
 */
ob_start();

try {
    $appRoot = dirname(__DIR__, 5); // Navigate to App folder
    $projectRoot = dirname($appRoot); // Navigate to School-SAAS root
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'auth_check_school_admin.php';
    require_once $projectRoot . DIRECTORY_SEPARATOR . 'autoloader.php';
    require_once $appRoot . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';

    $school_id = $_SESSION['school_id'] ?? null;
    if (!$school_id) {
        throw new Exception('Unauthorized');
    }

    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
    $format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';
    
    if (!$exam_id) {
        throw new Exception('Exam ID is required');
    }

    $db = \Database::connect();
    
    $examController = new \App\Modules\School_Admin\Controllers\ExamController($db, $school_id);
    
    // Get exam details
    $exam = $examController->getExamById($exam_id);
    if (!$exam) {
        throw new Exception('Exam not found');
    }
    
    // Get exam results
    $results = $examController->getExamResults($exam_id);
    
    if ($format === 'csv') {
        // Download as CSV
        ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="exam_results_' . $exam_id . '_' . date('Y-m-d-His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, ['Student Name', 'Admission No', 'Class', 'Section', 'Subject', 'Marks', 'Grade']);
        
        // Data rows
        foreach ($results as $result) {
            fputcsv($output, [
                $result['first_name'] . ' ' . $result['last_name'],
                $result['admission_no'] ?? '',
                $result['class_name'] ?? '',
                $result['section_name'] ?? '',
                $result['subject_name'] ?? '',
                $result['marks'] ?? '',
                $result['grade'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    } else {
        throw new Exception('PDF download not yet implemented');
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
