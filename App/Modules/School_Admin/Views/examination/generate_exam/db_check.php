<?php
/**
 * Direct Database Check - No Models, No Controllers, Just Raw Queries
 */

require_once __DIR__ . '/../../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;

if (!$school_id) {
    die("ERROR: No school_id in session. School ID required.");
}

$pdo = Database::connect();

echo "==========================================\n";
echo "DIRECT DATABASE CHECK - School ID: $school_id\n";
echo "==========================================\n\n";

// 1. Check school_classes directly
echo "1. SCHOOL_CLASSES TABLE\n";
echo "-----------------------------------------\n";
try {
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'school_classes'")->fetch();
    if (!$result) {
        echo "❌ Table 'school_classes' does NOT exist!\n";
    } else {
        echo "✅ Table exists\n";
        
        // Check total records
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM school_classes");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "   Total records: $total\n";
        
        // Check records for this school
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM school_classes WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $schoolCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "   Records for school_id=$school_id: $schoolCount\n";
        
        if ($schoolCount > 0) {
            echo "\n   Sample records:\n";
            $stmt = $pdo->prepare("SELECT id, school_id, class_name FROM school_classes WHERE school_id = ? LIMIT 3");
            $stmt->execute([$school_id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "   - ID: {$row['id']}, School: {$row['school_id']}, Class: {$row['class_name']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Check school_class_sections
echo "2. SCHOOL_CLASS_SECTIONS TABLE\n";
echo "-----------------------------------------\n";
try {
    $result = $pdo->query("SHOW TABLES LIKE 'school_class_sections'")->fetch();
    if (!$result) {
        echo "❌ Table 'school_class_sections' does NOT exist!\n";
        echo "   (If you use 'school_sections' instead, let me know!)\n";
    } else {
        echo "✅ Table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM school_class_sections");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "   Total records: $total\n";
        
        // Check columns
        $stmt = $pdo->query("DESCRIBE school_class_sections");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "   Columns: " . implode(", ", $columns) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Check school_subjects
echo "3. SCHOOL_SUBJECTS TABLE\n";
echo "-----------------------------------------\n";
try {
    $result = $pdo->query("SHOW TABLES LIKE 'school_subjects'")->fetch();
    if (!$result) {
        echo "❌ Table 'school_subjects' does NOT exist!\n";
    } else {
        echo "✅ Table exists\n";
        
        // Check columns first
        $stmt = $pdo->query("DESCRIBE school_subjects");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "   Columns:\n";
        foreach ($columns as $col) {
            echo "   - {$col['Field']}\n";
        }
        
        // Check total records
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM school_subjects");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "\n   Total records: $total\n";
        
        // Check records for this school
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM school_subjects WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $schoolCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "   Records for school_id=$school_id: $schoolCount\n";
        
        if ($schoolCount > 0) {
            echo "\n   Sample records:\n";
            $query = "SELECT id, school_id, name FROM school_subjects WHERE school_id = ? LIMIT 3";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$school_id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "   - ID: {$row['id']}, School: {$row['school_id']}, Name: {$row['name']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Check school_exams (this one works, so verify it)
echo "4. SCHOOL_EXAMS TABLE\n";
echo "-----------------------------------------\n";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM school_exams WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "✅ Exams count: $count\n";
    
    if ($count > 0) {
        echo "   Sample exams:\n";
        $stmt = $pdo->prepare("SELECT id, exam_name FROM school_exams WHERE school_id = ? LIMIT 3");
        $stmt->execute([$school_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - ID: {$row['id']}, Name: {$row['exam_name']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo "==========================================\n";
echo "END OF CHECK\n";
echo "==========================================\n";
echo "\nCopy this output and share it to debug the issue!\n";
?>
