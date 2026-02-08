<?php
/**
 * Test API Endpoint for Exam Assignments - DETAILED DIAGNOSTIC
 * This file helps diagnose database and API issues
 */

require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;

if (!$school_id) {
    echo "<div style='color: red; font-weight: bold; background: #fee; padding: 15px; border-radius: 5px;'>ERROR: No school_id in session</div>";
    exit;
}

// Get PDO connection
$pdo = Database::connect();

echo "<h2>🔍 Complete Database Diagnostic Report</h2>";
echo "<p><strong>School ID:</strong> <span style='background: #ffe; padding: 5px 10px; border-radius: 3px;'>$school_id</span></p>";
echo "<p><strong>Database Status:</strong> " . ($pdo ? "✅ Connected" : "❌ Failed") . "</p>";
echo "<hr>";

// Test 1: Check school_classes table
echo "<h3>1️⃣ Testing school_classes Table</h3>";
try {
    // First check ALL records
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_classes");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    echo "<p>Total records in school_classes: <strong>$total</strong></p>";
    
    // Then check for this school_id
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_classes WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] ?? 0;
    echo "<p>Classes for school_id $school_id: <strong style='color: " . ($count > 0 ? "green" : "red") . "'>$count</strong></p>";
    
    // Show actual structure
    $stmt = $pdo->query("DESCRIBE school_classes");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns in school_classes:</strong> " . implode(", ", $columns) . "</p>";
    
    // Show sample data for this school
    $stmt = $pdo->prepare("SELECT id, school_id, class_name, status FROM school_classes WHERE school_id = ? LIMIT 5");
    $stmt->execute([$school_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($classes) > 0) {
        echo "<pre style='background: #f0f0f0; padding: 10px; border-left: 3px solid green;'>";
        foreach ($classes as $cls) {
            echo "ID: {$cls['id']}, School: {$cls['school_id']}, Name: {$cls['class_name']}, Status: {$cls['status']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>⚠️ NO CLASSES FOUND FOR SCHOOL_ID $school_id</strong></p>";
        // Show sample data from other schools
        $stmt = $pdo->query("SELECT DISTINCT school_id FROM school_classes LIMIT 3");
        $schools = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Sample school IDs with data: " . implode(", ", $schools) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 2: Check school_class_sections table
echo "<h3>2️⃣ Testing school_class_sections Table</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_class_sections");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    echo "<p>Total records in school_class_sections: <strong>$total</strong></p>";
    
    // Show columns
    $stmt = $pdo->query("DESCRIBE school_class_sections");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns:</strong> " . implode(", ", $columns) . "</p>";
    
    // Check if it has school_id column
    if (in_array('school_id', $columns)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_class_sections WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        echo "<p>Sections for school_id $school_id: <strong>$count</strong></p>";
    }
    
    // Show sample
    $stmt = $pdo->query("SELECT * FROM school_class_sections LIMIT 3");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($samples) > 0) {
        echo "<pre style='background: #f0f0f0; padding: 10px;'>";
        echo json_encode($samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 3: Check school_subjects table
echo "<h3>3️⃣ Testing school_subjects Table</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_subjects");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    echo "<p>Total records in school_subjects: <strong>$total</strong></p>";
    
    // Show columns
    $stmt = $pdo->query("DESCRIBE school_subjects");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns:</strong> " . implode(", ", $columns) . "</p>";
    
    // Check for this school
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_subjects WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] ?? 0;
    echo "<p>Subjects for school_id $school_id: <strong style='color: " . ($count > 0 ? "green" : "red") . "'>$count</strong></p>";
    
    // Show sample
    $stmt = $pdo->prepare("SELECT id, school_id, name, status FROM school_subjects WHERE school_id = ? LIMIT 5");
    $stmt->execute([$school_id]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($subjects) > 0) {
        echo "<pre style='background: #f0f0f0; padding: 10px; border-left: 3px solid green;'>";
        foreach ($subjects as $subj) {
            echo "ID: {$subj['id']}, School: {$subj['school_id']}, Name: {$subj['name']}, Status: {$subj['status']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>⚠️ NO SUBJECTS FOUND FOR SCHOOL_ID $school_id</strong></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 4: Check school_exams table
echo "<h3>4️⃣ Testing school_exams Table</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_exams WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] ?? 0;
    echo "<p>Exams for school_id $school_id: <strong style='color: " . ($count > 0 ? "green" : "red") . "'>$count</strong></p>";
    
    // Show columns
    $stmt = $pdo->query("DESCRIBE school_exams");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns:</strong> " . implode(", ", $columns) . "</p>";
    
    // Show sample
    $stmt = $pdo->prepare("SELECT id, school_id, exam_name, status FROM school_exams WHERE school_id = ? LIMIT 3");
    $stmt->execute([$school_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($exams) > 0) {
        echo "<pre style='background: #f0f0f0; padding: 10px; border-left: 3px solid green;'>";
        foreach ($exams as $exam) {
            echo "ID: {$exam['id']}, School: {$exam['school_id']}, Name: {$exam['exam_name']}, Status: {$exam['status']}\n";
        }
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Summary
echo "<h3>📋 Summary & Recommendations</h3>";
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_classes WHERE school_id = ?");
$stmt->execute([$school_id]);
$classCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_subjects WHERE school_id = ?");
$stmt->execute([$school_id]);
$subjectCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM school_exams WHERE school_id = ?");
$stmt->execute([$school_id]);
$examCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Item</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Count</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Status</th>";
echo "</tr>";

echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Classes</strong></td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>$classCount</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($classCount > 0 ? "✅" : "❌ MISSING") . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Subjects</strong></td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>$subjectCount</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($subjectCount > 0 ? "✅" : "❌ MISSING") . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'><strong>Exams</strong></td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>$examCount</td>";
echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($examCount > 0 ? "✅" : "❌ MISSING") . "</td>";
echo "</tr>";

echo "</table>";

echo "<hr>";

if ($classCount == 0 || $subjectCount == 0) {
    echo "<div style='background: #fee; padding: 15px; border-radius: 5px; border-left: 4px solid red;'>";
    echo "<strong style='color: red;'>⚠️ PROBLEM FOUND:</strong><br>";
    if ($classCount == 0) {
        echo "❌ No classes in database for school_id $school_id<br>";
        echo "→ You need to create classes first in your School Admin<br>";
    }
    if ($subjectCount == 0) {
        echo "❌ No subjects in database for school_id $school_id<br>";
        echo "→ You need to create subjects first in your School Admin<br>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #efe; padding: 15px; border-radius: 5px; border-left: 4px solid green;'>";
    echo "<strong style='color: green;'>✅ Data exists!</strong><br>";
    echo "The problem might be with the status filter or column names.<br>";
    echo "Check the actual query being executed.";
    echo "</div>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Diagnostic Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
        }
        h2, h3 {
            color: #333;
        }
        p {
            color: #555;
            line-height: 1.6;
        }
        hr {
            border: none;
            border-top: 2px solid #ddd;
            margin: 20px 0;
        }
        pre {
            font-family: 'Courier New', monospace;
            border: 1px solid #ddd;
            overflow-x: auto;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
</body>
</html>
