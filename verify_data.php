<?php
/**
 * Quick database verification
 */
require_once __DIR__ . '/App/Core/database.php';

$pdo = Database::connect();

echo "<h1>School Data Verification</h1>";

// Try to get school_id from session or assume 1
session_start();
$school_id = $_SESSION['school_id'] ?? 1;
echo "<p><strong>School ID:</strong> $school_id</p>";

echo "<h2>Classes for this School:</h2>";
$classesQuery = "SELECT id, class_name FROM school_classes WHERE school_id = ? ORDER BY id";
$stmt = $pdo->prepare($classesQuery);
$stmt->execute([$school_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Class Name</th></tr>";
foreach ($classes as $class) {
    echo "<tr><td>" . $class['id'] . "</td><td>" . $class['class_name'] . "</td></tr>";
}
echo "</table>";

echo "<h2>Sections for Class ID 14:</h2>";
$sectionsQuery = "SELECT id, name FROM school_class_sections WHERE class_id = ? ORDER BY id";
$stmt = $pdo->prepare($sectionsQuery);
$stmt->execute([14]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($sections) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th></tr>";
    foreach ($sections as $section) {
        echo "<tr><td>" . $section['id'] . "</td><td>" . $section['name'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><strong>No sections found for class 14!</strong></p>";
}

echo "<h2>Exams for this School:</h2>";
$examsQuery = "SELECT id, exam_name FROM school_exams WHERE school_id = ? ORDER BY id";
$stmt = $pdo->prepare($examsQuery);
$stmt->execute([$school_id]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Exam Name</th></tr>";
foreach ($exams as $exam) {
    echo "<tr><td>" . $exam['id'] . "</td><td>" . $exam['exam_name'] . "</td></tr>";
}
echo "</table>";

echo "<h2>Subjects for this School:</h2>";
$subjectsQuery = "SELECT id, subject_name FROM school_subjects WHERE school_id = ? ORDER BY id";
$stmt = $pdo->prepare($subjectsQuery);
$stmt->execute([$school_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Subject Name</th></tr>";
foreach ($subjects as $subject) {
    echo "<tr><td>" . $subject['id'] . "</td><td>" . $subject['subject_name'] . "</td></tr>";
}
echo "</table>";

echo "<h2>Existing Exam Assignments:</h2>";
$assignmentsQuery = "SELECT ec.id, ec.exam_id, ec.class_id, ec.section_id, e.exam_name, c.class_name 
                     FROM school_exam_classes ec
                     LEFT JOIN school_exams e ON ec.exam_id = e.id
                     LEFT JOIN school_classes c ON ec.class_id = c.id
                     WHERE ec.school_id = ? 
                     ORDER BY ec.id DESC LIMIT 10";
$stmt = $pdo->prepare($assignmentsQuery);
$stmt->execute([$school_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($assignments) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Exam</th><th>Class</th><th>Class ID</th><th>Section ID</th></tr>";
    foreach ($assignments as $assign) {
        echo "<tr><td>" . $assign['id'] . "</td><td>" . $assign['exam_name'] . "</td><td>" . $assign['class_name'] . "</td><td>" . $assign['class_id'] . "</td><td>" . $assign['section_id'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No exam assignments yet.</p>";
}
?>
