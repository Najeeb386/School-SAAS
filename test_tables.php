<?php
require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/App/Core/database.php';

$db = \Database::connect();

// Check if tables exist and create them if needed
$tables_sql = [
    "CREATE TABLE IF NOT EXISTS school_classes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_id INT NOT NULL,
        session_id INT NOT NULL,
        class_name VARCHAR(100) NOT NULL,
        class_code VARCHAR(50),
        grade_level VARCHAR(50),
        class_order INT DEFAULT 0,
        description TEXT,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_school (school_id),
        KEY idx_session (session_id)
    )",
    
    "CREATE TABLE IF NOT EXISTS school_class_sections (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_id INT NOT NULL,
        session_id INT NOT NULL,
        class_id INT NOT NULL,
        section_name VARCHAR(100) NOT NULL,
        section_code VARCHAR(50),
        class_teacher_id INT,
        room_number VARCHAR(50),
        capacity INT,
        current_enrollment INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_class (class_id),
        KEY idx_school (school_id),
        KEY idx_session (session_id)
    )"
];

foreach ($tables_sql as $sql) {
    try {
        $db->exec($sql);
        echo "✓ Table created/exists\n";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nTables ready!";
?>
