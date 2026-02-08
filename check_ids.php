<?php
/**
 * Targeted diagnostic for specific IDs
 */
require_once __DIR__ . '/App/Core/database.php';

$pdo = Database::connect();

echo "<h1>Diagnostic Report for IDs in Console Error</h1>";

// Get school_id from session or assume 1
session_start();
$school_id = $_SESSION['school_id'] ?? 1;
echo "<p><strong>School ID:</strong> $school_id</p>";
echo "<hr>";

// IDs from the console error
$exam_id = 2;
$class_id = 14;
$section_id = 14;

echo "<h2>Checking IDs: exam_id=$exam_id, class_id=$class_id, section_id=$section_id</h2>";
echo "<hr>";

// Check exam_id
echo "<h3>1. Exam ID $exam_id</h3>";
$examQuery = "SELECT id, exam_name, school_id FROM school_exams WHERE id = ?";
$stmt = $pdo->prepare($examQuery);
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if ($exam) {
    if ($exam['school_id'] == $school_id) {
        echo "<p style='color: green;'><strong>✅ VALID:</strong> Exam ID $exam_id exists and belongs to school $school_id</p>";
        echo "<p>&nbsp;&nbsp;Exam Name: <strong>" . $exam['exam_name'] . "</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ ERROR:</strong> Exam ID $exam_id exists but belongs to school " . $exam['school_id'] . ", not school $school_id</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ NOT FOUND:</strong> Exam ID $exam_id does not exist</p>";
}
echo "<hr>";

// Check class_id
echo "<h3>2. Class ID $class_id</h3>";
$classQuery = "SELECT id, class_name, school_id FROM school_classes WHERE id = ?";
$stmt = $pdo->prepare($classQuery);
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if ($class) {
    if ($class['school_id'] == $school_id) {
        echo "<p style='color: green;'><strong>✅ VALID:</strong> Class ID $class_id exists and belongs to school $school_id</p>";
        echo "<p>&nbsp;&nbsp;Class Name: <strong>" . $class['class_name'] . "</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ ERROR:</strong> Class ID $class_id exists but belongs to school " . $class['school_id'] . ", not school $school_id</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ NOT FOUND:</strong> Class ID $class_id does not exist</p>";
}
echo "<hr>";

// Check section_id and its relationship to class_id
echo "<h3>3. Section ID $section_id for Class ID $class_id</h3>";
$sectionQuery = "SELECT id, name, class_id FROM school_class_sections WHERE id = ?";
$stmt = $pdo->prepare($sectionQuery);
$stmt->execute([$section_id]);
$section = $stmt->fetch(PDO::FETCH_ASSOC);

if ($section) {
    echo "<p style='color: green;'><strong>✅ FOUND:</strong> Section ID $section_id exists</p>";
    echo "<p>&nbsp;&nbsp;Section Name: <strong>" . $section['name'] . "</strong></p>";
    echo "<p>&nbsp;&nbsp;Belongs to Class ID: <strong>" . $section['class_id'] . "</strong></p>";
    
    if ($section['class_id'] == $class_id) {
        echo "<p style='color: green;'><strong>✅ CORRECT:</strong> Section belongs to Class ID $class_id</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ MISMATCH:</strong> Section ID $section_id belongs to Class ID " . $section['class_id'] . ", NOT Class ID $class_id</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ NOT FOUND:</strong> Section ID $section_id does not exist</p>";
    
    echo "<h4>Available sections for Class ID $class_id:</h4>";
    $availableQuery = "SELECT id, name FROM school_class_sections WHERE class_id = ? ORDER BY id";
    $stmt = $pdo->prepare($availableQuery);
    $stmt->execute([$class_id]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($sections) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Section ID</th><th>Section Name</th></tr>";
        foreach ($sections as $s) {
            echo "<tr><td>" . $s['id'] . "</td><td>" . $s['name'] . "</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>👉 Use one of these Section IDs instead!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ CLASS HAS NO SECTIONS:</strong> Class ID $class_id has no sections defined!</p>";
    }
}
echo "<hr>";

// Check subjects
echo "<h3>4. Subjects Check</h3>";
$subjectIds = [7, 6];
foreach ($subjectIds as $subj_id) {
    $subjectQuery = "SELECT id, subject_name, school_id FROM school_subjects WHERE id = ?";
    $stmt = $pdo->prepare($subjectQuery);
    $stmt->execute([$subj_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subject) {
        if ($subject['school_id'] == $school_id) {
            echo "<p style='color: green;'><strong>✅ VALID:</strong> Subject ID $subj_id exists for school $school_id</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ ERROR:</strong> Subject ID $subj_id belongs to different school</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ NOT FOUND:</strong> Subject ID $subj_id does not exist</p>";
    }
}
echo "<hr>";

// Check for duplicate assignment
echo "<h3>5. Duplicate Assignment Check</h3>";
$duplicateQuery = "SELECT id FROM school_exam_classes 
                   WHERE school_id = ? AND exam_id = ? AND class_id = ? AND section_id = ?";
$stmt = $pdo->prepare($duplicateQuery);
$stmt->execute([$school_id, $exam_id, $class_id, $section_id]);

if ($stmt->rowCount() > 0) {
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: orange;'><strong>⚠️ DUPLICATE:</strong> This exact assignment already exists with ID " . $existing['id'] . "</p>";
    echo "<p>If you want to modify it, use the Edit function instead.</p>";
} else {
    echo "<p style='color: green;'><strong>✅ NEW:</strong> This is not a duplicate assignment</p>";
}
echo "<hr>";

echo "<h2 style='color: darkblue;'>Summary:</h2>";
if ($section && $section['class_id'] == $class_id) {
    echo "<p style='color: green;'><strong>All IDs are valid and properly linked!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>There is an ID mismatch. Please use the correct section ID for class $class_id</strong></p>";
}
?>
