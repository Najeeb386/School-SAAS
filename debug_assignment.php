<?php
/**
 * Debug script to test exam assignment insertion
 */

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load required files
require_once __DIR__ . '/App/Core/database.php';
require_once __DIR__ . '/App/Modules/School_Admin/Models/ExamAssignmentModel.php';
require_once __DIR__ . '/App/Controllers/ExamAssignmentController.php';

try {
    // Get database connection
    $pdo = Database::connect();
    echo "<h2>✅ Database connected</h2>";
    
    // Use school_id = 1 for testing
    $school_id = 1;
    
    // Initialize model
    $model = new ExamAssignmentModel($pdo, $school_id);
    
    // Test data
    $assignment_data = [
        'exam_id' => '2',
        'class_id' => '14',
        'section_id' => '14',
        'subjects' => [
            [
                'subject_id' => '7',
                'exam_date' => '2026-02-09',
                'exam_time' => '14:42',
                'total_marks' => '30',
                'passing_marks' => '12',
                'status' => '1'
            ]
        ]
    ];
    
    echo "<h2>Test Data:</h2>";
    echo "<pre>" . json_encode($assignment_data, JSON_PRETTY_PRINT) . "</pre>";
    
    // Verify IDs exist in database
    echo "<h2>Verifying IDs exist in database:</h2>";
    
    // Check exam
    $examCheck = "SELECT id, exam_name FROM school_exams WHERE id = :id AND school_id = :school_id";
    $stmt = $pdo->prepare($examCheck);
    $stmt->execute([':id' => $assignment_data['exam_id'], ':school_id' => $school_id]);
    $exam = $stmt->fetch();
    if ($exam) {
        echo "<p style='color: green;'>✅ Exam exists: ID=" . $exam['id'] . ", Name=" . $exam['exam_name'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Exam NOT found with ID=" . $assignment_data['exam_id'] . "</p>";
    }
    
    // Check class
    $classCheck = "SELECT id, class_name FROM school_classes WHERE id = :id AND school_id = :school_id";
    $stmt = $pdo->prepare($classCheck);
    $stmt->execute([':id' => $assignment_data['class_id'], ':school_id' => $school_id]);
    $class = $stmt->fetch();
    if ($class) {
        echo "<p style='color: green;'>✅ Class exists: ID=" . $class['id'] . ", Name=" . $class['class_name'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Class NOT found with ID=" . $assignment_data['class_id'] . "</p>";
    }
    
    // Check section
    $sectionCheck = "SELECT id, name FROM school_class_sections WHERE id = :id AND class_id = :class_id";
    $stmt = $pdo->prepare($sectionCheck);
    $stmt->execute([':id' => $assignment_data['section_id'], ':class_id' => $assignment_data['class_id']]);
    $section = $stmt->fetch();
    if ($section) {
        echo "<p style='color: green;'>✅ Section exists: ID=" . $section['id'] . ", Name=" . $section['name'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Section NOT found with ID=" . $assignment_data['section_id'] . "</p>";
    }
    
    // Check subject
    $subjectCheck = "SELECT id, subject_name FROM school_subjects WHERE id = :id AND school_id = :school_id";
    $stmt = $pdo->prepare($subjectCheck);
    $stmt->execute([':id' => $assignment_data['subjects'][0]['subject_id'], ':school_id' => $school_id]);
    $subject = $stmt->fetch();
    if ($subject) {
        echo "<p style='color: green;'>✅ Subject exists: ID=" . $subject['id'] . ", Name=" . $subject['subject_name'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Subject NOT found with ID=" . $assignment_data['subjects'][0]['subject_id'] . "</p>";
    }
    
    echo "<hr>";
    
    // Test saveExamClass
    echo "<h2>Testing saveExamClass...</h2>";
    $exam_class_data = [
        'exam_id' => $assignment_data['exam_id'],
        'class_id' => $assignment_data['class_id'],
        'section_id' => $assignment_data['section_id'],
        'status' => 'active'
    ];
    
    $exam_class_id = $model->saveExamClass($exam_class_data);
    
    if ($exam_class_id) {
        echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Created exam class assignment with ID: $exam_class_id</p>";
        
        // Test saveExamSubjects
        echo "<h2>Testing saveExamSubjects...</h2>";
        $subjects_saved = $model->saveExamSubjects($exam_class_id, $assignment_data['subjects']);
        
        if ($subjects_saved) {
            echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Saved subjects successfully</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ FAILED:</strong> Failed to save subjects</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ FAILED:</strong> Could not create exam class assignment</p>";
        
        // Check if record already exists
        echo "<h3>Checking if assignment already exists...</h3>";
        $checkQuery = "SELECT id FROM school_exam_classes 
                      WHERE school_id = :school_id AND exam_id = :exam_id 
                      AND class_id = :class_id AND section_id = :section_id";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([
            ':school_id' => $school_id,
            ':exam_id' => $exam_class_data['exam_id'],
            ':class_id' => $exam_class_data['class_id'],
            ':section_id' => $exam_class_data['section_id']
        ]);
        
        if ($checkStmt->rowCount() > 0) {
            $existing = $checkStmt->fetch();
            echo "<p style='color: orange;'><strong>⚠️ INFO:</strong> This assignment already exists with ID: " . $existing['id'] . "</p>";
        }
    }
    
    // Test verification - count assignments
    echo "<h2>Database Verification:</h2>";
    $countQuery = "SELECT COUNT(*) as count FROM school_exam_classes WHERE school_id = :school_id";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute([':school_id' => $school_id]);
    $result = $countStmt->fetch();
    echo "<p>Total exam class assignments: <strong>" . $result['count'] . "</strong></p>";
    
} catch (\Exception $e) {
    echo "<h2 style='color: red;'>❌ ERROR</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
    echo "<pre><strong>Trace:</strong> " . $e->getTraceAsString() . "</pre>";
}
?>
