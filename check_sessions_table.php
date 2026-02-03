<?php
require_once __DIR__ . '/Config/auth_check_school_admin.php';
require_once __DIR__ . '/Core/database.php';

$db = \Database::connect();
$result = $db->query("DESCRIBE school_sessions");
$columns = $result->fetchAll(\PDO::FETCH_ASSOC);

echo "<h2>school_sessions Table Structure:</h2>";
echo "<pre>";
print_r($columns);
echo "</pre>";

// Also show sample data
$stmt = $db->query("SELECT * FROM school_sessions LIMIT 5");
$data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "<h2>Sample Data:</h2>";
echo "<pre>";
print_r($data);
echo "</pre>";
?>
