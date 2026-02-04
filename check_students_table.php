<?php
require_once __DIR__ . '/Config/connection.php';

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get table structure
    $stmt = $db->prepare('DESCRIBE school_students');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>school_students table structure:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Get a sample record
    echo "<h2>Sample record:</h2>";
    $stmt = $db->prepare('SELECT * FROM school_students LIMIT 1');
    $stmt->execute();
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
