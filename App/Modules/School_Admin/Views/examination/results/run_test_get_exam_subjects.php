<?php
// CLI test harness for get_exam_subjects.php
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET['exam_id'] = 5;
$_GET['class_id'] = 15;
$_GET['school_id'] = 10;
// Simulate logged-in school admin for CLI testing
session_start();
$_SESSION['logged_in'] = true;
$_SESSION['user_type'] = 'school';
$_SESSION['school_id'] = 10;

include __DIR__ . '/get_exam_subjects.php';
// EOF

