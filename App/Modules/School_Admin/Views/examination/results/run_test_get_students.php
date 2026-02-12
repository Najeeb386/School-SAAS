<?php
// CLI test harness for get_students_by_class.php
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET['class_id'] = 11;
$_GET['section_id'] = 8;
$_GET['school_id'] = 10;
session_start();
$_SESSION['logged_in'] = true;
$_SESSION['user_type'] = 'school';
$_SESSION['school_id'] = 10;
include __DIR__ . '/get_students_by_class.php';
?>